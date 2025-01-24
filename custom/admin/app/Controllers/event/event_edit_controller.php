<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/LectureFormatModel.php');
require_once('/var/www/html/moodle/custom/app/Models/TutorModel.php');

$categoryModel = new CategoryModel();
$lectureFormatModel = new LectureFormatModel();
$tutorModel = new TutorModel();

$categorys = $categoryModel->getCategorys();
$lectureFormats = $lectureFormatModel->getLectureFormats();
$tutors = $tutorModel->getTutors();

class EventEditController {

    private $eventModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
    }
    
    public function getEventData($id) {
        if ($id === null) {
            return null; // 新規作成の場合
        }

        $events = $this->eventModel->getEventById($id);
        $select_lecture_formats = [];
        $select_categorys = [];
        $select_courses = [];
        if(!empty($events)) {
            foreach($events['lecture_formats'] as $select_category) {
                $select_lecture_formats[] = $select_category['lecture_format_id'];
            }
            $events['select_lecture_formats'] = $select_lecture_formats;

            foreach($events['categorys'] as $select_category) {
                $select_categorys[] = $select_category['category_id'];
            }
            $events['select_categorys'] = $select_categorys;

            foreach($events['course_infos'] as $select_course) {
                $events['select_course'][$select_course['no']] = $select_course;
            }
            
        } else {
            $events['select_lecture_formats'] = $select_lecture_formats;
            $events['select_categorys'] = $select_categorys;
            $events['select_course'] = $select_courses;
        }

        return $events;
    }
}
?>