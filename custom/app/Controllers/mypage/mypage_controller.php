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
            'id, name, name_kana, email, phone1, city, guardian_kbn, birthday, guardian_name, guardian_email, description, notification_kbn',
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
    public function getEventApplications($offset = 0, $limit = 1, $page = 1, $get_application = 'booking')
    {
        try {

            // limit と offset を整数にキャスト
            $limit = intval($limit);
            $offset = intval($offset);
            $page = intval($page); // 現在のページ番号

            // ページネーションの設定
            $perPage = $limit; // 1ページあたりのアイテム数

            // SQLクエリ（ページネーション対応）
            $sql = $get_application == 'booking' ? "
                WITH ranked_courses AS (
                    SELECT 
                        ci.id AS course_id,
                        ci.no,
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
                    rc.no,
                    rc.course_date
                FROM 
                    filtered_applications fa
                JOIN 
                    {event} e ON fa.event_id = e.id
                JOIN 
                    ranked_courses rc 
                    ON fa.event_id = rc.event_id
                    AND rc.rn = 1
                WHERE 
                    fa.user_id = :user_id
                    AND fa.app_rn = 1
                ORDER BY 
                    fa.event_id, rc.course_date
                LIMIT $limit OFFSET $offset;
            " : "
            WITH ranked_courses AS (
                SELECT 
                    ci.id AS course_id,
                    ci.no,
                    ci.course_date,
                    eaci.event_id,
                    eaci.event_application_id
                FROM 
                    {course_info} ci
                JOIN 
                    {event_application_course_info} eaci ON ci.id = eaci.course_info_id
                WHERE 
                    ci.course_date < CURDATE()
            ),
            filtered_applications AS (
                SELECT 
                    ea.*
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
                rc.no,
                rc.course_date
            FROM 
                filtered_applications fa
            JOIN 
                {event} e ON fa.event_id = e.id
            JOIN 
                ranked_courses rc 
                ON fa.id = rc.event_application_id
            WHERE 
                fa.user_id = :user_id
            ORDER BY 
                fa.event_id, rc.course_date
            LIMIT $limit OFFSET $offset;
        ";

            // パラメータ設定
            $params = [
                'user_id' => $this->USER->id,
            ];

            // トータルカウントの取得
            $totalCount = (int) $this->getTotalEventApplicationsCount($get_application);
            // トータルページ数の計算
            $totalPages = ceil($totalCount / $perPage);

            // SQLクエリを実行してデータを取得
            $data = $this->DB->get_records_sql($sql, $params);

            // ページネーション情報とデータをまとめて返す
            $pagenete_data = [
                'data' => $data,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'per_page' => $perPage,
                    'total_count' => $totalCount
                ]
            ];
        } catch (Exception $e) {
            var_dump($e);
        }

        return $pagenete_data;
    }

    private function getTotalEventApplicationsCount($get_application)
    {
        try {
            $sql = $get_application == 'booking' ? "
                WITH filtered_applications AS (
                SELECT 
                    ea.*,
                    ROW_NUMBER() OVER (PARTITION BY ea.event_id ORDER BY ea.application_date ASC) AS app_rn
                FROM 
                    {event_application} ea
                JOIN 
                    {event} e ON ea.event_id = e.id
                JOIN 
                    {event_application_course_info} eaci ON ea.id = eaci.event_application_id
                JOIN 
                    {course_info} ci ON eaci.course_info_id = ci.id  -- course_info とイベントの関連付け
                WHERE 
                    ci.course_date >= CURDATE()  -- 未来のコースのみ
                )
                SELECT COALESCE(COUNT(*), 0) AS count
                FROM filtered_applications fa
                WHERE fa.user_id = :user_id
                AND fa.app_rn = 1;
            " : "
                WITH filtered_applications AS (
                SELECT 
                    ea.*
                FROM 
                    {event_application} ea
                JOIN 
                    {event} e ON ea.event_id = e.id
                JOIN 
                    {event_application_course_info} eaci ON ea.id = eaci.event_application_id
                JOIN 
                    {course_info} ci ON eaci.course_info_id = ci.id  -- course_info とイベントの関連付け
                WHERE 
                    ci.course_date < CURDATE()  -- 未来のコースのみ
                )
                SELECT COALESCE(COUNT(*), 0) AS count
                FROM filtered_applications fa
                WHERE fa.user_id = :user_id;
            ";

            $params = ['user_id' => $this->USER->id];
            $count = $this->DB->get_record_sql($sql, $params)->count;
        } catch (Exception $e) {
            var_dump($e);
        }
        return $count;
    }
}
