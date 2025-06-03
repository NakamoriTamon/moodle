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

$currentPage = 1; // 現在のページ番号（デフォルト: 1）
$perPage = 10; // 1ページあたりの件数
// 検索条件を取得
// 現在日以降のイベントを表示
$event_start_date = $date = date('Y-m-d 00:00:00');;

$events = $eventModel->getEvents([
    'event_start_date' => $event_start_date,
    'is_not_reserved' => true
], $currentPage, $perPage);

$now = new DateTime();
$now = $now->format('Ymd');

$best_events = $eventModel->getEvents([
    // 'event_status' => [1, 2],
    'deadline_status' => [1, 2],
    'is_best' => true,
    'is_not_reserved' => true
], $currentPage, $perPage);

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
            if ($count > 2) {
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
        if ($event['event_kbn'] == PLURAL_EVENT && $event['capacity'] > 0) { // 複数回シリーズのイベント
            foreach ($event['course_infos'] as $select_course) {
                // n回講座の開催日の一日前が既に過ぎているものはチェックの対象外とする
                $deadline = !empty($select_course['deadline_date']) ? (new DateTime($select_course['deadline_date']))->format('Ymd') : ((new DateTime($select_course['course_date']))->modify('-1 day'))->format('Ymd');
                if ($deadline < $now) {
                    continue;
                }
                $capacityFlg = checkCapacity($event['id'], $select_course['id']);
                if ($capacityFlg) {
                    break;
                }
            }
        } elseif ($event['event_kbn'] == EVERY_DAY_EVENT && $event['capacity'] > 0) { // 期間内に毎日開催のイベント
            // 開催日時になる前ならチェック対象とする
            $deadline_day = (new DateTime($event['end_event_date']))->format('Y-m-d');
            $deadline_hour = (new DateTime($event['end_hour']))->format('H:i:s');
            $deadline = (new DateTime($deadline_day . ' ' . $deadline_hour))->format('YmdHis');
            if ($deadline >= (new DateTime())->format('YmdHis')) {
                $capacityFlg = checkCapacity($event['id'], null);
            }
        } elseif ($event['event_kbn'] == SINGLE_EVENT && $event['capacity'] > 0) { // 単発のイベント
            // 開催日が既に過ぎているものはチェックの対象外とする
            $deadline = !empty($event['deadline']) ? (new DateTime($event['deadline']))->format('Ymd') : ((new DateTime($event['event_date']))->modify('-1 day'))->format('Ymd');
            if ($deadline >= $now) {
                $capacityFlg = checkCapacity($event['id'], null);
            }
        } else {
            $capacityFlg = true;
        }
        $event['capacity_flg'] = $capacityFlg;
    }
}

function checkCapacity($eventId, $courseInfoId)
{
    $capacity_flg = false;
    // $courseInfoIdが無い場合、空数が最小のレコードを取得
    // $courseInfoIdが有る場合、指定した開催日のレコードを取得
    global $eventApplicationModel;
    $result = $eventApplicationModel->getSumTicketCountByEventId($eventId, empty($courseInfoId) ? null : $courseInfoId, true);
    if (!empty($result)) {
        $ticket_data = $result[0];
        $aki_ticket = $ticket_data['available_tickets'];
        $capacity_flg = $aki_ticket > 0 ? true : false;
    } else {
        $capacity_flg = true;
    }

    return $capacity_flg;
}
