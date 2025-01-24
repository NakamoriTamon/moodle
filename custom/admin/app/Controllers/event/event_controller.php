<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/LectureFormatModel.php');

$eventModel = new EventModel();
$categoryModel = new CategoryModel();
$lectureFormatModel = new LectureFormatModel();

$categorys = $categoryModel->getCategorys();
$lectureFormats = $lectureFormatModel->getLectureFormats();

$currentPage = $_GET['page'] ?? 1; // 現在のページ番号（デフォルト: 1）
$perPage = 10; // 1ページあたりの件数
// 検索条件を取得
$category_id = $_POST['category_id'] ?? '';
$event_status = $_POST['event_status'] ?? '';
$event_id = $_POST['event_id'] ?? '';

$events = $eventModel->getEvents([
    'category_id' => $category_id,
    'event_status' => $event_status,
    'event_id' => $event_id,
], $currentPage, $perPage);

$totalCount = count($events);
// フォーム送信（POST）でコントローラーを呼び出す処理
$action = optional_param('action', '', PARAM_ALPHA); // アクションパラメータを取得

if ($action === 'index') {
    $_SESSION['old_input'] = $_POST; // 入力内容も保持
    include '/var/www/html/moodle/custom/admin/app/Views/event/index.php';
}
