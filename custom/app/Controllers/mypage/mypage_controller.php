<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');

class MypageController {
    private $DB;
    private $USER;
    private $eventApplicationModel;

    public function __construct() {
        global $DB, $USER; 
        $this->DB = $DB;
        $this->USER = $USER;
        $this->eventApplicationModel = new EventApplicationModel();
    }

    // $lastname_kana = "";
    // $firstname_kana = "";
    // ユーザー情報を取得
    public function getUser() {
        return $this->DB->get_record(
            'user',
            ['id' => $this->USER->id],
            'id, name, name_kana, email, phone1, city, guardian_kbn, birthday, guardian_name, guardian_email, description'
        );
    }

    // 適塾記念情報を取得
    public function getTekijukuCommemoration() {
        return $this->DB->get_record(
            'tekijuku_commemoration',
            ['fk_user_id' => $this->USER->id],
            'id, number, type_code, name, kana, sex, post_code, address, tell_number, email, payment_method, note, is_published, is_subscription'
        );
    }

    // イベント申し込み情報を取得
    public function getEventApplications() {
        $sql = "
            SELECT 
                ea.id AS event_application_id,
                ea.event_id,
                ea.user_id,
                ea.price,
                ea.ticket_count,
                ea.payment_date,
                e.id AS event_id,
                e.name AS event_name,
                e.venue_name AS venue_name,
                ci.id AS course_id,
                ci.course_date AS course_date
            FROM 
                {event_application} ea
            JOIN 
                {event} e ON ea.event_id = e.id
            LEFT JOIN 
                {event_application_course_info} eaci ON ea.id = eaci.event_application_id
            LEFT JOIN 
                {course_info} ci ON eaci.course_info_id = ci.id
            WHERE 
                ea.user_id = :user_id
        ";
        $params = ['user_id' => $this->USER->id];

        return $this->DB->get_records_sql($sql, $params);
    }
}