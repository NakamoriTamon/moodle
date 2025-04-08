<?php
require_once('/var/www/html/moodle/config.php');
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventSurveyCustomFieldModel.php');

// 接続情報取得
global $DB;
global $USER;

function recursive_htmlspecialchars($data)
{
    if (is_array($data)) {
        return array_map('recursive_htmlspecialchars', $data);
    } else {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

function sanitize_post_int($key)
{
    $value = sanitize_post($key);
    return ($value === '') ? null : (int)$value;
}

function sanitize_post($key)
{
    if (!isset($_POST[$key])) {
        return '';
    }
    $value = $_POST[$key];
    if (is_array($value)) {
        $value = recursive_htmlspecialchars($value);
        $value = implode(', ', $value);
    } else {
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    return $value;
}

function sanitize_post_array($key)
{
    if (!isset($_POST[$key])) {
        return [];
    }
    $value = $_POST[$key];
    if (!is_array($value)) {
        $value = [$value];
    }
    return recursive_htmlspecialchars($value);
}


// CSRF チェック
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header('Location: /custom/app/Views/signup/index.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseInfoId = (int) sanitize_post('course_info_id');
    $eventId = (int) sanitize_post('event_id');
    $eventApplicationId = (int) sanitize_post('event_application_id');
    $eventSurveyCustomfieldCategoryId = (int) sanitize_post('event_survey_customfield_category_id');
    $impression = sanitize_post('impression');
    $participation = sanitize_post('participation');
    $foundMethod = sanitize_post_array('found_method');
    $reason = sanitize_post_array('reason');
    $otherFoundMethod = sanitize_post('other_found_method');
    $otherReason = sanitize_post('reason_other');

    $satisfaction    = sanitize_post_int('satisfaction');
    $understanding   = sanitize_post_int('understanding');
    $goodPoint       = sanitize_post_int('good_point');
    $time            = sanitize_post_int('time');
    $holdingEnviroment = sanitize_post_int('holding_enviroment');
    $sex             = sanitize_post_int('sex');

    $otherGoodPoint      = sanitize_post('other_good_point');
    $noGoodEnviromentReason  = sanitize_post('no_good_enviroment_reason');
    $lectureSuggestions  = sanitize_post('lecture_suggestions');
    $speakerSuggestions  = sanitize_post('speaker_suggestions');
    $work                = sanitize_post('work');
    $address             = sanitize_post('address');
    $prefectures         = sanitize_post('prefecture');

    $fieldInputDataList = [];
    $params = [];
    if (!empty($eventSurveyCustomfieldCategoryId)) {
        $eventSurveyCustomFieldModel = new EventSurveyCustomFieldModel();
        $fieldList = $eventSurveyCustomFieldModel->getEventSurveyCustomFieldById($eventSurveyCustomfieldCategoryId);
        foreach ($fieldList as $fields) {
            $input_value = "";
            $tag_name = $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'];
            if ($fields['field_type'] == 3) {
                $input_data = sanitize_post($tag_name);
                $input_data = explode(", ", $input_data);
                $params[$tag_name] = $input_data;
                $options = explode(",", $fields['selection']);

                foreach ($options as $i => $option) {
                    if (in_array($option, $input_data)) {
                        if ($i == 0) {
                            $input_value = $option;
                            continue;
                        }
                        $input_value .= ',' . $option;
                    }
                }
            } elseif ($fields['field_type'] == 4) {
                $input_value = sanitize_post($tag_name);
                $options = explode(",", $fields['selection']);
                foreach ($options as $i => $option) {
                    if ($option == $input_value) {
                        $input_value = $option;
                        $params[$tag_name] = $input_value;
                        break;
                    }
                }
                if (!isset($params[$tag_name])) {
                    $params[$tag_name] = "";
                }
            } else {
                $input_value = optional_param($tag_name, '', PARAM_TEXT);
                $params[$tag_name] = $input_value;
            }

            $fieldInputDataList[] = ['event_survey_customfield_id' => $fields['id'], 'field_type' => $fields['field_type'], 'input_data' => $input_value];
        }
    }

    if ($participation == '2') {
        $found_method_error              = validate_other_input($foundMethod, $otherFoundMethod);
        $reason_error                    = validate_other_input($reason, $otherReason);
        $satisfaction_error              = validate_input($satisfaction);
        $understanding_error             = validate_input($understanding);
        $good_point_error                = validate_other_input($goodPoint, $otherGoodPoint);
        $time_error                      = validate_input($time);
        $holding_enviroment_error        = validate_input($holdingEnviroment);
        $no_good_enviroment_reason_error = validate_other_input($holdingEnviroment, $noGoodEnviromentReason);
        $lecture_suggestions_error       = validate_text_input($lectureSuggestions);
        $speaker_suggestions_error       = validate_text_input($speakerSuggestions);

        if (
            $found_method_error || $reason_error || $satisfaction_error || $understanding_error || $good_point_error ||
            $time_error || $holding_enviroment_error || $no_good_enviroment_reason_error || $lecture_suggestions_error || $speaker_suggestions_error
        ) {
            $_SESSION['errors'] = [
                'found_method'              => $found_method_error,
                'reason'                    => $reason_error,
                'satisfaction'              => $satisfaction_error,
                'understanding'             => $understanding_error,
                'good_point'                => $good_point_error,
                'time'                      => $time_error,
                'holding_enviroment'        => $holding_enviroment_error,
                'no_good_enviroment_reason' => $no_good_enviroment_reason_error,
                'lecture_suggestions'       => $lecture_suggestions_error,
                'speaker_suggestions'       => $speaker_suggestions_error,
            ];
            $_SESSION['old_input'] = $_POST;
            $_SESSION['old_input']['survey_params'] = $params;
            $_SESSION['message_error'] = '登録に失敗しました';
            $_SESSION['course_info_id'] = $courseInfoId;
            $_SESSION['event_application_id'] = $eventApplicationId;
            header("Location: /custom/app/Views/survey/index.php");
            exit;
        }
    } elseif (empty($participation)) {
        $_SESSION['old_input'] = $_POST;
        $_SESSION['old_input']['survey_params'] = $params;
        $_SESSION['course_info_id'] = $courseInfoId;
        $_SESSION['event_application_id'] = $eventApplicationId;
        $_SESSION['message_error'] = '登録に失敗しました';
        $_SESSION['errors'] = ['participation' => '選択をお願いします。'];
        header("Location: /custom/app/Views/survey/index.php");
        exit;
    }
}

try {
    // トランザクション開始
    $transaction = $DB->start_delegated_transaction();
    $record = new stdClass();
    $record->course_info_id = $courseInfoId;
    $record->user_id = $_SESSION['user_id'];
    $record->event_id = $eventId;
    $record->thoughts = $impression;
    $record->attend = $participation;
    $record->found_method = implode(', ', (array)$foundMethod);
    $record->other_found_method = $otherFoundMethod;
    $record->reason       = implode(', ', (array)$reason);
    $record->other_reason = $otherReason;
    $record->satisfaction = $satisfaction;
    $record->understanding = $understanding;
    $record->good_point = $goodPoint;
    $record->other_good_point = $otherGoodPoint;
    $record->time = $time;
    $record->holding_environment = $holdingEnviroment;
    $record->no_good_environment_reason = $noGoodEnviromentReason;
    $record->lecture_suggestions = $lectureSuggestions;
    $record->speaker_suggestions = $speakerSuggestions;
    $record->work = $work;
    $record->sex = $sex;
    $record->address = $address;
    $record->prefectures = $prefectures;

    $surveyApplicationId = $DB->insert_record_raw('survey_application', $record, true);

    
    // アンケートカスタムフィールドがある場合
    if (!empty($eventSurveyCustomfieldCategoryId)) {
        foreach ($fieldInputDataList as $fieldInputData) {
            $record = new stdClass();
            $record->survey_application_id = $surveyApplicationId;
            $record->event_survey_customfield_id = $fieldInputData['event_survey_customfield_id'];
            $record->field_type = $fieldInputData['field_type'];
            $record->input_data = $fieldInputData['input_data'];
            $DB->insert_record_raw('survey_application_customfield', $record);
        }
    }

    // コミット
    $transaction->allow_commit();

    header("Location: /custom/app/Views/survey/complete.php");
    exit;
} catch (Exception $e) {
    // トランザクションが開始されていればロールバック
    if (isset($transaction)) {
        $transaction->rollback($e);
    }
    $_SESSION['old_input'] = $_POST;
    $_SESSION['message_error'] = '登録に失敗しました';
    header("Location: /custom/app/Views/survey/index.php");
    exit;
}
