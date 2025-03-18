<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/CategoryModel.php');
require_once($CFG->dirroot . '/custom/app/Models/LectureFormatModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');
require_once($CFG->dirroot . '/custom/app/Models/SurveyApplicationModel.php');

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
$surveyApplicationModel = new SurveyApplicationModel();

$categorys = $categoryModel->getCategories();

$currentPage = $_GET['page'] ?? 1; // 現在のページ番号（デフォルト: 1）
$perPage = 10; // 1ページあたりの件数
// 検索条件を取得
$category_id = $_POST['category_id'] ?? '';
$event_status = $_POST['event_status'] ?? '';
$event_id = $_POST['event_id'] ?? '';

// イベントを取得
$events = $eventModel->getEvents([
    'userid' => $userid,
    'shortname' => $shortname
], $currentPage, $perPage);

// アンケート回答を取得
$surveyApplications = $surveyApplicationModel->getSurveyApplications([
    'category_id' => $category_id,
    'event_status' => $event_status,
    'event_id' => $event_id,
], $currentPage, $perPage);

$totalCount = $surveyApplicationModel->getSurveyTotal([
    'category_id' => $category_id,
    'event_status' => $event_status,
    'event_id' => $event_id,
]);

// フォーム送信（POST）でコントローラーを呼び出す処理
$action = optional_param('action', '', PARAM_ALPHA); // アクションパラメータを取得

// 検索フォームが送信されていない場合は、セッションに保存された検索条件をクリア
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    unset($_SESSION['old_input']);
}

// 現在の検索条件を取得
$queryParams = $_GET; // GETパラメータを取得
unset($queryParams['page']); // ページ番号は後で設定するため削除
$queryString = http_build_query($queryParams); // クエリ文字列を作成

if ($action === 'index') {
    $_SESSION['old_input'] = $_POST; // 入力内容も保持
    include $CFG->dirroot . '/custom/admin/app/Views/survey/index.php';
}
