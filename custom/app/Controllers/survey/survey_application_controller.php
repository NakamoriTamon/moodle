<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventSurveyCustomFieldModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');

class SurveyApplicationController
{

    private $eventModel;
    private $eventSurveyCustomFieldModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->eventSurveyCustomFieldModel = new EventSurveyCustomFieldModel();
    }

    public function surveys(int $course_info_id, $formdata = null)
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
                e.event_kbn,
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

        if (empty($event_application)) {
            redirect(new moodle_url('/custom/app/Views/404.php'));
            exit;
        }

        // アンケートが存在しているか確認
        $survey_application = $DB->record_exists('survey_application', array('course_info_id' => $course_info_id, 'user_id'  => $USER->id));
        
        $event_id = $event_application->event_id;
        $event = $this->eventModel->getEventById($event_id);
        $event_survey_customfield_category_id = $event['event_survey_customfield_category_id'];

        $passage = '';
        if(!empty($event_survey_customfield_category_id)) {
            $surveyFieldList = $this->eventSurveyCustomFieldModel->getEventSurveyCustomFieldById($event_survey_customfield_category_id);
            
            $checked = '';
            $customfield_type_list = CUSTOMFIELD_TYPE_LIST;
            $params = null;
            if (!is_null($formdata) && isset($formdata)) {
                $params = $formdata;
            }
            foreach ($surveyFieldList as $fields) {
                $passage .= '<li><h4 class="sub_ttl">' . $fields['name'];
                if ($fields['field_type'] == 3) {
                    $passage .= '<span class="comment">※複数回答可</span></h4>';
                    $passage .= '<div class="list_field f_check">';
                    $options = explode(",", $fields['selection']);
                    foreach ($options as $i => $option) {
                        $name = "";
                        $name = $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'];
                        $checked_param = is_null($params) || !isset($params[$name]) ? [] : $params[$name];
                        $checked = isChoicesSelected($option, $checked_param, null) ? 'checked' : '';
                        $name .= '[]';
                        $passage .= '<div class="check_item"><label><input type="' . $customfield_type_list[$fields['field_type']] . '" name="' . $name . '" value="' . $option . '"' . $checked . '>' . $option . '</label></div>';
                    }
                    $passage .= '</li>';
                    continue;
                }
                if ($fields['field_type'] == 4) {
                    $passage .= '</h4>';
                    $passage .= '<div class="list_field f_radio">';
                    $options = explode(",", $fields['selection']);
                    foreach ($options as $i => $option) {
                        $name = "";
                        $name = $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'];
                        $checked_param = is_null($params) || !isset($params[$name]) ? "" : $params[$name];
                        if (!is_null($params)) {
                            $checked = isSelected($option, $checked_param, null) ? 'checked' : '';
                        } else {
                            $checked = '';
                        }

                        $passage .= '<label><input type="' . $customfield_type_list[$fields['field_type']] . '" name="' . $name . '" value="' . $option . '"' . $checked . '>' . $option . '</label>';
                    }
                    $passage .= '</div>';
                    continue;
                }
                if ($fields['field_type'] == 2) {
                    $passage .= '</h4>';
                    $name = $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'];
                    $value = is_null($params) || !isset($params[$name]) ? "" : $params[$name];
                    $passage .= '<div class="list_field f_txtarea"><textarea name="' . $name . '" rows="4" cols="50">' . $value . '</textarea></div>';
                    continue;
                }
                if ($fields['field_type'] == 1 || $fields['field_type'] == 5) {
                    $passage .= '</h4>';
                    $name = $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'];
                    $value = is_null($params) || !isset($params[$name]) ? "" : $params[$name];
                    $passage .= '<div><input type="' . $customfield_type_list[$fields['field_type']] . '" name="' . $name . '" value="' . $value . '"></div>';
                    continue;
                }
            }
        }

        // 結果を返す
        return [
            'passage' => $passage,
            'data' => $event_application,
            'exist' => $survey_application,
            'event_survey_customfield_category_id' => $event_survey_customfield_category_id
        ];
    }
}
