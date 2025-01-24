<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/LectureFormatModel.php');

$categoryModel = new CategoryModel();
$lectureFormatModel = new LectureFormatModel();

$categorys = $categoryModel->getCategorys();
$lectureFormats = $lectureFormatModel->getLectureFormats();

class EventController
{

    private $eventModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
    }

    public function index()
    {
        $events = $this->eventModel->getEvents();

        // 開催前、開催中、開催終了判定

        return $events;
    }
}
