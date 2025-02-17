<?php
require_once('/var/www/html/moodle/config.php');
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/local/commonlib/lib.php');

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
    $policyAgreement         = sanitize_post('policy_agreement');
    $impression              = sanitize_post('impression');
    $participationExperience = sanitize_post('participation_experience');
    $foundMethod             = sanitize_post_array('found_method');
    $reason                  = sanitize_post_array('reason');
    $otherFoundMethod        = sanitize_post('other_found_method');
    $otherReason             = sanitize_post('reason_other');
    $satisfaction            = sanitize_post('satisfaction');
    $understanding           = sanitize_post('understanding');
    $goodPoint               = sanitize_post('good_point');
    $otherGoodPoint          = sanitize_post('other_good_point');
    $time                    = sanitize_post('time');
    $holdingEnviroment       = sanitize_post('holding_enviroment');
    $noGoodEnviromentReason  = sanitize_post('no_good_enviroment_reason');
    $work                    = sanitize_post('work');
    $sex                     = sanitize_post('sex');
    $address                 = sanitize_post('address');
    $prefectures             = sanitize_post('prefecture');

    if ($participationExperience == '2') {
        $found_method_error              = validate_found_method($foundMethod, $otherFoundMethod);
        $reason_error                    = validate_reason($reason, $otherReason);
        $satisfaction_error              = validate_satisfaction($satisfaction);
        $understanding_error             = validate_understanding($understanding);
        $good_point_error                = validate_good_point($goodPoint, $otherGoodPoint);
        $time_error                      = validate_time($time);
        $holding_enviroment_error        = validate_holding_enviroment($holdingEnviroment);
        $no_good_enviroment_reason_error = validate_no_good_enviroment_reason($holdingEnviroment, $noGoodEnviromentReason);

        if (
            $found_method_error || $reason_error || $satisfaction_error || $understanding_error || $good_point_error ||
            $time_error || $holding_enviroment_error || $no_good_enviroment_reason_error
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
            ];
            $_SESSION['old_input'] = $_POST;
            $_SESSION['message_error'] = '登録に失敗しました。';
            header("Location: /custom/app/Views/survey/survey_application.php");
            exit;
        }
    } elseif (empty($participationExperience)) {
        $_SESSION['old_input'] = $_POST;
        $_SESSION['message_error'] = '登録に失敗しました。';
        $_SESSION['errors'] = ['participation_experience' => '選択をお願いします。'];
        header("Location: /custom/app/Views/survey/survey_application.php");
        exit;
    }
}

// 直書きのSQL文で挿入するためのパラメータを準備
$params = [
    'event_id'                   => 43,
    'user_id'                    => $USER->id,
    'survey_custom_field_id'     => 19,
    'thoughts'                   => $impression,
    'attend'                     => $participationExperience,
    'found_method'               => implode(', ', $foundMethod),
    'other_found_method'         => $otherFoundMethod,
    'reason'                     => implode(', ', $reason),
    'other_reason'               => $otherReason,
    'satisfaction'               => $satisfaction,
    'understanding'              => $understanding,
    'good_point'                 => $goodPoint,
    'other_good_point'           => $otherGoodPoint,
    'time'                       => $time,
    'holding_environment'        => $holdingEnviroment,
    'no_good_environment_reason' => $noGoodEnviromentReason,
    'work'                       => $work,
    'sex'                        => $sex,
    'address'                    => $address,
    'prefectures'                => $prefectures,
];

$sql = "INSERT INTO {survey_application} (
    event_id,
    user_id,
    survey_custom_field_id,
    thoughts,
    attend,
    found_method,
    other_found_method,
    reason,
    other_reason,
    satisfaction,
    understanding,
    good_point,
    other_good_point,
    time,
    holding_environment,
    no_good_environment_reason,
    work,
    sex,
    address,
    prefectures
) VALUES (
    :event_id,
    :user_id,
    :survey_custom_field_id,
    :thoughts,
    :attend,
    :found_method,
    :other_found_method,
    :reason,
    :other_reason,
    :satisfaction,
    :understanding,
    :good_point,
    :other_good_point,
    :time,
    :holding_environment,
    :no_good_environment_reason,
    :work,
    :sex,
    :address,
    :prefectures
)";

try {
    // トランザクション開始
    $transaction = $DB->start_delegated_transaction();

    // SQL文を実行
    $DB->execute($sql, $params);

    // コミット
    $transaction->allow_commit();

    $_SESSION['message_success'] = '登録が完了しました';
    header("Location: /custom/app/Views/survey/survey_application.php");
    exit;
} catch (Exception $e) {
    // トランザクションが開始されていればロールバック
    if (isset($transaction)) {
        $transaction->rollback($e);
    }
    $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
    $_SESSION['old_input'] = $_POST;
    header("Location: /custom/app/Views/survey/survey_application.php");
    exit;
}
