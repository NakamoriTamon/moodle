<?php
require_once('/var/www/html/moodle/config.php');

class CustomController
{
    public function index()
    {
        global $DB, $CFG;
        $event_category_list = $DB->get_record('event_customfield_category', ['is_delete' => false]);
        // var_dump($event_category_list);

        var_dump($event_category_list);
    }
}
