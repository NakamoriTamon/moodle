<?php
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/MovieModel.php');

class EventRegisterController
{
    private $movieModel;

    public function __construct()
    {
        $this->movieModel = new MovieModel();
    }

    public function events(int $page = 1, int $perPage = 10)
    {
        global $DB;
        // 全件取得（イベント申し込みの全レコード）
        $event_application_courses = $DB->get_records_sql(
            "SELECT eac.*
             FROM {event_application_course_info} eac
             JOIN {event_application} ea ON ea.id = eac.event_application_id
             WHERE ea.payment_date IS NOT NULL"
        );

        $event_list = [];
        foreach ($event_application_courses as $application) {
            $event_detail = $DB->get_record_sql("SELECT * FROM {event} WHERE id = ?", [$application->event_id]);
            if ($event_detail) {
                $event_list[] = $event_detail;
            }
        }
        $totalCount = count($event_list);
        $offset = ($page - 1) * $perPage;
        $paginatedEvents = array_slice($event_list, $offset, $perPage);

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
