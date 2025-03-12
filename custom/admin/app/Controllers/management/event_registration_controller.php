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


        // 検索項目取得
        $year = $_POST['year'] ?? null;
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
        $current_page = $_GET['page'];

        if (empty($current_page) && !empty($page)) {
            $current_page  = $page;
        }
        if (empty($current_page) && empty($page)) {
            $current_page  = 1;
        }

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

        $is_display = false;
        $is_single = false;
        $course_info_id = null;
        // 講義動画を取得
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

        $application_list = [];
        // 講義回数まで絞り込んだ場合
        if (!empty($course_info_id)) {
            $application_course_info_list = $this->eventApplicationCourseInfo->getByCourseInfoId($course_info_id, $keyword, $current_page);
            $application_list = $application_course_info_list;
        }
        // イベント単位まで絞り込んだ場合
        if (empty($course_info_id) && !empty($event_id)) {
            var_dump($event_id);
        }

        // ユーザー情報がなければ配列から排除する
        $count = 0;
        foreach ($application_list as $key => $application) {
            $result_application = $application['application'][$count]['user'] ?? null;
            if (empty($result_application)) {
                unset($application_list[$key]);
            }
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
