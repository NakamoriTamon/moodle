<?php
define('CLI_SCRIPT', true);

// Moodleの設定ファイルを読み込む
require(__DIR__.'/../../config.php');
use Dotenv\Dotenv;
use core\context\system;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();

// Moodleのメーラーオブジェクトを使用
global $DB, $CFG;
$email = 'shibuya@trans-it.net';
$name = '澁谷';
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

$htmlBody = "
<div style=\"text-align: center; font-family: Arial, sans-serif;\">
    <p style=\"text-align: left; font-weight:bold;\">{$name}様</p><br>
    <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">これはテストメールです</p><br>
    <p style=\"text-align: left; font-size: 13px; margin:0; \">これはテストメールです</p><br>
    <br>
    <p style=\"text-align: left; font-size: 13px; margin:0; \">これはテストメールです</p><br>
    <p style=\"text-align: left; font-size: 13px; margin:0; \">これはテストメールです</p><br>
    <br>
    <p style=\"text-align: left; font-size: 13px; margin:0; \">なお、再度ご利用をご希望される場合は、新規登録が必要となります。</p><br>
    <p style=\"margin-top: 30px; font-size: 13px; text-align: left;\">
        このメールは、配信専用アドレスから送信されています。<br>
        このメールに返信いただいても、返信内容の確認及びご返信はできません。<br>
    </p>
</div>
";
$mail->Subject = 'これはテストメールです';
$mail->Body = $htmlBody;

$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true
    )
);
$mail->send();