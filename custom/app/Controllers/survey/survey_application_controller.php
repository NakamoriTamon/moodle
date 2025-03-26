<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

class SurveyApplicationController
{
    public function surveys(int $course_info_id)
    {
        global $DB;
        global $USER;

        // レコード取得（アンケート関連情報を取得）
        $sql = "SELECT 
                ci.id,
                ci.no,
                ci.course_date, 
                e.id AS event_id, 
                e.name,
                e.start_hour,
                e.end_hour
            FROM mdl_course_info ci
            JOIN mdl_event_course_info ec ON ec.course_info_id = ci.id
            JOIN mdl_event e ON e.id = ec.event_id
            WHERE ci.id = :course_info_id";

        // パラメータ設定
        $params = [
            'course_info_id'  => $course_info_id,
        ];

        // SQLでイベント申し込み情報を取得
        $event_application = $DB->get_record_sql($sql, $params);

        // アンケートが存在しているか確認
        $survey_application = $DB->record_exists('survey_application', array('course_info_id' => $course_info_id, 'user_id'  => $USER->id));

        // 結果を返す
        return [
            'data' => $event_application,
            'exist' => $survey_application,
        ];
    }
}
