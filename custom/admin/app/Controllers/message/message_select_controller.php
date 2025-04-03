<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/RoleAssignmentsModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/UserModel.php');


global $USER, $DB, $COURSE;

class MessageSelectController
{
    private $roleAssignmentsModel;
    private $categoryModel;
    private $eventModel;
    private $userModel;

    public function __construct()
    {
        $this->roleAssignmentsModel = new RoleAssignmentsModel();
        $this->categoryModel = new CategoryModel();
        $this->eventModel = new EventModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        global $USER;
        // ユーザーID(会員番号)を取得
        $userid = $USER->id;
        $role = $this->roleAssignmentsModel->getShortname($userid);
        $shortname = $role['shortname'];
        // 検索項目取得
        $page = $_POST['page'] ?? 1;
        $kbn_id = $_POST['kbn_id'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        $event_status_id = $_POST['event_status_id'] ?? null;
        $event_id = $_POST['event_id'] ?? null;
        $course_no = $_POST['course_no'] ?? null;
        $keyword = $_POST['keyword'] ?? null;
        $_SESSION['old_input'] = $_POST;
        
        $filters = array_filter([
            'category_id' => $category_id,
            'event_status' => $event_status_id,
            'event_id' => $event_id,
            'userid' => $userid,
            'shortname' => $shortname
        ]);
        $event_list = $this->eventModel->getEvents($filters, 1, 100000);
        
        $is_display = false;
        $is_single = false;
        $course_info_id = null;
        $user_list = [];
        if ($kbn_id == 1 && !empty($event_id)) {
            foreach ($event_list as $event) {
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
                // 毎日開催イベントの場合
                if ($event['event_kbn'] == 3) {
                    $course_info_id = null;
                    $course_no = "";
                    $_SESSION['old_input']['course_no'] = "";
                    $is_single = true;
                    $is_display = true;
                }
            }

            $filters = array_filter([
                'event_id' => $event_id,
                'keyword' => $keyword
            ]);
            //
            $user_list = $this->userModel->getEventEntryUser($filters, $page, 8);
        } elseif($kbn_id == 2) {

        }

        $category_list = $this->categoryModel->getCategories();

        $is_single = false;
        $data = [
            'category_list' => $category_list,
            'event_list' => $event_list,
            'kbn_id' => $kbn_id,
            'is_display' => $is_display,
            'is_single' => $is_single,
            'course_info_id' => $course_info_id,
            'course_no' => $course_no,
            'user_list' => $user_list,
            // 'total_count' => $total_count,
            // 'per_page' => $per_page,
            // 'current_page' => $current_page,
            // 'page' => $current_page,
            // 'survey_period' => $survey_period,
            // 'survey_field_list' => $survey_field_list,
        ];

        return $data;
    }
}