<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/lib/classes/context/system.php');
require '/var/www/vendor/autoload.php';

global $DB;
$password = $_POST['password'] ?? null;
$confirm_password = $_POST['confirm_password'] ?? null;
$token = $_POST['token'] ?? null;
$_SESSION['old_input'] = $_POST;

// CSRF チェック
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = '再設定に失敗しました';
        header('Location: /custom/app/Views/user/pass_mail.php');
        exit;
    }
}

// バリデーションチェック
if (empty($password)) {
    $_SESSION['errors']['password'] = '新しいパスワードを入力してください';
} else {
    $result_text = pass_val($password);
    if (!empty($result_text)) {
        $_SESSION['errors']['password'] = '新しいパスワード' . $result_text;
    }
}
if (empty($confirm_password)) {
    $_SESSION['errors']['confirm_password'] = 'パスワード（確認用）を入力してください';
} else {
    $result_text = pass_val($confirm_password);
    if (!empty($result_text)) {
        $_SESSION['errors']['confirm_password'] = '新しいパスワード' . $result_text;
    }
}
if (!empty($_SESSION['errors'])) {
    header('Location: /custom/app/Views/user/pass_reset.php?token=' . $token);
    exit;
}
if ($password !== $confirm_password) {
    $_SESSION['errors']['confirm_password'] = 'パスワード（確認用）が異なります';
    header('Location: /custom/app/Views/user/pass_reset.php?token=' . $token);
    exit;
}

$user_password_reset = $DB->get_record('user_password_resets', ['token' => $token]);
if (empty($user_password_reset)) {
    $_SESSION['message_error'] = '再設定に失敗しました';
    header('Location: /custom/app/Views/user/pass_reset.php?token=' . $token);
    exit;
}

try {
    $transaction = $DB->start_delegated_transaction();
    $record = new stdClass();
    $record->id = $user_password_reset->userid;
    $record->password = password_hash($password, PASSWORD_DEFAULT);
    $DB->update_record_raw('user', $record);
    $transaction->allow_commit();
    redirect('/custom/app/Views/user/pass_reset_complete.php');
    exit;
} catch (Throwable $e) {
    try {
        $transaction->rollback($e);
    } catch (Throwable $e) {
        $_SESSION['message_error'] = '再設定に失敗しました';
        header('Location: /custom/app/Views/user/pass_reset.php?token=' . $token);
        exit;
    }
}

function pass_val($password)
{
    if (strlen($password) < 8 || strlen($password) > 20) {
        return 'は8文字以上20文字以下である必要があります。';
    }
    // 英字（大文字・小文字）と数字の使用必須
    if (!preg_match('/[A-Za-z]/', $password)) {
        return 'にはアルファベットが含まれている必要があります。';
    }
    if (!preg_match('/[0-9]/', $password)) {
        return 'には数字が含まれている必要があります。';
    }

    return null;
}
