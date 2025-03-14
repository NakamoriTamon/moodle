<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/LectureFormatModel.php');

global $USER, $DB, $COURSE;

// ユーザーIDを取得
$userid = $USER->id;

// ユーザーのロールを取得
$sql = "SELECT r.id, r.shortname 
        FROM {role_assignments} ra
        JOIN {role} r ON ra.roleid = r.id
        WHERE ra.userid = :userid";
        
$params = ['userid' => $userid];

$roles = $DB->get_records_sql($sql, $params);
foreach ($roles as $role) {
    $shortname = $role->shortname;
}

$eventModel = new EventModel();
$categoryModel = new CategoryModel();
$lectureFormatModel = new LectureFormatModel();

$categorys = $categoryModel->getCategories();
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
    'userid' => $userid,
    'shortname' => $shortname
], $currentPage, $perPage);

$totalCount = $eventModel->getEventTotal([
    'category_id' => $category_id,
    'event_status' => $event_status,
    'event_id' => $event_id,
]);
// フォーム送信（POST）でコントローラーを呼び出す処理
$action = optional_param('action', '', PARAM_ALPHA); // アクションパラメータを取得

if ($action === 'index') {
    $_SESSION['old_input'] = $_POST; // 入力内容も保持
    include '/var/www/html/moodle/custom/admin/app/Views/event/index.php';
}
