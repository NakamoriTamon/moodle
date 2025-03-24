<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');

// CSRF チェック
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/app/Views/user/index.php');
        exit;
    }
}

$course_id = $_POST['course_id'] ?? null;
$companion_name = $_POST['companion_name'] ?? null;
$application_id = $_POST['application_id'] ?? null;
$_SESSION['reserve']['course_id'] = $course_id;
$_SESSION['reserve']['id'] = $application_id;
$_SESSION['old_input'] = $_POST;

$name_size = 50;
$companion_name_error  = validate_max_text($companion_name, 'お連れ様の氏名', $name_size, true);
if (!empty($companion_name_error)) {
    $_SESSION['message_error'] = '登録に失敗しました';
    $_SESSION['errors']['companion_name'] = $companion_name_error;
    header('Location: /custom/app/Views/event/reserve.php');
    exit;
}
try {
    $transaction = $DB->start_delegated_transaction();
    $record = new stdClass();
    $record->id = $application_id;
    $record->companion_name = $companion_name;
    $DB->update_record_raw('event_application', $record);
    $transaction->allow_commit();
    $_SESSION['message_success'] = '登録に成功しました';
    header('Location: /custom/app/Views/event/reserve.php');
    exit;
} catch (Throwable $e) {
    try {
        $transaction->rollback($e);
    } catch (Throwable $e) {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/app/Views/event/reserve.php');
        exit;
    }
}
