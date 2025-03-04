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

$currentPage = $_GET['page'] ?? 1; // 現在のページ番号（デフォルト: 1）
$perPage = 12; // 1ページあたりの件数
// 検索条件を取得
$category_id = $_GET['category'] ?? [];
$event_status = $_GET['event_status'] ?? [];
$deadline_status = $_GET['deadline_status'] ?? [];
$lecture_format_id = $_GET['lecture_format_id'] ?? [];
$keyword = $_GET['keyword'] ?? '';
$event_start_date = $_GET['event_start_date'] ?? '';
$event_end_date = $_GET['event_end_date'] ?? '';

$events = $eventModel->getEvents([
    'event_status' => $event_status,
    'deadline_status' => $deadline_status,
    'lecture_format_id' => $lecture_format_id,
    'keyword' => $keyword,
    'event_start_date' => $event_start_date,
    'event_end_date' => $event_end_date,
    'category_id' => $category_id
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

$totalCount = $eventModel->getEventTotal([
    'event_status' => $event_status,
    'deadline_status' => $deadline_status,
    'lecture_format_id' => $lecture_format_id,
    'keyword' => $keyword,
    'event_start_date' => $event_start_date,
    'event_end_date' => $event_end_date,
    'category_id' => $category_id
]);
// フォーム送信（POST）でコントローラーを呼び出す処理
$action = optional_param('action', '', PARAM_ALPHA); // アクションパラメータを取得

$_SESSION['old_input'] = $_GET; // 入力内容も保持
if ($action === 'index') {
    include '/var/www/html/moodle/custom/app/Views/event/index.php';
}
