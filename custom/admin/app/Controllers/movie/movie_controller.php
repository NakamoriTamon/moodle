<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/MovieModel.php');
require_once('/var/www/html/moodle/custom/app/Models/RoleAssignmentsModel.php');
global $DB;
class MovieController
{

    private $categoryModel;
    private $eventModel;
    private $movieModel;
    private $roleAssignmentsModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        $this->eventModel = new EventModel();
        $this->movieModel = new MovieModel();
        $this->roleAssignmentsModel = new RoleAssignmentsModel();
    }

    public function index()
    {

        global $USER;
        global $DB;

        // 検索項目取得
        $category_id = $_POST['category_id'] ?? null;
        $event_status_id = $_POST['event_status_id'] ?? null;
        $event_id = $_POST['event_id'] ?? null;
        $course_no = $_POST['course_no'] ?? null;
        $_SESSION['old_input'] = $_POST;

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

        $movie = [];
        $is_display = false;
        $is_single = false;
        $is_double_speed = false;
        $course_info_id = null;
        $course_list = [];

        // 講義動画を取得
        foreach ($event_list as $event) {
            if (!empty($event_id)) {
                if ($event['event_kbn'] == SINGLE_EVENT) {
                    foreach ($event['course_infos'] as $course_info) {
                        $course_info_id = $course_info['id'];
                        $course_list = [];
                        $course_no = "1";
                        $is_display = true;
                        $is_single = true;
                    }
                } elseif ($event['event_kbn'] == PLURAL_EVENT) {
                    if (!empty($course_no)) {
                        foreach ($event['course_infos'] as $course_info) {
                            if ($course_info['no'] == $course_no) {
                                $course_info_id = $course_info['id'];
                                $is_display = true;
                            }
                        }
                    }
                    $course_list = $event['course_infos'];
                } elseif ($event['event_kbn'] == EVERY_DAY_EVENT) {
                    foreach ($event['course_infos'] as $course_info) {
                        $course_info_id = $course_info['id'];
                        $course_list = [];
                        $course_no = "1";
                        $is_single = true;
                        $is_display = true;
                    }
                }
            }
        }


        $movie = $this->movieModel->getMovieByInfoId($course_info_id);
        $event_list = !empty($event_id) && empty($event_status_id) && empty($category_id) ?  $select_event_list : $event_list;
        $category_list = $this->categoryModel->getCategories();

        $data = [
            'category_list' => $category_list,
            'event_list' => $event_list,
            'movie' => $movie,
            'is_display' => $is_display,
            'is_single' => $is_single,
            'is_double_speed' => $is_double_speed,
            'course_info_id' => $course_info_id,
            'course_no' => $course_no,
            'course_list' => $course_list,
        ];

        return $data;
    }
}
