<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/MovieModel.php');
global $DB;
class MovieController
{

    private $categoryModel;
    private $eventModel;
    private $movieModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        $this->eventModel = new EventModel();
        $this->movieModel = new MovieModel();
    }

    public function index()
    {
        // 検索項目取得
        $category_id = $_POST['category_id'] ?? null;
        $event_status_id = $_POST['event_status_id'] ?? null;
        $event_id = $_POST['event_id'] ?? null;
        $course_no = $_POST['course_no'] ?? null;
        $_SESSION['old_input'] = $_POST;

        $filters = array_filter([
            'category_id' => $category_id,
            'event_status' => $event_status_id,
            'event_id' => $event_id,
            'course_no' => $course_no
        ]);

        // null の要素を削除しイベント検索
        $filters = array_filter($filters);
        $event_list = $this->eventModel->getEvents($filters, 1, 100000);
        $select_event_list = $this->eventModel->getEvents([], 1, 100000); // イベント名選択用

        $movie = [];
        $is_display = false;
        $is_single = false;
        $is_double_speed = false;
        $id = null;
        $course_info_id = null;
        // 講義動画を取得
        foreach ($event_list as $event) {
            $is_double_speed = $event['is_double_speed'];
            if (!empty($event_id)) {
                // 単発イベントの場合
                if ($event['event_kbn'] == 1) {
                    foreach ($event['course_infos'] as $course_info) {
                        $course_info_id = $course_info['id'];
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
            'id' => $id
        ];

        return $data;
    }
}
