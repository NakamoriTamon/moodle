<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/LectureFormatModel.php');
require_once('/var/www/html/moodle/custom/app/Models/TargetModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');

$eventModel = new EventModel();
$categoryModel = new CategoryModel();
$lectureFormatModel = new LectureFormatModel();
$targetModel = new TargetModel();
$eventApplicationModel = new EventApplicationModel();

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
$target = $_GET['target'] ?? '';
$keyword = $_GET['keyword'] ?? '';
$event_start_date = $_GET['event_start_date'] ?? '';
$event_end_date = $_GET['event_end_date'] ?? '';

$events = $eventModel->getEvents([
    'event_status' => $event_status,
    'deadline_status' => $deadline_status,
    'lecture_format_id' => $lecture_format_id,
    'target' => $target,
    'keyword' => $keyword,
    'event_start_date' => $event_start_date,
    'event_end_date' => $event_end_date,
    'category_id' => $category_id
], $currentPage, $perPage);

$now = new DateTime();
$now = $now->format('Ymd');

if (!empty($events)) {
    foreach ($events as &$event) {
        $select_lecture_formats = [];
        $select_categorys = [];
        $select_courses = [];

        foreach ($event['lecture_formats'] as $select_category) {
            $select_lecture_formats[] = $select_category['lecture_format_id'];
        }
        $event['select_lecture_formats'] = $select_lecture_formats;

        foreach ($event['categorys'] as $select_category) {
            $select_categorys[] = $select_category['category_id'];
        }
        $event['select_categorys'] = $select_categorys;

        $count = 1;
        foreach ($event['course_infos'] as $select_course) {
            if ($count > 2 || empty($select_course['course_date'])) {
                break;
            }

            $course_date = (new DateTime($select_course['course_date']))->format('Ymd');

            if ($course_date >= $now) {
                $event['select_course'][$select_course['no']] = $select_course;
                $count++;
            }
        }

        // 定員数に空きがあるか確認を行う
        $capacityFlg = false;
        if($event['event_kbn'] == PLURAL_EVENT && $event['capacity'] > 0){ // 複数回シリーズのイベント
            foreach ($event['course_infos'] as $select_course){
                // n回講座の開催日の一日前が既に過ぎているものはチェックの対象外とする
                $deadline = !empty($select_course['deadline_date']) ? (new DateTime($select_course['deadline_date']))->format('Ymd') : ((new DateTime($select_course['course_date']))->modify('-1 day'))->format('Ymd');
                if ($deadline < $now) {
                    continue;
                }
                $capacityFlg = checkCapacity($event['id'], $select_course['id']);
                if($capacityFlg){
                    break;
                }
            }
        }elseif($event['event_kbn'] == EVERY_DAY_EVENT && $event['capacity'] > 0){ // 期間内に毎日開催のイベント
            // 開催日時になる前ならチェック対象とする
            $deadline_day = (new DateTime($event['end_event_date']))->format('Y-m-d');
            $deadline_hour = (new DateTime($event['end_hour']))->format('H:i:s');
            $deadline = (new DateTime($deadline_day.' '.$deadline_hour))->format('YmdHis');
            if ($deadline >= (new DateTime())->format('YmdHis')) {
                $capacityFlg = checkCapacity($event['id'], null);
            }
        }elseif($event['event_kbn'] == SINGLE_EVENT && $event['capacity'] > 0){// 単発のイベント
            // 開催日が既に過ぎているものはチェックの対象外とする
            $deadline = !empty($event['deadline']) ? (new DateTime($event['deadline']))->format('Ymd') : ((new DateTime($event['event_date']))->modify('-1 day'))->format('Ymd');
            if ($deadline >= $now) {
                $capacityFlg = checkCapacity($event['id'], null);
            }
        }else{
            $capacityFlg = true;
        }
        $event['capacity_flg'] = $capacityFlg;
    }
}

$totalCount = $eventModel->getEventTotal([
    'event_status' => $event_status,
    'deadline_status' => $deadline_status,
    'lecture_format_id' => $lecture_format_id,
    'target' => $target,
    'keyword' => $keyword,
    'event_start_date' => $event_start_date,
    'event_end_date' => $event_end_date,
    'category_id' => $category_id
]);
// フォーム送信（POST）でコントローラーを呼び出す処理
$action = optional_param('action', '', PARAM_ALPHA); // アクションパラメータを取得

// 現在の検索条件を取得
$queryParams = $_GET; // GETパラメータを取得
unset($queryParams['page']); // ページ番号は後で設定するため削除
$queryString = http_build_query($queryParams); // クエリ文字列を作成

$_SESSION['old_input'] = $_GET; // 入力内容も保持
if ($action === 'index') {
    include '/var/www/html/moodle/custom/app/Views/event/index.php';
}

function checkCapacity($eventId,$courseInfoId){
    $capacity_flg = false;
    // $courseInfoIdが無い場合、空数が最小のレコードを取得
    // $courseInfoIdが有る場合、指定した開催日のレコードを取得
    global $eventApplicationModel;
    $result = $eventApplicationModel->getSumTicketCountByEventId($eventId, empty($courseInfoId) ? null : $courseInfoId, true);
    if(!empty($result)) {
        $ticket_data = $result[0];
        $aki_ticket = $ticket_data['available_tickets'];
        $capacity_flg = $aki_ticket > 0 ? true : false;
    }else{
        $capacity_flg = true;
    }

    return $capacity_flg;
}