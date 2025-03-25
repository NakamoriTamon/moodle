<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/SurveyApplicationModel.php');
global $DB;
class SurveyController
{

    private $categoryModel;
    private $eventModel;
    private $surveyApplicationModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        $this->eventModel = new EventModel();
        $this->surveyApplicationModel = new SurveyApplicationModel();
    }

    public function index()
    {

        global $USER;
        global $DB;

        // 検索項目取得
        $page = $_POST['page'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        $event_status_id = $_POST['event_status_id'] ?? null;
        $event_id = $_POST['event_id'] ?? null;
        $course_no = $_POST['course_no'] ?? null;
        $_SESSION['old_input'] = $_POST;

        // ページネーション
        $per_page = 15;
        $current_page = $_GET['page'] ?? 1;

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
                if ($event['event_kbn'] == 1) {
                    foreach ($event['course_infos'] as $course_info) {
                        $course_info_id = $course_info['id'];
                        $course_no = 1;
                        $_SESSION['old_input']['course_no'] = "1";
                        $is_single = true;
                        $is_display = true;
                    }
                }
                // 複数回イベントの場合
                if ($event['event_kbn'] == 2 && !empty($course_no)) {
                    foreach ($event['course_infos'] as $course_info) {
                        if ($course_info['no'] == $course_no) {
                            $course_info_id = $course_info['id'];
                            $is_display = true;
                        }
                    }
                }
            }
        }

        $survey_list = [];
        $total_count = 0;
        if (!empty($course_info_id) || !empty($event_id)) {
            $survey_list = $this->surveyApplicationModel->getSurveyApplications($course_info_id, $event_id, $current_page);
            $total_count = $this->surveyApplicationModel->getCountSurveyApplications($course_info_id, $event_id);
        }

        // 講座回数でソートする
        usort($survey_list, function ($a, $b) {
            return $a['course_info']['no'] <=> $b['course_info']['no'];
        });

        $event_list = !empty($event_id) && empty($event_status_id) && empty($category_id) ?  $select_event_list : $event_list;
        $category_list = $this->categoryModel->getCategories();

        $data = [
            'category_list' => $category_list,
            'event_list' => $event_list,
            'is_display' => $is_display,
            'is_single' => $is_single,
            'course_info_id' => $course_info_id,
            'course_no' => $course_no,
            'survey_list' => $survey_list,
            'total_count' => $total_count,
            'per_page' => $per_page,
            'current_page' => $current_page,
            'page' => $current_page,
        ];

        return $data;
    }
}
