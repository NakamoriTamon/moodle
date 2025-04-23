<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/MovieModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationCourseInfoModel.php');
global $DB;

class EventMovieController
{
    private $movieModel;
    private $eventApplicationCourseInfoModel;

    public function __construct()
    {
        $this->movieModel = new MovieModel();
        $this->eventApplicationCourseInfoModel = new EventApplicationCourseInfoModel();
    }

    public function index($course_info_id)
    {

        global $USER;
        global $DB;

        $path = null;
        $is_payment = false;
        $movie = $this->movieModel->getMovieByInfoId($course_info_id);
        $event_application_list = $this->eventApplicationCourseInfoModel->getByCourseInfoId($course_info_id, []);
        foreach ($event_application_list as $event_application) {
            $application_list = $event_application['application'];
            foreach ($application_list as $application) {
                if ($USER->id == $application['user_id']) {
                    $is_payment = true;
                    if ($application['event']['event_kbn'] == EVERY_DAY_EVENT) {
                        $path = "/uploads/movie/" . $course_info_id . "/1/" . $movie['file_name'];
                    } else {
                        $path = "/uploads/movie/" . $course_info_id . "/" . $event_application['course_info']['no'] . "/" . $movie['file_name'];
                    }
                    $event = $DB->get_record('event', ['id' => $application['event_id']]);
                    $is_double_speed = $event->is_double_speed;
                }
            }
        }
        $data = [
            'path' => $path,
            'is_payment' => $is_payment,
            'is_double_speed' => $is_double_speed,
        ];

        return $data;
    }
}
