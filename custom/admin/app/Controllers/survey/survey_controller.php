<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/CategoryModel.php');
require_once($CFG->dirroot . '/custom/app/Models/LectureFormatModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');
require_once($CFG->dirroot . '/custom/app/Models/SurveyApplicationModel.php');

// // Ajaxリクエストの処理 - カテゴリーとステータスでフィルタリングされたイベントを取得
// if (isset($_POST['ajax']) && $_POST['ajax'] === 'get_filtered_events') {
//     handleFilteredEventsRequest();
//     exit;
// }

// // Ajaxリクエストの処理 - 特定のイベントに紐づく回数を取得
// if (isset($_POST['ajax']) && $_POST['ajax'] === 'get_event_counts') {
//     handleEventCountsRequest();
//     exit;
// }

// // 通常のコントローラー処理
// global $USER, $DB, $COURSE;

// // ユーザー情報の取得
// $userid = $USER->id;
// $shortname = '';

// // ユーザーのロールを取得
// $sql = "SELECT r.id, r.shortname 
//         FROM {role_assignments} ra
//         JOIN {role} r ON ra.roleid = r.id
//         WHERE ra.userid = :userid";

// $params = ['userid' => $userid];
// $roles = $DB->get_records_sql($sql, $params);

// foreach ($roles as $role) {
//     $shortname = $role->shortname;
// }

// // モデルのインスタンス化
// $eventModel = new EventModel();
// $categoryModel = new CategoryModel();
// $surveyApplicationModel = new SurveyApplicationModel();

// // カテゴリーの取得
// $categorys = $categoryModel->getCategories();

// // ページネーション設定
// $currentPage = $_GET['page'] ?? 1; // 現在のページ番号（デフォルト: 1）
// $perPage = 10; // 1ページあたりの件数

// // 検索条件を取得（POSTまたはGETから）
// $category_id = $_POST['category_id'] ?? $_GET['category_id'] ?? '';
// $event_status = $_POST['event_status'] ?? $_GET['event_status'] ?? '';
// $event_id = $_POST['event_id'] ?? $_GET['event_id'] ?? '';
// $course_info_id = $_POST['event_count'] ?? $_GET['event_count'] ?? ''; // 回数ID

// // イベントの取得
// if (!empty($category_id) || !empty($event_status)) {
//     // カテゴリーまたは開催ステータスで絞り込みされた場合
//     $events = $eventModel->getEvents([
//         'userid' => $userid,
//         'shortname' => $shortname,
//         'category_id' => $category_id,
//         'event_status' => $event_status
//     ], $currentPage, $perPage);
// } else {
//     // 絞り込みがない場合は全てのイベントを取得
//     $events = $eventModel->getEvents([
//         'userid' => $userid,
//         'shortname' => $shortname
//     ], $currentPage, $perPage);
// }

// // イベントに関連する回数を取得
// $event_counts = [];
// if (!empty($event_id)) {
//     $sql = "SELECT ci.id, ci.no 
//             FROM {event_course_info} eci
//             JOIN {course_info} ci ON eci.course_info_id = ci.id
//             WHERE eci.event_id = :event_id
//             ORDER BY ci.no ASC";

//     $params = ['event_id' => $event_id];
//     $courseInfos = $DB->get_records_sql($sql, $params);

//     foreach ($courseInfos as $courseInfo) {
//         $event_counts[] = [
//             'id' => $courseInfo->id,
//             'no' => $courseInfo->no
//         ];
//     }
// }

// // アンケート回答を取得
// $surveyApplications = $surveyApplicationModel->getSurveyApplications([
//     'category_id' => $category_id,
//     'event_status' => $event_status,
//     'event_id' => $event_id,
//     'course_info_id' => $course_info_id,
// ], $currentPage, $perPage);

// // 総件数の取得
// $totalCount = $surveyApplicationModel->getSurveyTotal([
//     'category_id' => $category_id,
//     'event_status' => $event_status,
//     'event_id' => $event_id,
//     'course_info_id' => $course_info_id,
// ]);

// // フォーム送信処理
// $action = optional_param('action', '', PARAM_ALPHA); // アクションパラメータを取得

// // 検索条件をセッションに保存
// $_SESSION['old_input'] = [
//     'category_id' => $category_id,
//     'event_status' => $event_status,
//     'event_id' => $event_id,
//     'event_count' => $course_info_id
// ];

// // 現在の検索条件からクエリ文字列を作成（空の値は除外）
// $queryParams = [
//     'category_id' => $category_id,
//     'event_status' => $event_status,
//     'event_id' => $event_id,
//     'event_count' => $course_info_id
// ];
// $queryString = http_build_query(array_filter($queryParams));

// // indexアクションの場合はビューを表示
// if ($action === 'index') {
//     include $CFG->dirroot . '/custom/admin/app/Views/survey/index.php';
// }

// /**
//  * カテゴリーとステータスでフィルタリングされたイベントのAjaxリクエストを処理
//  */
// function handleFilteredEventsRequest() {
//     // POSTリクエストからパラメータを取得
//     $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : '';
//     $event_status = isset($_POST['event_status']) ? $_POST['event_status'] : '';

//     // EventModelのインスタンス化
//     $eventModel = new EventModel();

//     // カテゴリとステータスに基づいてイベントをフィルタリング
//     $filteredEvents = $eventModel->getEvents([
//         'category_id' => $category_id,
//         'event_status' => $event_status
//     ]);

//     // 結果を簡略化（IDと名前のみ）
//     $result = array_map(function($event) {
//         return [
//             'id' => $event['id'],
//             'name' => $event['name']
//         ];
//     }, $filteredEvents);

//     // JSONとしてレスポンス
//     header('Content-Type: application/json');
//     echo json_encode($result);
// }

// /**
//  * イベントIDに基づいて回数を取得するAjaxリクエストを処理
//  */
// function handleEventCountsRequest() {
//     // POSTリクエストからパラメータを取得
//     $event_id = isset($_POST['event_id']) ? $_POST['event_id'] : '';

//     if (empty($event_id)) {
//         header('Content-Type: application/json');
//         echo json_encode([]);
//         return;
//     }

//     global $DB;

//     // イベントに紐づく回数（course_info）を取得
//     $sql = "SELECT ci.id, ci.no 
//             FROM {event_course_info} eci
//             JOIN {course_info} ci ON eci.course_info_id = ci.id
//             WHERE eci.event_id = :event_id
//             ORDER BY ci.no ASC";

//     $params = ['event_id' => $event_id];
//     $courseInfos = $DB->get_records_sql($sql, $params);

//     $result = [];
//     foreach ($courseInfos as $courseInfo) {
//         $result[] = [
//             'id' => $courseInfo->id,
//             'no' => $courseInfo->no
//         ];
//     }

// $result = [];
// // JSONとしてレスポンス
// header('Content-Type: application/json');
// echo json_encode($result);
// }
