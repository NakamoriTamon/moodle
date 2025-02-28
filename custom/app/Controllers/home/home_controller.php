<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/LectureFormatModel.php');

$eventModel = new EventModel();
$categoryModel = new CategoryModel();
$lectureFormatModel = new LectureFormatModel();

$categorys = $categoryModel->getCategories();
$lectureFormats = $lectureFormatModel->getLectureFormats();

$currentPage = 1; // 現在のページ番号（デフォルト: 1）
$perPage = 10; // 1ページあたりの件数
// 検索条件を取得
// 現在日以降のイベントを表示
$event_start_date = $date = date('Y-m-d 00:00:00');;

$events = $eventModel->getEvents([
    'event_start_date' => $event_start_date
], $currentPage, $perPage);

if(!empty($events)) {
    foreach($events as &$event) { 
        $select_lecture_formats = [];
        $select_categorys = [];
        $select_courses = [];
        
        foreach($event['lecture_formats'] as $select_category) {
            $select_lecture_formats[] = $select_category['lecture_format_id'];
        }
        $event['select_lecture_formats'] = $select_lecture_formats;

        foreach($event['categorys'] as $select_category) {
            $select_categorys[] = $select_category['category_id'];
        }
        $event['select_categorys'] = $select_categorys;

        foreach($event['course_infos'] as $select_course) {
            $event['select_course'][$select_course['no']] = $select_course;
        }
    }
    
}
?>