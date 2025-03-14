<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/LectureFormatModel.php');
require_once('/var/www/html/moodle/custom/app/Models/TargetModel.php');

$eventModel = new EventModel();
$categoryModel = new CategoryModel();
$lectureFormatModel = new LectureFormatModel();
$targetModel = new TargetModel();

$categorys = $categoryModel->getCategories();
$lectureFormats = $lectureFormatModel->getLectureFormats();
$targets = $targetModel->getTargets();

$currentPage = 1; // 現在のページ番号（デフォルト: 1）
$perPage = 10; // 1ページあたりの件数
// 検索条件を取得
// 現在日以降のイベントを表示
$event_start_date = $date = date('Y-m-d 00:00:00');;

$events = $eventModel->getEvents([
    'event_start_date' => $event_start_date
], $currentPage, $perPage);

$now = new DateTime();
$now = $now->format('Ymd');

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

        $count = 1;
        foreach($event['course_infos'] as $select_course) {
            if($count > 2) {
                break;
            }
            $course_date = (new DateTime($select_course['course_date']))->format('Ymd');
            if ($course_date >= $now) {
                $event['select_course'][$select_course['no']] = $select_course;
            }

            $count++;
        }
    }
    
}
?>