<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/LectureFormatModel.php');
require_once('/var/www/html/moodle/custom/app/Models/RoleAssignmentsModel.php');

global $USER, $DB, $COURSE;

// ユーザーID(会員番号)を取得
$userid = $USER->id;

$eventModel = new EventModel();
$categoryModel = new CategoryModel();
$lectureFormatModel = new LectureFormatModel();
$roleAssignmentsModel = new RoleAssignmentsModel();

$role = $roleAssignmentsModel->getShortname($userid);
$shortname = $role['shortname'];

$categorys = $categoryModel->getCategories();
$lectureFormats = $lectureFormatModel->getLectureFormats();

$currentPage = $_GET['page'] ?? 1; // 現在のページ番号（デフォルト: 1）
$perPage = 15; // 1ページあたりの件数
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
if(empty($totalCount)) {
    $totalCount = 0;
}
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
