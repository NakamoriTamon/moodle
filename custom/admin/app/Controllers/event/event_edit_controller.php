<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/LectureFormatModel.php');

$categoryModel = new CategoryModel();
$lectureFormatModel = new LectureFormatModel();

$categorys = $categoryModel->getCategorys();
$lectureFormats = $lectureFormatModel->getLectureFormats();

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

        return $events;
    }
}
?>