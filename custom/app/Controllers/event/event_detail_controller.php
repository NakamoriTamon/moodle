<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/LectureFormatModel.php');
require_once('/var/www/html/moodle/custom/app/Models/TutorModel.php');
require_once('/var/www/html/moodle/custom/app/Models/TargetModel.php');

$eventModel = new EventModel();
$categoryModel = new CategoryModel();
$lectureFormatModel = new LectureFormatModel();
$tutorModel = new TutorModel();
$targetModel = new TargetModel();

$categorys = $categoryModel->getCategories();
$lectureFormats = $lectureFormatModel->getLectureFormats();
$targets = $targetModel->getTargets();

$id = $_GET['id'] ?? null;

$event = $eventModel->getEventById($id);

$select_lecture_formats = [];
$select_categorys = [];
$select_courses = [];
$select_tutor = [];
if(!empty($event)) {
    
    foreach($event['lecture_formats'] as $lecture_format) {
        $lecture_format_id = $lecture_format['lecture_format_id'];
    
        foreach ($lectureFormats as $lectureFormat) {
            if ($lectureFormat['id'] == $lecture_format_id) {
                $select_lecture_formats[] = $lectureFormat;
                break;
            }
        }
    }

    foreach($event['categorys'] as $select_category) {
        $category_id = $select_category['category_id'];
    
        foreach ($categorys as $category) {
            if ($category['id'] == $category_id) {
                $select_categorys[] = $category;
                break;
            }
        }
    }

    $tutor_ids = [];
    foreach($event['course_infos'] as $select_course) {
        $course_date = new DateTime($select_course['course_date']);
        // event_kbn:3　期間内毎日イベント開催の場合
        if($event['event_kbn'] == EVERY_DAY_EVENT && empty($event['deadline']) && empty($event['all_deadline'])) {
            // event_kbn:3
            // `end_hour` の時刻を設定
            list($hour, $minute, $second) = explode(':', $event['end_hour']);
            $course_date->setTime($hour, $minute, $second);
            
            // 現在時刻のUNIXタイムスタンプ
            $current_timestamp = new DateTime();
            if($current_timestamp > $course_date) {
                $select_course['close_date'] = 1;
            }
            
            $event['select_course'][$select_course['no']] = $select_course;
        } else {
            $event['select_course'][$select_course['no']] = $select_course;
        }
        
        if(isset($select_course['details'])) {
            $tutor_id = $select_course['details'][0]['tutor_id'];
            if(count($tutor_ids) == 0 || (count($tutor_ids) > 0 && !in_array($tutor_id, $tutor_ids))){
                $tutor_ids[] = $tutor_id;
            }
        }
    }
    foreach($tutor_ids as $tutor_id) {
        $select_tutor[] = $tutorModel->getTutorsById($tutor_id);
    }

}
