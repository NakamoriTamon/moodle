<?php
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventApplicationModel.php');
require_once($CFG->dirroot . '/custom/app/Models/CategoryModel.php');
require_once($CFG->dirroot . '/custom/app/Models/LectureFormatModel.php');
require_once($CFG->dirroot . '/custom/app/Models/TutorModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventCustomFieldCategoryModel.php');
require_once($CFG->dirroot . '/custom/app/Models/TargetModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventSurveyCustomFieldCategoryModel.php');

$categoryModel = new CategoryModel();
$lectureFormatModel = new LectureFormatModel();
$tutorModel = new TutorModel();
$customFieldCategoryModel = new EventCustomFieldCategoryModel();
$targetModel = new TargetModel();
$curveyCustomFieldCategoryModel = new EventSurveyCustomFieldCategoryModel();

$categorys = $categoryModel->getCategories();
$lectureFormats = $lectureFormatModel->getLectureFormats();
$tutors = $tutorModel->getTutors();
$tutor_options = "";
foreach($tutors as $tutor) {
    $tutor_options .= "<option value=" . $tutor['id'] . ">" . $tutor['name'] . "</option>";
}
$event_category_list = $customFieldCategoryModel->getCustomFieldCategory();
// アンケートカスタム区分
$curvey_custom_list = $curveyCustomFieldCategoryModel->getSurveyCustomFieldCategory();
$targets = $targetModel->getTargets();

class EventEditController {

    private $eventModel;
    private $eventApplicationModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->eventApplicationModel = new EventApplicationModel();
    }
    
    public function getEventData($id) {
        if ($id === null) {
            return null; // 新規作成の場合
        }

        $events = $this->eventModel->getEventById($id);
        
        if (!is_null($id) && empty($events)) {
            $_SESSION['message_error'] = '選択したイベントは存在しません ';
            redirect(new moodle_url('/custom/admin/app/Views/event/index.php'));
            exit;
        }
        
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

    // 購入枚数を表示
    public function getTicketCount($id) {
        if ($id === null) {
            return []; // 新規作成の場合
        }
        $tickets = $this->eventApplicationModel->getSumTicketCountByEventId($id, null, false);

        return $tickets;
    }
}
?>