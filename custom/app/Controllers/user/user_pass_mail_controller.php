<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/lib/classes/context/system.php');
require '/var/www/vendor/autoload.php';

use Dotenv\Dotenv;
use core\context\system;
use PHPMailer\PHPMailer\PHPMailer;

// 接続情報取得
global $DB;

$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();

// CSRF チェック
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/app/Views/user/pass_mail.php');
        exit;
    }
}

// POSTパラメータの取得
$email = $_POST['email'] ?? null;
$_SESSION['old_input'] = $_POST;

if (empty($email)) {
    $_SESSION['errors'] = ['email' => 'メールアドレスを入力してください'];
    header('Location: /custom/app/Views/user/pass_mail.php');
    exit;
}

$user = $DB->get_record('user', ['email' => $email]);
if (empty($user)) {
    $_SESSION['errors'] = ['email' => 'アカウントが存在しません'];
    header('Location: /custom/app/Views/user/pass_mail.php');
    exit;
}
// 権限の確認
$general_user = $DB->get_record('role_assignments', ['userid' => $user->id, 'roleid' => 7]);
if (empty($general_user)) {
    $_SESSION['errors'] = ['email' => 'アカウントが存在しません'];
    header('Location: /custom/app/Views/user/pass_mail.php');
    exit;
}

// 現在時刻を取得
$current_time = time();
$token = null;
try {
    $transaction = $DB->start_delegated_transaction();
    $new_request = new stdClass();
    $new_request->userid = $user->id;
    $new_request->timerequested = $current_time;
    $new_request->timererequested = 0; // 初回リクエストの場合は 0
    $token = bin2hex(random_bytes(16)); // 32文字のランダムなトークン
    $new_request->token = $token;
    $DB->insert_record('user_password_resets', $new_request);

    // 再設定URLを生成
    $reset_url = $CFG->wwwroot . '/custom/app/Views/user/pass_reset.php?token=' . $token;

    // メール送信処理
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host        = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth    = true;
    $mail->Username    = $_ENV['MAIL_USERNAME'];
    $mail->Password    = $_ENV['MAIL_PASSWORD'];
    $mail->SMTPSecure  = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->CharSet     = PHPMailer::CHARSET_UTF8;
    $mail->Port        = $_ENV['MAIL_PORT'];

    $mail->setFrom($_ENV['MAIL_FROM_ADRESS'], 'Sender Name');
    $mail->addAddress($email, 'Recipient Name');
    $mail->addReplyTo('no-reply@example.com', 'No Reply');
    $mail->isHTML(true);

    // メール本文（確認URLを表示）
    $htmlBody = "
        <div style=\"text-align: center; font-family: Arial, sans-serif;\">
            <p style=\"text-align: left; font-weight:bold;\">{$user->name}様</p><br>
            <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">
                あなたのアカウントに新しいパスワードを設定するには以下のウェブアドレスにアクセスしてください</p><br>
            <br>
            <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">▼ パスワード再設定はこちら
            <p style=\"text-align: left; font-size: 13px;\">
                <a href=\"{$reset_url}\">{$reset_url}</a>
            </p>
            <br>
            <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">※このURLの有効期限は  **30分以内** です。</P>
            <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">期限を過ぎると再設定できませんので、ご注意ください。</P><br>
            <p style=\"margin-top: 30px; font-size: 13px; text-align: left;\">
                このメールは、配信専用アドレスから送信されています。<br>
                このメールに返信いただいても、返信内容の確認及びご返信はできません。<br>
            </p>
        </div>
        ";
    $mail->Subject = '【知の広場】パスワード再設定メール';
    $mail->Body = $htmlBody;

    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true
        )
    );

    $mail->send();
    $transaction->allow_commit();
    redirect('/custom/app/Views/user/pass_mail_complete.php');
    exit;
} catch (Throwable $e) {
    try {
        $transaction->rollback($e);
    } catch (Throwable $e) {
        $_SESSION['message_error'] = '送信に失敗しました';
        redirect('/custom/app/Views/user/pass_mail.php');
        exit;
    }
}
