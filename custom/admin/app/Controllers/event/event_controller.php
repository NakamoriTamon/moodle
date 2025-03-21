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
$category_id = $_GET['select_category_id'] ?? '';
$event_status = $_GET['select_event_status'] ?? '';
$event_id = $_GET['select_event_id'] ?? '';

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
    'userid' => $userid,
    'shortname' => $shortname
]);
// フォーム送信（POST）でコントローラーを呼び出す処理
$action = optional_param('action', '', PARAM_ALPHA); // アクションパラメータを取得

// 現在の検索条件を取得
$queryParams = $_GET; // GETパラメータを取得
$_SESSION['old_input'] = $_GET;
unset($queryParams['page']); // ページ番号は後で設定するため削除
$queryString = http_build_query($queryParams); // クエリ文字列を作成

if ($action === 'index') {
    include '/var/www/html/moodle/custom/admin/app/Views/event/index.php';
}
