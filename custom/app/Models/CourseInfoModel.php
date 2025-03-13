
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
            SELECT 
                ci.id AS ci_id,
                no,
                ca.participant_mail, 
                e.start_hour,
                e.name,
                e.venue_name
            FROM 
                mdl_course_info ci
            INNER JOIN 
                mdl_event_application_course_info ca ON ci.id = ca.course_info_id
            INNER JOIN 
                mdl_event e ON ca.event_id = e.id
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

