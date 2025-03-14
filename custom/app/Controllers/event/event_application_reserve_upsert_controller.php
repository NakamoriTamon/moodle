<?php
require_once('/var/www/html/moodle/config.php');

// CSRF チェック
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/app/Views/user/index.php');
        exit;
    }
}

$companion_name = $_POST['companion_name'] ?? null;
$application_id = $_POST['application_id'] ?? null;

try {
    $transaction = $DB->start_delegated_transaction();
    $record = new stdClass();
    $record->id = $application_id;
    $record->companion_name = $companion_name;
    $DB->update_record_raw('event_application', $record);
    $transaction->allow_commit();
    $_SESSION['message_success'] = '登録に成功しました';
    if (headers_sent($file, $line)) {
        die("Headers already sent in $file on line $line");
    }
    header('Location: /custom/app/Views/mypage/index.php');
    exit;
} catch (Throwable $e) {
    try {
        $transaction->rollback($e);
    } catch (Throwable $e) {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/app/Views/mypage/index.php');
        exit;
    }
}
