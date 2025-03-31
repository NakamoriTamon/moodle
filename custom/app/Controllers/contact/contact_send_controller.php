<?php
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');

use Dotenv\Dotenv;
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

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
    if (is_numeric($event_id)) {
        $eventModel = new EventModel();
        $event = $eventModel->getEventById($event_id);
        $inquiry_mail = empty($event["inquiry_mail"]) ? $_ENV['MAIL_FROM_ADRESS'] : $event["inquiry_mail"];
    } else {
        $inquiry_mail = $_ENV['MAIL_FROM_ADRESS'];
    }

    $dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
    $dotenv->load();

    // SESのクライアント設定
    $SesClient = new SesClient([
        'version' => 'latest',
        'region'  => 'ap-northeast-1', // 東京リージョン
        'credentials' => [
            'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
            'secret' => $_ENV['AWS_SECRET_ACCESS_KEY_ID'],
        ]
    ]);

    $recipients = [$inquiry_mail];

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

    $subject = 'お問い合わせメール' . $event_name;

    try {
        $result = $SesClient->sendEmail([
            'Destination' => [
                'ToAddresses' => $recipients,
            ],
            'Source' => "知の広場 <{$_ENV['MAIL_FROM_ADDRESS']}>",
            'Message' => [
                'Subject' => [
                    'Data' => $subject,
                    'Charset' => 'UTF-8'
                ],
                'Body' => [
                    'Html' => [
                        'Data' => $htmlBody,
                        'Charset' => 'UTF-8'
                    ]
                ]
            ]
        ]);
    } catch (AwsException $e) {
        $_SESSION['message_error'] = 'メールの送信に失敗しました';
        header('Location: /custom/app/Views/contact/index.php');
        exit;
    }

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

    $recipients = [$email];
    $subject = $event_name . 'お問い合わせを受け付けました';

    try {
        $result = $SesClient->sendEmail([
            'Destination' => [
                'ToAddresses' => $recipients,
            ],
            'ReplyToAddresses' => ['no-reply@example.com'],
            'Source' => "知の広場 <{$_ENV['MAIL_FROM_ADDRESS']}>",
            'Message' => [
                'Subject' => [
                    'Data' => $subject,
                    'Charset' => 'UTF-8'
                ],
                'Body' => [
                    'Html' => [
                        'Data' => $htmlBody,
                        'Charset' => 'UTF-8'
                    ]
                ]
            ]
        ]);
    } catch (AwsException $e) {
        $_SESSION['message_error'] = 'メールの送信に失敗しました';
        header('Location: /custom/app/Views/contact/index.php');
        exit;
    }

    unset($_SESSION['errors'], $_SESSION['old_input'], $SESSION->formdata, $_SESSION['message_error']);
    header('Location: /custom/app/Views/contact/complete.php');
    exit;
} catch (Exception $e) {
    $_SESSION['message_error'] = 'メール送信に失敗しました。お手数ですが、再度ご入力をお願い致します。';
    header('Location: /custom/app/Views/contact/index.php');
    exit;
}
