<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/lib/classes/context/system.php');
require '/var/www/vendor/autoload.php';

class UserPassResetController
{
    public function index($token = null)
    {
        global $DB;
        // トークンの有効性を確認
        $reset_data = $DB->get_record('user_password_resets', ['token' => $token]);
        if (empty($reset_data)) {
            return false;
        }
        if ($reset_data->timerequested + 6000 < time()) {
            return false;
        }
        return true;
    }
}
