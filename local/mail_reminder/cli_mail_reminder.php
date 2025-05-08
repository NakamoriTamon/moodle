<?php
define('CLI_SCRIPT', true);
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CourseInfoModel.php');
require(__DIR__ . '/../../config.php');

use Dotenv\Dotenv;
use core\context\system;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$model = new CourseInfoModel();
$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();
// Moodleのメーラーオブジェクトを使用
global $DB, $CFG;
$targets = $model->getReminderTargets();

if (empty($targets)) {
    echo "メール送信対象なし: " . $e->getMessage() . date('Y-m-d H:i:s') . "\n";
}

foreach ($targets as $index => $target) {
    $email = $target->participant_mail;
    $start_hour = $target->start_hour;
    $tomorrow = date('Y年m月d日', strtotime('tomorrow'));
    $evnt_name = $target->name;
    $venue_name = $target->venue_name;
    $start_hour = $target->start_hour;
    $no = $target->no;
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
        <p style=\"text-align: left; font-weight:bold;\">関係各位</p><br>
        <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">平素よりお世話になっております。</p><br>
        <p style=\"text-align: left; font-size: 13px; margin:0; \">大阪大学より、明日開催予定のイベントについてご案内申し上げます。</p><br>
        <br>
        <p style=\"text-align: left; font-size: 13px; margin:0; \">■ 開催概要</p><br>
        <p style=\"text-align: left; font-size: 13px; margin:0; \">日程:{$tomorrow}</p><br>
        <p style=\"text-align: left; font-size: 13px; margin:0; \">イベント名:【第{$no}回】{$evnt_name}</p><br>
        <p style=\"text-align: left; font-size: 13px; margin:0; \">会場:{$venue_name}</p><br>
        <p style=\"text-align: left; font-size: 13px; margin:0; \">開始時間:{$start_hour}</p><br>
        <br>
        <p style=\"text-align: left; font-size: 13px; margin:0; \">ご不明な点がございましたら、お気軽にお問い合わせください。</p><br>
        <p style=\"text-align: left; font-size: 13px; margin:0; \">何卒よろしくお願い申し上げます。</p><br>
        <p style=\"margin-top: 30px; font-size: 13px; text-align: left;\">
            このメールは、配信専用アドレスから送信されています。<br>
            このメールに返信いただいても、返信内容の確認及びご返信はできません。<br>
        </p>
    </div>
    ";
    $mail->Subject = '大阪大学イベント開催のご案内（' . $tomorrow . '）';
    $mail->Body = $htmlBody;

    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true
        )
    );

    // メール送信の試行
    try {
        $mail->send();
        echo "メール送信成功: " . $e->getMessage() . date('Y-m-d H:i:s') . "\n";
    } catch (Exception $e) {
        echo "メール送信失敗: " . $e->getMessage() . date('Y-m-d H:i:s') . "\n";
        error_log("メール送信失敗: " . $e->getMessage());
        continue;
    }
}
