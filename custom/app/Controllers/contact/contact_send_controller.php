<?php
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');

use Dotenv\Dotenv;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$event_id = "";
$name = "";
$email = "";
$email_confirm = "";
$event_name = "";
$inquiry_details = "";
if (isset($SESSION->formdata)) {
    $formdata = $SESSION->formdata;
    $event_id = $formdata['event_id'];
    $name = $formdata['name'];
    $email = $formdata['email'];
    $event_name = $formdata['event_name'];
    $inquiry_details = $formdata['inquiry_details'];
} else {
    $_SESSION['message_error'] = 'メール送信に失敗しました。お手数ですが、再度ご入力をお願い致します。';
    header('Location: /custom/app/Views/contact/index.php');
    exit;
}

try {
    $inquiry_mail = "";
    if(is_int($event_id)) {
        $eventModel = new EventModel();
        $event = $eventModel->getEventById($event_id);
        $inquiry_mail = $event["inquiry_mail"];
    } else {
        $inquiry_mail = $_ENV['MAIL_FROM_ADRESS'];
    }

    $dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
    $dotenv->load();

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $test = getenv('MAIL_HOST');
    $mail->Host = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['MAIL_USERNAME'];
    $mail->Password = $_ENV['MAIL_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->CharSet = PHPMailer::CHARSET_UTF8;
    $mail->Port = $_ENV['MAIL_PORT'];

    // 事務局側
    // From
    $mail->setFrom($_ENV['MAIL_FROM_ADRESS'], '大阪大学 知の広場 ハンダイ市民講座事務局');
    // To
    $mail->addAddress($inquiry_mail);
    $mail->isHTML(true);

    $htmlBody = "
    <div style=\"text-align: center; font-family: Arial, sans-serif;\">
        <p style=\"text-align: left; font-weight:bold;\">担当者様</p><br />
        <P style=\"text-align: left; font-size: 13px; margin:0; margin-bottom: 30px; padding:0;\">以下の内容でお問い合わせを受け付けました。ご確認のほどよろしくお願いいたします。</P><br /><br />
        <P style=\"text-align: left; font-size: 13px; margin:0; padding:0; \">■お名前</P>
        <p style=\"text-align: left; margin-top: 20px; font-size: 14px;margin-bottom: 30px; padding:0;\">" .  $name . " 様</p><br />
        <P style=\"text-align: left; font-size: 13px; margin:0; padding:0; \">■メールアドレス</P>
        <p style=\"text-align: left; margin-top: 20px; font-size: 14px; margin-bottom: 30px; padding:0;\">" . $email . "</p><br />
        <P style=\"text-align: left; font-size: 13px; margin:0; padding:0; \">■イベントID</P>
        <p style=\"text-align: left; margin-top: 20px; font-size: 14px; margin-bottom: 30px; padding:0;\">" . $event_id . "</p><br />
        <P style=\"text-align: left; font-size: 13px; margin:0; padding:0; \">■お問い合わせの項目</P>
        <p style=\"text-align: left; margin-top: 20px; font-size: 14px; margin-bottom: 30px; padding:0;\">" . $event_name . "</p><br />
        <P style=\"text-align: left; font-size: 13px; margin:0; padding:0; \">■お問い合わせの内容</P>
        <p style=\"text-align: left; margin-top: 20px; font-size: 14px; margin:0; padding:0;\">" .  nl2br($inquiry_details) . "</p><br /><br />
        <p style=\"text-align: left; margin-top: 30px; font-size: 13px; text-align: left;\">ご対応のほど、よろしくお願いいたします。</p>
    </div>
    ";

    $mail->Subject = 'お問い合わせメール' . $event_name;
    $mail->Body = $htmlBody;

    $mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
    );

    $mail->send();

    // 送信側
    // From
    $mail->setFrom($_ENV['MAIL_FROM_ADRESS'], '大阪大学 知の広場 ハンダイ市民講座事務局');
    // To
    $mail->addAddress($email);
    $mail->isHTML(true);

    $htmlBody = "
    <div style=\"text-align: center; font-family: Arial, sans-serif;\">
        <p style=\"text-align: left; font-weight:bold;\">" .  $name . " 様</p><br />
        <P style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">この度は知の広場へお問い合わせいただき、誠にありがとうございます。</P>
        <P style=\"text-align: left; font-size: 13px; margin:0; margin-bottom: 30px; padding:0;\">以下の内容でお問い合わせを受け付けました。</P><br /><br />
        <P style=\"text-align: left; font-size: 13px; margin:0; padding:0; \">■お名前</P>
        <p style=\"text-align: left; margin-top: 20px; font-size: 14px;margin-bottom: 30px; padding:0;\">" .  $name . " 様</p><br />
        <P style=\"text-align: left; font-size: 13px; margin:0; padding:0; \">■メールアドレス</P>
        <p style=\"text-align: left; margin-top: 20px; font-size: 14px; margin-bottom: 30px; padding:0;\">" .  $email . "</p><br />
        <P style=\"text-align: left; font-size: 13px; margin:0; padding:0; \">■お問い合わせの項目</P>
        <p style=\"text-align: left; margin-top: 20px; font-size: 14px; margin-bottom: 30px; padding:0;\">" .  $event_name . "</p><br />
        <P style=\"text-align: left; font-size: 13px; margin:0; padding:0; \">■お問い合わせの内容</P>
        <p style=\"text-align: left; margin-top: 20px; font-size: 14px; margin:0; padding:0;\">" .  nl2br($inquiry_details) . "</p><br />
        <p style=\"text-align: left; margin-top: 30px; font-size: 13px; text-align: left;\">担当者が確認の上、順次ご対応させていただきます。</p>
        <p style=\"font-size: 13px; text-align: left;\">回答にはお時間をいただく場合がございますので、予めご了承ください。</p><br />
        <p style=\"font-size: 13px; text-align: left;\">なお、本メールは自動送信ですので、返信いただいても対応できません。</p>
        <p style=\"font-size: 13px; text-align: left;\">何か追加のご連絡がある場合は、再度お問い合わせフォームよりお問い合わせください。</p><br />
        <p style=\"font-size: 13px; text-align: left;\">大阪大学 知の広場 ハンダイ市民講座事務局</p>
    </div>
    ";

    $mail->Subject = $event_name . 'お問い合わせを受け付けました';
    $mail->Body = $htmlBody;

    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $mail->send();

    unset($_SESSION['errors'], $_SESSION['old_input'], $SESSION->formdata, $_SESSION['message_error']);
    header('Location: /custom/app/Views/contact/complete.php');
    exit;
} catch (Exception $e) {
    $_SESSION['message_error'] = 'メール送信に失敗しました。お手数ですが、再度ご入力をお願い致します。';
    header('Location: /custom/app/Views/contact/index.php');
    exit;
}