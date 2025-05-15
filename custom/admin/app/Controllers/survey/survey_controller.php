<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/SurveyApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventSurveyCustomFieldModel.php');
require_once('/var/www/html/moodle/custom/app/Models/SurveyApplicationCustomfieldModel.php');
require_once('/var/www/html/moodle/custom/app/Models/RoleAssignmentsModel.php');

global $DB;
class SurveyController
{

    private $categoryModel;
    private $eventModel;
    private $surveyApplicationModel;
    private $eventSurveyCustomFieldModel;
    private $surveyApplicationCustomfieldModel;
    private $roleAssignmentsModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        $this->eventModel = new EventModel();
        $this->surveyApplicationModel = new SurveyApplicationModel();
        $this->eventSurveyCustomFieldModel = new EventSurveyCustomFieldModel();
        $this->surveyApplicationCustomfieldModel = new SurveyApplicationCustomfieldModel();
        $this->roleAssignmentsModel = new RoleAssignmentsModel();
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
        $userid = $USER->id;

        $department = "";

        // ページネーション
        $per_page = 15;
        if (!empty($page) && is_numeric($page) && (int)$page > 0) {
            $current_page = (int)$page;
        } else {
            $current_page = 1;
        }
        $role = $this->roleAssignmentsModel->getShortname($userid);
        $shortname = $role['shortname'];

        $first_filters = array_filter([
            'category_id' => $category_id,
            'event_status' => $event_status_id,
            'userid' => $userid,
            'shortname' => $shortname
        ]);
        $first_filters = array_filter($first_filters);
        $found = false;
        if (!empty($first_filters) && !empty($event_id)) {
            $first_event_list = $this->eventModel->getEvents($first_filters, 1, 100000);
            $select_event_list = $this->eventModel->getEvents([], 1, 100000); // イベント名選択用
            foreach ($first_event_list as $first_event) {
                if ($event_id == $first_event['id']) {
                    $found = true;
                }
            }
            if (!$found) {
                $event_id = $found ? $event_id : null;
            }
        }

        $role = $this->roleAssignmentsModel->getShortname($userid);
        $shortname = $role['shortname'];
        $filters = array_filter([
            'category_id' => $category_id,
            'event_status' => $event_status_id,
            'event_id' => $event_id,
            'course_no' => $course_no,
            'userid' => $userid,
            'shortname' => $shortname
        ]);

        // null の要素を削除しイベント検索
        $filters = array_filter($filters);
        $event_list = $this->eventModel->getEvents($filters, 1, 100000); // イベント名選択用
        $select_event_list = $this->eventModel->getEvents([
            'userid' => $userid,
            'shortname' => $shortname], 1, 100000); // イベント名選択用

        $is_display = false;
        $is_single = false;
        $course_info_id = null;
        $event_survey_customfield_category_id = null;
        $course_list = [];

        // イベント情報を特定する
        if (!empty($event_id)) {
            foreach ($event_list as $event) {
                $event_survey_customfield_category_id = $event['event_survey_customfield_category_id'];
                $department = $event['department'];
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
                if ($event['event_kbn'] == PLURAL_EVENT) {
                    $course_list = $event['course_infos'];
                    if(!empty($course_no)) {
                        foreach ($event['course_infos'] as $course_info) {
                            if ($course_info['no'] == $course_no) {
                                $course_info_id = $course_info['id'];
                                $is_display = true;
                            }
                        }
                    }
                }
                // 毎日開催イベントの場合
                if ($event['event_kbn'] == EVERY_DAY_EVENT) {
                    $course_info_id = null;
                    $course_no = "";
                    $_SESSION['old_input']['course_no'] = "";
                    $is_single = true;
                    $is_display = true;
                }
            }
        }

        $survey_list = [];
        $survey_field_list = [];
        $total_count = 0;
        $survey_period = null;
        if (!empty($course_info_id) || !empty($event_id)) {
            $survey_list = $this->surveyApplicationModel->getSurveyApplications($course_info_id, $event_id, $current_page);
            $total_count = $this->surveyApplicationModel->getCountSurveyApplications($course_info_id, $event_id);
            if (!empty($event_survey_customfield_category_id)) {
                $survey_field_list = $this->eventSurveyCustomFieldModel->getEventSurveyCustomFieldById($event_survey_customfield_category_id);
                foreach ($survey_list as &$survey) {
                    $list = $this->surveyApplicationCustomfieldModel->getESurveyApplicationCustomfieldBySurveyApplicationId($survey['id']);
                    $survey['customfiel'] = $list;

                    // アンケート時間集計
                    $start = strtotime($survey['event']["start_hour"]);
                    $end = strtotime($survey['event']["end_hour"]);
                    $survey_period = ($end - $start) / 60;
                }
            }
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
            'survey_period' => $survey_period,
            'survey_field_list' => $survey_field_list,
            'course_list' => $course_list,
            'department' => $department
        ];

        return $data;
    }
}
