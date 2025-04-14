
<?php
// require_once('/var/www/html/moodle/config.php');
class CourseInfoModel extends BaseModel
{
    /**
     * 明日の日付を基にリマインダー対象者を取得する
     * @return array
     */
    public function getReminderTargets()
    {
        global $DB;

        $tomorrow = date('Y-m-d', strtotime('tomorrow'));
        $tomorrow_end = date('Y-m-d', strtotime('+1 day', strtotime($tomorrow))); // 翌日の日付をPHP側で計算

        $sql = "
            WITH elf AS (
                SELECT *
                FROM {event_lecture_format}
                WHERE lecture_format_id = " . LIVE . "
            )
            SELECT 
                ca.id AS ca_id,
                ci.id AS ci_id,
                ci.no,
                ca.participant_mail, 
                e.start_hour,
                e.name,
                e.venue_name,
                e.real_time_distribution_url,
                elf.lecture_format_id
            FROM 
                mdl_event_application_course_info ca
            INNER JOIN 
                mdl_course_info ci ON ca.course_info_id = ci.id
            INNER JOIN 
                mdl_event e ON ca.event_id = e.id
            JOIN 
                elf ON elf.event_id = e.id
            WHERE 
                ci.course_date >= :tomorrow
            AND 
                ci.course_date < :tomorrow_end
        ";

        $params = [
            'tomorrow' => $tomorrow,
            'tomorrow_end' => $tomorrow_end
        ];
        try {
            $data = $DB->get_records_sql($sql, $params);

            return $data;
        } catch (Throwable $e) { // catch (Exception $e) から変更

            return []; // またはエラーを示す適切な値を返す
        }
    }
}
