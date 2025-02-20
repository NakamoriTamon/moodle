<?php
require_once('/var/www/html/moodle/config.php');
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/local/commonlib/lib.php');

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

global $DB;

$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();

$name          = $_POST['name'];
$email         = $_POST['email'];
$email_confirm = $_POST['email_confirm'];
$heading       = $_POST['heading'];
$message       = $_POST['message'];
$createdAt  = date('Y-m-d H:i:s');
$updatedAt  = date('Y-m-d H:i:s');

$name_error          = validate_contact_name($name);
$email_error         = validate_contact_email($email);
$email_confirm_error = validate_contact_email_confirm($email, $email_confirm);
$message_error       = validate_contact_message($message);

if ($name_error || $email_error || $email_confirm_error || $message_error) {
    $_SESSION['errors'] = [
        'name'          => $name_error,
        'email'         => $email_error,
        'email_confirm' => $email_confirm_error,
        'message'       => $message_error,
    ];
    $_SESSION['old_input'] = $_POST;
    $_SESSION['message_error'] = '登録に失敗しました。';
    header("Location: /custom/app/Views/contact/index.php");
    exit;
} else {
    // CSRF チェック
    if ($_SERVER['REQUEST_METHOD'] === "POST") {

        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message_error'] = '登録に失敗しました';
            header("Location: /custom/app/Views/contact/index.php");
            exit;
        }
        if (empty($_POST['confirm_token']) || !hash_equals($_SESSION['confirm_token'], $_POST['confirm_token'])) {
            $_SESSION['message_error'] = '登録に失敗しました';
            header("Location: /custom/app/Views/contact/index.php");
            exit;
        }
    }
    unset($_SESSION['csrf_token']);
    unset($_SESSION['confirm_token']);

    //仮もの
    $userid = (int) $_SESSION['USER']->id;
    $eventname = $heading;
    $simple = ["会員登録前のご質問", "その他一般的なお問い合わせ"];

    if (in_array($eventname, $simple)) {
        $eventid = null;
        $tutorid = null;
        $tutorname = "管理者";
    } else {
        $event = $DB->get_record('event', array('name' => $heading));
        $eventid = ($event && isset($event->id)) ? $event->id : '';
        $tutor = $DB->get_record('tutor', array('id' => $event->userid));
        $tutorid = null;
        $tutorname = ($tutor && isset($tutor->name)) ? $tutor->name : '';
    }


    $mail = new PHPMailer(true);
    try {
        // トランザクション開始
        $transaction = $DB->start_delegated_transaction();
        $record = new stdClass();
        $record->userid = $_SESSION['user_id'];
        $record->eventid = $eventid;
        $record->tutorid = $tutorid;
        $record->name = $name;
        $record->email = $email;
        $record->heading = $heading;
        $record->message = $message;
        $record->created_at = $createdAt;
        $record->updated_at = $updatedAt;

        $DB->insert_record_raw('contact_inquiries', $record);

        // 管理者側メール
        $mail->SMTPDebug   = 0;
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

        $htmlBody = "
    <div style=\"text-align: center; font-family: Arial, sans-serif;\">
        <p style=\"text-align: left; font-weight:bold;\">$tutorname 様</p>
        <p style=\"text-align: left; font-size: 13px; margin:0; margin-bottom: 30px;\">以下の内容でお問い合わせを受け付けました。ご確認のほどよろしくお願いいたします。</p>
        <div style=\"text-align: left; font-size: 13px; line-height: 1.6; border: 1px solid #ccc; padding: 15px; margin-bottom: 30px;\">
            <p><strong>■お名前</strong><br>$name</p>
            <p><strong>■メールアドレス</strong><br>$email</p>
            <p><strong>■お問い合わせの項目</strong><br>" . $eventname . "</p>
            <p><strong>■お問い合わせの内容</strong><br>$message</p>
        </div>
        <p style=\"margin-top: 30px; font-size: 13px; text-align: left;\">
            ご対応のほど、よろしくお願いいたします。
        </p>
    </div>
    ";
        $mail->Subject = '【お問い合わせのご連絡】';
        $mail->Body    = $htmlBody;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            )
        );

        $mail->send();


        // 利用者側
        $mail->SMTPDebug   = 0;
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

        $htmlBody = "
    <div style=\"text-align: center; font-family: Arial, sans-serif;\">
        <p style=\"text-align: left; font-weight:bold;\">$name 様</p>
        <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">この度は知の広場へお問い合わせいただき、誠にありがとうございます。</p>
        <p style=\"text-align: left; font-size: 13px; margin:0; margin-bottom: 30px;\">以下の内容でお問い合わせを受け付けました。</p>
        <div style=\"text-align: left; font-size: 13px; line-height: 1.6; border: 1px solid #ccc; padding: 15px; margin-bottom: 30px;\">
            <p><strong>■お名前</strong><br>$name</p>
            <p><strong>■お問い合わせの項目</strong><br>" . $eventname . "</p>
            <p><strong>■お問い合わせの内容</strong><br>$message</p>
        </div>
        <p style=\"margin-top: 30px; font-size: 13px; text-align: left;\">
            担当者が確認の上、順次ご対応させていただきます。回答にはお時間をいただく場合がございますので、予めご了承ください。<br>
            なお、本メールは自動送信ですので、返信いただいても対応できません。<br>
            何か追加のご連絡がある場合は、下記までお問い合わせください。<br><br>
            AA記念会 事務局<br>
            （メールアドレス）
        </p>
    </div>
    ";
        $mail->Subject = '【' . $eventname . '】お問い合わせを受け付けました';
        $mail->Body    = $htmlBody;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            )
        );

        $mail->send();
        $transaction->allow_commit();
        header("Location: /custom/app/Views/contact/complete.php");
        exit;
    } catch (Exception $e) {
        if (isset($transaction)) {
            $transaction->rollback($e);
        }
        $_SESSION['message_error'] = '登録に失敗しました';
        header("Location: /custom/app/Views/contact/complete.php");
    }
}
