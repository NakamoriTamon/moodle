<?php
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventApplicationModel.php');

class EventRegisterController
{
    public function events()
    {
        global $DB;
        $events = $DB->get_records_sql(
            "SELECT * FROM {event_application}"
        );
        foreach ($events as $event) {
            $event_list[] = $DB->get_record_sql(
                "SELECT * FROM {event} WHERE id = ?",
                [$event->event_id]
            );
        }
        return $event_list;
    }

    public function course_info_list($id = null)
    {
        global $DB;
        $course_info_list = $DB->get_record_sql(
            "SELECT * FROM {event_course_info} WHERE event_id = ?",
            [$id]
        );
        return $course_info_list;
    }

    public function pdf_list($id = null)
    {
        global $DB;
        $course_info_list = $DB->get_record_sql(
            "SELECT * FROM {event_course_info} WHERE event_id = ?",
            [$id]
        );
        $pdf_list = $DB->get_records_sql(
            "SELECT * FROM {course_material} WHERE course_info_id = ?",
            [$course_info_list->id]
        );
        return $pdf_list;
    }

    public function movie_list($id = null)
    {
        global $DB;
        $course_info_list = $DB->get_record_sql(
            "SELECT * FROM {event_course_info} WHERE event_id = ?",
            [$id]
        );
        $movie_list = $DB->get_records_sql(
            "SELECT * FROM {course_movie} WHERE course_info_id = ?",
            [$course_info_list->id]
        );
        return $movie_list;
    }
}
