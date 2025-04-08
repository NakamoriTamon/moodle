<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationCourseInfoModel.php');

class EventRegistrationController
{

    private $categoryModel;
    private $eventModel;
    private $eventApplicationCourseInfo;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        $this->eventModel = new EventModel();
        $this->eventApplicationCourseInfo = new EventApplicationCourseInfoModel();
    }

    public function index()
    {

        global $USER;
        global $DB;

        // 検索項目取得
        $keyword = $_POST['keyword'] ?? null;
        $page = $_POST['page'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        $event_status_id = $_POST['event_status_id'] ?? null;
        $event_id = $_POST['event_id'] ?? null;
        $course_no = $_POST['course_no'] ?? null;
        $keyword = $_POST['keyword'] ?? null;
        $_SESSION['old_input'] = $_POST;

        // ページネーション
        $per_page = 15;
        $current_page = $page;

        if (empty($current_page) && !empty($page)) {
            $current_page  = $page;
        }
        if (empty($current_page) && empty($page)) {
            $current_page  = 1;
        }

        // イベント選択時かつ他の選択肢が選択された際に対象イベントが含まれていなければ消す
        $first_filters = array_filter([
            'category_id' => $category_id,
            'event_status' => $event_status_id,
        ]);
        $first_filters = array_filter($first_filters);
        $found = false;
        if (!empty($first_filters) && !empty($event_id)) {
            $first_event_list = $this->eventModel->getEvents($first_filters, 1, 100000);
            foreach ($first_event_list as $first_event) {
                if ($event_id == $first_event['id']) {
                    $found = true;
                }
            }
            if (!$found) {
                $event_id = $found ? $event_id : null;
            }
        }

        $filters = array_filter([
            'category_id' => $category_id,
            'event_status' => $event_status_id,
            'event_id' => $event_id,
            'course_no' => $course_no
        ]);

        $role = $DB->get_record('role_assignments', ['userid' => $USER->id]);

        // null の要素を削除しイベント検索
        $filters = array_filter($filters);
        $event_list = $this->eventModel->getEvents($filters, 1, 100000);
        $select_event_list = $this->eventModel->getEvents([], 1, 100000); // イベント名選択用

        $is_display = false;
        $is_single = false;
        $course_info_id = null;

        // 部門管理者ログイン時は自身が作成したイベントのみを取得する
        if ($role->roleid == ROLE['COURSECREATOR']) {
            foreach ($event_list  as $key => $event) {
                if ($event['userid'] != $USER->id) {
                    unset($event_list[$key]);
                }
            }
            foreach ($select_event_list as $select_key => $select_event) {
                if ($select_event['userid'] != $USER->id) {
                    unset($select_event_list[$select_key]);
                }
            }
        }

        // イベント情報を特定する
        foreach ($event_list as $event) {
            if (!empty($event_id)) {
                // 単発イベントの場合
                if ($event['event_kbn'] == SINGLE_EVENT) {
                    foreach ($event['course_infos'] as $course_info) {
                        $course_info_id = $course_info['id'];
                        $course_no = 1;
                        $_SESSION['old_input']['course_no'] = "1";
                        $is_single = true;
                        $is_display = true;
                    }
                }
                // 複数回イベントの場合
                elseif ($event['event_kbn'] == PLURAL_EVENT && !empty($course_no)) {
                    foreach ($event['course_infos'] as $course_info) {
                        if ($course_info['no'] == $course_no) {
                            $course_info_id = $course_info['id'];
                            $is_display = true;
                        }
                    }
                }
                elseif ($event['event_kbn'] == EVERY_DAY_EVENT) {
                    foreach ($event['course_infos'] as $course_info) {
                        $course_info_id = "";
                        $course_no = "";
                        $_SESSION['old_input']['course_no'] = "";
                        $is_single = true;
                        $is_display = true;
                    }
                }
            }
        }

        // IDの0を落とす
        if (is_numeric($keyword)) {
            $keyword = ltrim($keyword, '0');
        }
        $application_course_info_list = [];
        // 講義回数まで絞り込んだ場合
        if (!empty($course_info_id)) {
            $application_course_info_list = $this->eventApplicationCourseInfo->getByCourseInfoId($course_info_id, $keyword, $current_page);
        }
        // イベント単位まで絞り込んだ場合
        if (empty($course_info_id) && !empty($event_id)) {
            $application_course_info_list = $this->eventApplicationCourseInfo->getByEventEventId($event_id, $keyword, $current_page);
        }

        // 講座回数でソートする
        usort($application_course_info_list, function ($a, $b) {
            return $a['course_info']['no'] <=> $b['course_info']['no'];
        });

        // 表示データを取得・整形する
        $application_list = [];
        foreach ($application_course_info_list as $key => $application_course_info) {
            $application = reset($application_course_info['application']);
            $event = $application['event'];
            $application_date = new DateTime($application['application_date']);
            $application_date = $application_date->format("Y年n月j日");

            $name = '';
            $user_id = '';
            $is_paid = '';
            $payment_type = '';
            $payment_date = '';

            // お連れ様の場合はユーザー情報は取得しない
            if ($application['user']['email'] ==  $application_course_info['participant_mail']) {
                $name = $application['user']['name'];
                $formatted_id =  str_pad($application["user"]['id'], 8, "0", STR_PAD_LEFT);
                $user_id  = substr_replace($formatted_id, ' ', 4, 0);
                if ($application['pay_method'] != FREE_EVENT) {
                    $payment_type = PAYMENT_SELECT_LIST[$application['pay_method']];
                    $is_paid = !empty($application['payment_date']) ? '決済済' : '未決済';
                    if (!empty($application['payment_date'])) {
                        $payment_date = new DateTime($application['payment_date']);
                        $payment_date = $payment_date->format("Y年n月j日");
                    }
                }
            } elseif (!empty($keyword)) {
                // キーワード未検索時はお連れ様の情報も取得する
                continue;
            }

            $application_list[$key] = [
                'id' => $application_course_info['id'],
                'event_name' => $event['name'],
                'no' => '第' . $application_course_info['course_info']['no'] . '講座',
                'user_id' => $user_id,
                'name' => $name,
                'email' => $application_course_info['participant_mail'],
                'payment_type' => $payment_type,
                'is_paid' => $is_paid,
                'payment_date' => $payment_date,
                'application_date' => $application_date,
                'participation_kbn' => $application_course_info['participation_kbn'],
            ];
        }

        $total_count = count($application_list);
        $event_list = !empty($event_id) && empty($event_status_id) && empty($category_id) ?  $select_event_list : $event_list;
        $category_list = $this->categoryModel->getCategories();

        $data = [
            'category_list' => $category_list,
            'event_list' => $event_list,
            'is_display' => $is_display,
            'is_single' => $is_single,
            'course_info_id' => $course_info_id,
            'course_no' => $course_no,
            'application_list' => $application_list,
            'total_count' => $total_count,
            'per_page' => $per_page,
            'current_page' => $current_page,
            'page' => $current_page,
        ];

        return $data;
    }
}
