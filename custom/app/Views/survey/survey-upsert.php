<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');

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
    return recursive_htmlspecialchars($value);
}

// CSRF チェック
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/app/Views/signup/index.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventid = (int) sanitize_post('event_id');
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

    if ($participation == '2') {
        $found_method_error              = validate_survey_found_method($foundMethod, $otherFoundMethod);
        $reason_error                    = validate_survey_reason($reason, $otherReason);
        $satisfaction_error              = validate_survey_satisfaction($satisfaction);
        $understanding_error             = validate_survey_understanding($understanding);
        $good_point_error                = validate_survey_good_point($goodPoint, $otherGoodPoint);
        $time_error                      = validate_survey_time($time);
        $holding_enviroment_error        = validate_survey_holding_enviroment($holdingEnviroment);
        $no_good_enviroment_reason_error = validate_survey_no_good_enviroment_reason($holdingEnviroment, $noGoodEnviromentReason);

        if (
            $found_method_error || $reason_error || $satisfaction_error || $understanding_error || $good_point_error ||
            $time_error || $holding_enviroment_error || $no_good_enviroment_reason_error
        ) {
            $_SESSION['errors'] = [
                'found_method' => $found_method_error,
                'reason' => $reason_error,
                'satisfaction' => $satisfaction_error,
                'understanding' => $understanding_error,
                'good_point' => $good_point_error,
                'time' => $time_error,
                'holding_enviroment' => $holding_enviroment_error,
                'no_good_enviroment_reason' => $no_good_enviroment_reason_error,
            ];
            $_SESSION['old_input'] = $_POST;
            $_SESSION['event_id'] = $eventid;
            $_SESSION['message_error'] = '登録に失敗しました。';
            header("Location: /custom/app/Views/survey/index.php?event_id=" . $eventid);
            exit;
        }
    } elseif (empty($participation)) {
        $_SESSION['old_input'] = $_POST;
        $_SESSION['event_id'] = $eventid;
        $_SESSION['message_error'] = '登録に失敗しました。';
        $_SESSION['errors'] = ['participation' => '選択をお願いします。'];
        header("Location: /custom/app/Views/survey/index.php?event_id=" . $eventid);
        exit;
    }
}

try {
    // トランザクション開始
    $transaction = $DB->start_delegated_transaction();
    $record = new stdClass();
    $record->event_id = $eventid;
    $record->user_id = $_SESSION['user_id'];
    $record->thoughts = $impression;
    $record->attend = $participation;
    $record->found_method = implode(', ', $foundMethod);
    $record->other_found_method = $otherFoundMethod;
    $record->reason = implode(', ', $reason);
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

    $DB->insert_record_raw('survey_application', $record);

    // コミット
    $transaction->allow_commit();

    $_SESSION['message_success'] = '登録が完了しました';
    header("Location: /custom/app/Views/event/register.php");
    exit;
} catch (Exception $e) {
    if (isset($transaction)) {
        $transaction->rollback($e);
    }
    $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
    $_SESSION['old_input'] = $_POST;
    header("Location: /custom/app/Views/event/register.php");
    exit;
}
