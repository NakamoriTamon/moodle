<?php
define('CLI_SCRIPT', true);
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CourseInfoModel.php');
require(__DIR__ . '/../../config.php');

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\Exception;
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

$model = new CourseInfoModel();
$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();
// Moodleのメーラーオブジェクトを使用
global $DB, $CFG;
$targets = $model->getReminderTargets();

if (empty($targets)) {
    echo "メール送信対象なし: " . date('Y-m-d H:i:s') . "\n";
}

// SESのクライアント設定
$SesClient = new SesClient([
    'version' => 'latest',
    'region'  => 'ap-northeast-1',
    'credentials' => [
        'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
        'secret' => $_ENV['AWS_SECRET_ACCESS_KEY_ID'],
    ]
]);

// 会員のメールアドレス
$mail_to_list = [];
$is_first = true;
foreach ($targets as $target) {
    $email = $target->participant_mail;
    // ドメイン名が有効かDNSチェック（MXレコード確認）
    $domain = substr(strrchr($email, "@"), 1);
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && $domain && checkdnsrr($domain, "MX")) {
        $mail_to_list[] = $email;
    }
    if ($is_first) {
        $start_hour = $target->start_hour;
        $tomorrow = date('Y年m月d日', strtotime('tomorrow'));
        $evnt_name = $target->name;
        $venue_name = $target->venue_name;
        $start_hour = $target->start_hour;
        $no = $target->no;
        $is_first = false;
    }
}

// メール内容設定
$subject = "【明日開催】{$evnt_name} に関するご案内";
$htmlBody = "
<div style=\"text-align: center; font-family: Arial, sans-serif;\">
    <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">知の広場事務局です。</p><br>
    <p style=\"text-align: left; font-size: 13px; margin:0; \">明日開催予定のイベントについてご案内申し上げます。</p><br>
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

// メール送信
$count = 0;
foreach ($mail_to_list as $send_mail_adress) {
    try {
        $result = $SesClient->sendEmail([
            'Destination' => [
                'ToAddresses' => [$send_mail_adress],
            ],
            'ReplyToAddresses' => ['no-reply@example.com'],
            'Source' => "大阪大学知の広場事務局 <{$_ENV['MAIL_FROM_ADDRESS']}>",
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
        $count = $count + 1;
    } catch (AwsException $e) {
        echo "メール送信失敗: " . $e->getMessage() . date('Y-m-d H:i:s') . "\n";
        error_log("メール送信失敗: " . $e->getMessage() . '該当メールアドレス : ' . $send_mail_adress);
    }
}

echo "メール送信成功: " . date('Y-m-d H:i:s') . "\n";
error_log("メール送信成功: 送信件数" . $count . '件');
