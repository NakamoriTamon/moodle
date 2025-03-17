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
        global $DB;

        // 全件取得（イベント申し込みの全レコード）
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
            eac.event_application_id, 
            eac.course_info_id, 
            e.id AS event_id, 
            e.name, 
            e.thumbnail_img,
            e.archive_streaming_period, 
            e.start_hour,
            ci.no, 
            ci.release_date, 
            ci.course_date, 
            cmt_data.materials, 
            cmv_data.movies
        FROM {event_application_course_info} eac
        JOIN {event_application} ea ON ea.id = eac.event_application_id
        JOIN {event} e ON e.id = ea.event_id
        JOIN {course_info} ci ON ci.id = eac.course_info_id
        LEFT JOIN cmt_data ON cmt_data.course_info_id = ci.id
        LEFT JOIN cmv_data ON cmv_data.course_info_id = ci.id
        WHERE ea.payment_date IS NOT NULL
        AND ea.user_id = :user_id";

        // パラメータ設定
        $params = [
            'user_id' => $this->USER->id,
        ];

        // SQLでイベント申し込み情報を取得
        $event_application_courses = $DB->get_records_sql($sql, $params);

        // 総件数を取得
        $totalCount = count($event_application_courses);

        // ページネーションのオフセット
        $offset = ($page - 1) * $perPage;

        // 必要なページ分のデータを抽出
        $paginatedEvents = array_slice($event_application_courses, $offset, $perPage);

        // material_names と movie_names を配列に変換して追加
        foreach ($paginatedEvents as $event) {
            // material_names を配列に変換
            if (!empty($event->materials)) {
                $event->materials = explode(',', $event->materials);
            }

            // movie_names を配列に変換
            if (!empty($event->movies)) {
                $event->movies = explode(',', $event->movies);
            }
        }

        // 結果を返す
        return [
            'data' => $paginatedEvents,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_count' => $totalCount,
                'total_pages' => ceil($totalCount / $perPage)
            ]
        ];
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
