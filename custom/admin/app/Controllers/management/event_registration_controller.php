<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationCourseInfoModel.php');
require_once('/var/www/html/moodle/custom/app/Models/RoleAssignmentsModel.php');

class EventRegistrationController
{

    private $categoryModel;
    private $eventModel;
    private $eventApplicationCourseInfo;
    private $roleAssignmentsModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        $this->eventModel = new EventModel();
        $this->eventApplicationCourseInfo = new EventApplicationCourseInfoModel();
        $this->roleAssignmentsModel = new RoleAssignmentsModel();
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
        $total_count = 0;

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

        $role = $this->roleAssignmentsModel->getShortname($USER->id);
        $shortname = $role['shortname'];
        $filters = array_filter([
            'category_id' => $category_id,
            'event_status' => $event_status_id,
            'event_id' => $event_id,
            'course_no' => $course_no,
            'userid' => $USER->id,
            'shortname' => $shortname
        ]);

        // null の要素を削除しイベント検索
        $filters = array_filter($filters);
        $event_list = $this->eventModel->getEvents($filters, 1, 100000);
        $select_event_list = $this->eventModel->getEvents([
            'userid' => $USER->id,
            'shortname' => $shortname
        ], 1, 100000); // イベント名選択用
        $course_list = [];

        $is_display = false;
        $is_single = false;
        $course_info_id = null;

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
                elseif ($event['event_kbn'] == PLURAL_EVENT) {
                    $course_list = $event['course_infos'];
                    if (!empty($course_no)) {
                        foreach ($event['course_infos'] as $course_info) {
                            if ($course_info['no'] == $course_no) {
                                $course_info_id = $course_info['id'];
                                $is_display = true;
                            }
                        }
                    }
                } elseif ($event['event_kbn'] == EVERY_DAY_EVENT) {
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
        $application_course_info_list_count = [];
        // 講義回数まで絞り込んだ場合
        if (!empty($course_info_id)) {
            $application_course_info_list = $this->eventApplicationCourseInfo->getByCourseInfoId($course_info_id, $keyword, $current_page, $per_page);
            $application_course_info_list_count = $this->eventApplicationCourseInfo->getByCourseInfoId($course_info_id, $keyword, 1, 100000);
        }
        // イベント単位まで絞り込んだ場合
        if (empty($course_info_id) && !empty($event_id)) {
            $application_course_info_list = $this->eventApplicationCourseInfo->getByEventEventId($event_id, $keyword, $current_page, $per_page);
            $application_course_info_list_count = $this->eventApplicationCourseInfo->getByEventEventId($event_id, $keyword, 1, 1000000);
        }

        $total_count = 0;
        if (!empty($application_course_info_list_count)) {
            foreach ($application_course_info_list_count as $value) {
                // キーワード検索ではお連れ様は検索から省く
                if ($value['ticket_type'] != TICKET_TYPE['SELF'] && !empty($keyword)) {
                    continue;
                }
                $total_count = $total_count + 1;
            }
        }

        // 講座回数でソートする
        usort($application_course_info_list, function ($a, $b) {
            return $a['course_info']['no'] <=> $b['course_info']['no'];
        });

        // カスタムフィールド情報取得
        $customfield_header_list = [];
        if ($event_id) {
            $curstom_list = $DB->get_record('event', ['id' => $event_id]);
            $custom_id = $curstom_list->event_customfield_category_id;
            if (!empty($custom_id) && $custom_id > 0) {
                $custom_field_list = $DB->get_records('event_customfield', ['event_customfield_category_id' => $custom_id, 'is_delete' => 0]);
                usort($custom_field_list, function ($a, $b) {
                    return (int)$a->sort - (int)$b->sort;
                });
                foreach ($custom_field_list as $custom_field) {
                    $customfield_header_list[$custom_field->id] = $custom_field->name;
                }
            }
        }

        // 保護者氏名が存在し、14歳未満の場合保護者氏名表示フラグをあげる
        $is_guardian_name = false;
        foreach ($application_course_info_list_count as $index => $application_checklist) {
            $target_user = reset($application_checklist['application'])['user'];
            if (!empty($target_user['guardian_name']) && $this->getAge($target_user['birthday']) < 14) {
                $is_guardian_name = true;
                break;
            }
        }

        // 表示データを取得・整形する
        $application_list = [];
        $application_customfield_list = [];
        foreach ($application_course_info_list as $key => $application_course_info) {
            $application_customfield_list = [];
            $application = reset($application_course_info['application']);
            $event = $application['event'];
            $application_date = new DateTime($application['application_date']);
            $application_date = $application_date->format("Y年n月j日");

            $name = '';
            $user_id = '';
            $is_paid = '';
            $payment_type = '';
            $payment_date = '';
            $note = '';
            $age = null;

            // 支払区分（payment_kbn）が「未払い(期限切れ)（2）」のデータは除外する
            if ($application['payment_kbn'] === 2) {
                continue;
            }

            // お連れ様の場合はユーザー情報は取得しない
            if ($application_course_info['ticket_type'] == TICKET_TYPE['SELF']) {
                $name = $application['user']['name'];
                $guardian_name = $application['user']['guardian_name'];
                $formatted_id =  str_pad($application["user"]['id'], 8, "0", STR_PAD_LEFT);
                $user_id  = substr_replace($formatted_id, ' ', 4, 0);
                $phone1 = $application['user']['phone1'];
                if ($application['pay_method'] != FREE_EVENT) {
                    $payment_type = PAYMENT_SELECT_LIST[$application['pay_method']];
                    $is_paid = !empty($application['payment_date']) ? '決済済' : '未決済';
                    if (!empty($application['payment_date'])) {
                        $payment_date = new DateTime($application['payment_date']);
                        $payment_date = $payment_date->format("Y年n月j日");
                    }
                }
                $note = $application['note'];
                $age = $this->getAge($application['user']['birthday']);
            } elseif (!empty($keyword)) {
                // キーワード未検索時はお連れ様の情報も取得する
                continue;
            }

            $application_congnitions = $DB->get_records('event_application_cognition', [
                'event_application_id' => $application_course_info['event_application_id']
            ]);

            $other = '';
            $trigger_txt = [];
            foreach ($application_congnitions as $application_congnition) {
                $other = $application_congnition->note;
                $trigger_txt[] = EVENT_TRIGGER_LIST[$application_congnition->cognition_id];
            }
            $trigger_txt_str = implode(', ', $trigger_txt);

            // カスタムフィールド回答結果を収集
            foreach (array_keys($customfield_header_list) as $index) {
                $event_customfield_list = $DB->get_record('event_application_customfield', [
                    'event_application_id' => $application_course_info['event_application_id'],
                    'event_customfield_id' => $index
                ]);
                $application_customfield_list[$application_course_info['event_application_id']][$index] = $event_customfield_list->input_data;
            }

            $application_list[$key] = [
                'id' => $application_course_info['id'],
                'event_name' => $event['name'],
                'no' => '第' . $application_course_info['course_info']['no'] . '講座',
                'user_id' => $user_id,
                'name' => $name,
                'email' => $application_course_info['participant_mail'],
                'phone1' => $phone1,
                'payment_type' => $payment_type,
                'is_paid' => $is_paid,
                'payment_date' => $payment_date,
                'application_date' => $application_date,
                'participation_kbn' => $application_course_info['participation_kbn'],
                'age' => $age,
                'note' => $note,
                'other' => $other,
                'trigger_txt_str' => $trigger_txt_str,
                'application_customfield_list' => $application_customfield_list
            ];

            if ($is_guardian_name) {
                $application_list[$key]['guardian_name'] = $guardian_name;
            }
        }

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
            'course_list' => $course_list,
            'customfield_header_list' => $customfield_header_list,
            'is_guardian_name' => $is_guardian_name
        ];

        return $data;
    }

    /**
     *  現在の年齢を取得する
     */
    public function getAge(?string $birthday = null): ?int
    {
        if (empty($birthday)) {
            return null;
        }

        $birthday = new DateTime(substr($birthday, 0, 10));
        $today = new DateTime();
        $age = $today->diff($birthday)->y;
        return $age;
    }
}
