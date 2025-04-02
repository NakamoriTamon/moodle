<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/MovieModel.php');

class EventRegisterController
{
    private $movieModel;
    private $USER;

    public function __construct()
    {
        global $USER;
        $this->USER = $USER;
        $this->movieModel = new MovieModel();
    }

    public function events(int $page = 1, int $perPage = 10)
    {
        try {
            global $DB;

            // ページネーションの設定
            $offset = ($page - 1) * $perPage;

            // 本人用チケットのみを取得（TICKET_TYPE['SELF']）
            $self_ticket_type = TICKET_TYPE['SELF'];
            
            $now = new DateTime();
            $now_time = $now->format('Y-m-d H:i:s');
            $day_end_time = $now->format('Y-m-d 23:59:59');
            $sql = "WITH cmt_data AS (
            SELECT 
                cm.course_info_id, 
                GROUP_CONCAT(cm.file_name ORDER BY cm.file_name) AS materials
            FROM {course_material} cm
            GROUP BY cm.course_info_id
        ),
        cmv_data AS (
            SELECT 
                cmv.course_info_id, 
                GROUP_CONCAT(cmv.file_name ORDER BY cmv.file_name) AS movies
            FROM {course_movie} cmv
            GROUP BY cmv.course_info_id
        )
        SELECT 
            eaci.id AS event_application_course_info_id,
            ea.id AS event_application_id,
            ea.event_id,
            ea.user_id,
            ea.price,
            ea.ticket_count,
            ea.payment_date,
            ea.event_application_package_types,
            e.name AS event_name,
            e.venue_name,
            e.thumbnail_img,
            e.archive_streaming_period,
            e.material_release_period,
            e.start_hour,
            ci.id AS course_id,
            ci.no,
            ci.course_date,
            ci.release_date,
            ci.material_release_date,
            eaci.participation_kbn,
            eaci.ticket_type,
            cmt_data.materials,
            cmv_data.movies
        FROM 
            {event_application_course_info} eaci
        JOIN 
            {event_application} ea ON ea.id = eaci.event_application_id
        JOIN 
            {event} e ON e.id = ea.event_id
        JOIN 
            {course_info} ci ON ci.id = eaci.course_info_id
        LEFT JOIN 
            cmt_data ON cmt_data.course_info_id = ci.id
        LEFT JOIN 
            cmv_data ON cmv_data.course_info_id = ci.id
        WHERE 
            (ea.payment_date IS NOT NULL OR ea.pay_method = 4)
            AND ea.user_id = :user_id
            AND eaci.ticket_type = :self_ticket_type
            AND (
                -- リリース日がNULLの場合: 開催日+23:59:59 を公開終了とする
                (ci.release_date IS NULL AND ci.course_date >= '$day_end_time')

                -- リリース日がある場合: `release_date + archive_streaming_period` で公開終了を計算
                OR (
                    (ci.release_date IS NOT NULL OR ci.material_release_date IS NOT NULL)
                    AND GREATEST(
                        CASE 
                            WHEN ci.release_date IS NOT NULL THEN DATE_ADD(ci.release_date, INTERVAL e.archive_streaming_period DAY)
                            ELSE '1970-01-01'
                        END,
                        CASE
                            WHEN ci.material_release_date IS NOT NULL THEN DATE_ADD(ci.material_release_date, INTERVAL e.material_release_period DAY)
                            ELSE '1970-01-01'
                        END
                    ) >= '$now_time'
                )
            )
        ORDER BY 
            ci.course_date ASC
        LIMIT $perPage OFFSET $offset";

            // パラメータ設定
            $params = [
                'user_id' => $this->USER->id,
                'self_ticket_type' => $self_ticket_type,
            ];

            // トータル件数を取得するためのクエリ
            $count_sql = "
            WITH cmt_data AS (
            SELECT 
                cm.course_info_id, 
                GROUP_CONCAT(cm.file_name ORDER BY cm.file_name) AS materials
            FROM {course_material} cm
            GROUP BY cm.course_info_id
        ),
        cmv_data AS (
            SELECT 
                cmv.course_info_id, 
                GROUP_CONCAT(cmv.file_name ORDER BY cmv.file_name) AS movies
            FROM {course_movie} cmv
            GROUP BY cmv.course_info_id
        )
        SELECT 
            COUNT(eaci.id) as count
        FROM 
            {event_application_course_info} eaci
        JOIN 
            {event_application} ea ON ea.id = eaci.event_application_id
        JOIN 
            {event} e ON e.id = ea.event_id
        JOIN 
            {course_info} ci ON ci.id = eaci.course_info_id
        LEFT JOIN 
            cmt_data ON cmt_data.course_info_id = ci.id
        LEFT JOIN 
            cmv_data ON cmv_data.course_info_id = ci.id
        WHERE 
            (ea.payment_date IS NOT NULL OR ea.pay_method = 4)
            AND ea.user_id = :user_id
            AND eaci.ticket_type = :self_ticket_type
            AND (
                -- リリース日がNULLの場合: 開催日+23:59:59 を公開終了とする
                (ci.release_date IS NULL AND ci.course_date >= '$now_time')

                -- リリース日がある場合: `release_date + archive_streaming_period` で公開終了を計算
                OR (
                    (ci.release_date IS NOT NULL OR ci.material_release_date IS NOT NULL)
                    AND GREATEST(
                        CASE 
                            WHEN ci.release_date IS NOT NULL THEN DATE_ADD(ci.release_date, INTERVAL e.archive_streaming_period DAY)
                            ELSE '1970-01-01'
                        END,
                        CASE
                            WHEN ci.material_release_date IS NOT NULL THEN DATE_ADD(ci.material_release_date, INTERVAL e.material_release_period DAY)
                            ELSE '1970-01-01'
                        END
                    ) >= '$now_time'
                )
            )
        ORDER BY 
            ci.course_date ASC
        ";

            // 総件数取得
            $totalCount = (int) $DB->count_records_sql($count_sql, $params);

            // 総ページ数計算
            $totalPages = ceil($totalCount / $perPage);

            // SQLでイベント申し込み情報を取得
            $events = $DB->get_records_sql($sql, $params);

            // materialsとmoviesを配列に変換して追加
            foreach ($events as $event) {
                // 表示時に必要なnameプロパティの互換性維持
                $event->name = $event->event_name;

                // course_info_idプロパティの追加（表示ページでの互換性のため）
                $event->course_info_id = $event->course_id;

                // material_namesを配列に変換
                if (!empty($event->materials)) {
                    $event->materials = explode(',', $event->materials);
                }

                // movie_namesを配列に変換
                if (!empty($event->movies)) {
                    $event->movies = explode(',', $event->movies);
                }
            }

            // ページネーション情報とデータをまとめて返す
            $paginate_data = [
                'data' => $events,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'per_page' => $perPage,
                    'total_count' => $totalCount
                ]
            ];

            return $paginate_data;
        } catch (Exception $e) {
            error_log('events Error: ' . $e->getMessage());
            var_dump($e);
            // エラー時は空の結果を返す
            return [
                'data' => [],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => 0,
                    'per_page' => $perPage,
                    'total_count' => 0
                ],
                'error' => $e->getMessage()
            ];
        }
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
            [$course_info_list->course_info_id]
        );
        return $pdf_list;
    }

    public function course_list($id = null)
    {
        global $DB;
        $course_list = $DB->get_record_sql(
            "SELECT * FROM {course_info} WHERE id = ?",
            [$id]
        );
        return $course_list;
    }

    public function movie_list($id = null)
    {
        global $DB;
        $course_info_list = $DB->get_record_sql(
            "SELECT * FROM {event_course_info} WHERE event_id = ?",
            [$id]
        );
        $movie_list = $DB->get_record_sql(
            "SELECT * FROM {course_movie} WHERE course_info_id = ?",
            [$course_info_list->course_info_id]
        );
        return $movie_list;
    }

    public function event_list($id = null)
    {
        global $DB;

        $movie = [];
        $is_display = false;
        $is_single = false;
        $is_double_speed = false;
        $course_info_id = null;

        $event = $DB->get_record_sql(
            "SELECT * FROM {event} WHERE id = ?",
            [$id]
        );
        $course_info_list = $DB->get_record_sql(
            "SELECT * FROM {event_course_info} WHERE event_id = ?",
            [$event->id]
        );
        if ($course_info_list) {
            $course_info_id = $course_info_list->course_info_id;
            // 講義動画を取得
            $is_double_speed = $event->is_double_speed;
            if (!empty($event)) {
                // 単発イベントの場合
                if ($event->event_kbn == 1) {
                    $course_no = 1;
                    $is_single = true;
                    $is_display = true;
                }
            }
            // 複数回イベントの場合
            if ($event->event_kbn == 2 && !empty($course_no)) {
                $is_display = true;
            }
            $movie = $this->movieModel->getMovieByInfoId($course_info_id);

            $data = [
                'movie' => $movie,
                'is_display' => $is_display,
                'is_single' => $is_single,
                'is_double_speed' => $is_double_speed,
                'course_info_id' => $course_info_id,
                'course_no' => $course_no,
            ];
        }

        return $data;
    }
}
