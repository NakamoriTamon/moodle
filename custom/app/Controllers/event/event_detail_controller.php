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
if (!empty($event)) {

    foreach ($event['lecture_formats'] as $lecture_format) {
        $lecture_format_id = $lecture_format['lecture_format_id'];

        foreach ($lectureFormats as $lectureFormat) {
            if ($lectureFormat['id'] == $lecture_format_id) {
                $select_lecture_formats[] = $lectureFormat;
                break;
            }
        }
    }

    foreach ($event['categorys'] as $select_category) {
        $category_id = $select_category['category_id'];

        foreach ($categorys as $category) {
            if ($category['id'] == $category_id) {
                $select_categorys[] = $category;
                break;
            }
        }
    }

    $tutor_ids = [];
    $tutor_names = [];
    foreach($event['course_infos'] as $select_course) {
        if(!empty($select_course['id'])) {
            $deadline_date = new DateTime($select_course['deadline_date']);
            // 現在時刻のUNIXタイムスタンプ
            $current_timestamp = new DateTime();
            if($current_timestamp > $deadline_date) {
                $select_course['close_date'] = 1;
            }
            
            $event['select_course'][$select_course['no']] = $select_course;
            
            if(isset($select_course['details'])) {
                foreach($select_course['details'] as $details) {
                    $tutor_id = $details['tutor_id'];
                    $tutor_name = $details['tutor_name'];
                    if (count($tutor_ids) == 0 || (count($tutor_ids) > 0 && !in_array($tutor_id, $tutor_ids))) {
                        if(!empty($tutor_id)) {
                            $tutor_ids[] = $tutor_id;
                        } else {
                            if(!empty($tutor_name)) {
                                $tutor_names[] = $tutor_name;
                            }
                        }
                    }
                }
            }
        }
    }
    // 重複を削除
    $tutor_ids = array_unique($tutor_ids);
    $tutor_names = array_unique($tutor_names);
    
    foreach ($tutor_ids as $tutor_id) {
        $select_tutor[] = $tutorModel->getTutorsById($tutor_id);
    }
}
