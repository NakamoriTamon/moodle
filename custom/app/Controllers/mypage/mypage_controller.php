<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');

class MypageController
{
    private $DB;
    private $USER;
    private $eventApplicationModel;

    public function __construct()
    {
        global $DB, $USER;
        $this->DB = $DB;
        $this->USER = $USER;
        $this->eventApplicationModel = new EventApplicationModel();
    }

    // $lastname_kana = "";
    // $firstname_kana = "";
    // ユーザー情報を取得
    public function getUser()
    {
        return $this->DB->get_record(
            'user',
            ['id' => $this->USER->id],
            'id, name, name_kana, email, phone1, city, guardian_kbn, birthday, guardian_name, guardian_email, description'
        );
    }

    // 適塾記念情報を取得
    public function getTekijukuCommemoration()
    {
        return $this->DB->get_record(
            'tekijuku_commemoration',
            ['fk_user_id' => $this->USER->id],
            'id, number, type_code, name, kana, sex, post_code, address, tell_number, email, payment_method, note, is_published, is_subscription'
        );
    }

    // イベント申し込み情報を取得
    public function getEventApplications()
    {
        $sql = "
            WITH ranked_courses AS (
                SELECT 
                    ci.id AS course_id,
                    ci.course_date,
                    eaci.event_id,
                    eaci.event_application_id,
                    ROW_NUMBER() OVER (PARTITION BY eaci.event_id ORDER BY ci.course_date ASC) AS rn
                FROM 
                    {course_info} ci
                JOIN 
                    {event_application_course_info} eaci ON ci.id = eaci.course_info_id
                WHERE 
                    ci.course_date >= CURDATE()
            ),
            filtered_applications AS (
                SELECT 
                    ea.*,
                    ROW_NUMBER() OVER (PARTITION BY ea.event_id ORDER BY ea.application_date ASC) AS app_rn
                FROM 
                    {event_application} ea
            )
            SELECT 
                fa.id AS event_application_id,
                fa.event_id,
                fa.user_id,
                fa.price,
                fa.ticket_count,
                fa.payment_date,
                e.id AS event_id,
                e.name AS event_name,
                e.venue_name AS venue_name,
                rc.course_id,
                rc.course_date
            FROM 
                filtered_applications fa
            JOIN 
                {event} e ON fa.event_id = e.id
            LEFT JOIN 
                ranked_courses rc 
                ON fa.event_id = rc.event_id
                AND rc.rn = 1
            WHERE 
                fa.user_id = :user_id
                AND fa.app_rn = 1
            ORDER BY 
                fa.event_id, rc.course_date;
        ";
        $params = ['user_id' => $this->USER->id];

        return $this->DB->get_records_sql($sql, $params);
    }
}
