<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

class SurveyApplicationController
{
    public function index(int $event_id)
    {
        global $DB;
        global $USER;

        $survey_list = $DB->get_record('survey_application', array('user_id' => $USER->id, 'event_id' => $event_id));

        return $survey_list;
    }

    public function events(int $event_id)
    {
        global $DB;

        $events = $DB->get_record_sql("SELECT * FROM {event} WHERE id = ?", [$event_id]);

        return $events;
    }
}
