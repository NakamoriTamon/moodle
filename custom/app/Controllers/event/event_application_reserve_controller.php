<?php
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventApplicationModel.php');

class EventReserveController
{
    public function index($id = null)
    {
        global $DB;
        $event_application = $DB->get_record_sql(
            "SELECT * FROM {event_application} WHERE id = ?",
            [$id]
        );
        return $event_application;
    }

    public function getReserveById($id = null)
    {
        global $DB;
        $event_list = $DB->get_record_sql(
            "SELECT * FROM {event} WHERE id = ?",
            [$id]
        );
        return $event_list;
    }

    public function getUserGardianById($id = null)
    {
        global $DB;
        $user_list = $DB->record_exists_sql(
            "SELECT * FROM {user} WHERE id = ? AND guardian_kbn = ?",
            [$id, 1]
        );
        return $user_list;
    }
}
