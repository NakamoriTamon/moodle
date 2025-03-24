#!/usr/bin/env php
<?php
// CLI実行を定義
define('CLI_SCRIPT', true);

// Moodle設定の読み込み
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/TargetModel.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/clilib.php');

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;
use Dotenv\Dotenv;

$eventModel = new EventModel();
$targetModel = new TargetModel();
$baseModel = new BaseModel();
$pdo = $baseModel->getPdo();

// $model = new CourseInfoModel();
$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();
// Moodleのメーラーオブジェクトを使用
global $DB, $CFG;

// コマンドラインオプションの設定
list($options, $unrecognized) = cli_get_params([
    'eventid' => null,
    'help'    => false,
], [
    'h' => 'help'
]);

// ヘルプテキスト
if ($options['help']) {
    echo "イベント通知メールを送信します。

    オプション:
    --eventid=INT    送信対象のイベントID（必須）
    -h, --help       このヘルプメッセージを表示

    ";
    exit(0);
}

// イベントIDが指定されているか確認
if (empty($options['eventid'])) {
    cli_error('イベントIDが指定されていません。--eventid=XXX を指定してください。');
}

$eventId = (int)$options['eventid'];

// イベント情報の取得
try {
    $stmt = $pdo->prepare("SELECT capacity, name, event_kbn, event_date, start_hour, end_hour, venue_name, description, target, start_event_date, end_event_date FROM mdl_event WHERE id = :id");
    $stmt->execute([':id' => $eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    // if (!$event) {
    //     write_log("エラー: 指定されたイベントID {$eventId} は存在しません。", $log_file);
    //     exit(1);
    // }

    // 講義形式情報の取得
    $stmt = $pdo->prepare("
        SELECT lf.name 
        FROM mdl_event_lecture_format elf
        JOIN mdl_lecture_format lf ON elf.lecture_format_id = lf.id
        WHERE elf.event_id = :event_id
    ");
    $stmt->execute([':event_id' => $eventId]);
    $lectureFormatTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $formatText = !empty($lectureFormatTypes) ? implode('・', $lectureFormatTypes) : '';

    // 時間から秒を削除する処理
    if (!empty($event['start_hour'])) {
        $startTime = new DateTime($event['start_hour']);
        $startHourFormatted = $startTime->format('H:i');
    } else {
        $startHourFormatted = '00:00';
    }

    if (!empty($event['end_hour'])) {
        $endTime = new DateTime($event['end_hour']);
        $endHourFormatted = $endTime->format('H:i');
    } else {
        $endHourFormatted = '00:00';
    }

    // 開催日時の取得を修正
    $eventDateText = '';
    if ($event['event_kbn'] == SINGLE_EVENT) {
        // 単発イベント
        if (!empty($event['event_date'])) {
            $date = new DateTime($event['event_date']);
            $formattedDate = $date->format('Y年m月d日');
            $eventDateText = $formattedDate . ' ' . $startHourFormatted . '～' . $endHourFormatted;
        }
    } elseif ($event['event_kbn'] == PLURAL_EVENT) {
        // 複数回シリーズ
        $stmt = $pdo->prepare("
        SELECT ci.course_date
        FROM mdl_event_course_info eci
        JOIN mdl_course_info ci ON eci.course_info_id = ci.id
        WHERE eci.event_id = :event_id
        ORDER BY ci.course_date ASC
        LIMIT 1
    ");
        $stmt->execute([':event_id' => $eventId]);
        $firstCourseDateRow = $stmt->fetch();

        if ($firstCourseDateRow) {
            $date = new DateTime($firstCourseDateRow['course_date']);
            $formattedDate = $date->format('Y年m月d日');
            $eventDateText = $formattedDate . ' ' . $startHourFormatted . '～' . $endHourFormatted . '（第1回目）';
        }
    } elseif ($event['event_kbn'] == EVERY_DAY_EVENT) {
        // 毎日開催
        if (!empty($event['start_event_date']) && !empty($event['end_event_date'])) {
            $startDate = new DateTime($event['start_event_date']);
            $endDate = new DateTime($event['end_event_date']);
            $eventDateText = $startDate->format('Y年m月d日') . '～' . $endDate->format('Y年m月d日') . ' ' . $startHourFormatted . '～' . $endHourFormatted;
        }
    }

    // 会場情報の設定
    $venueInfo = !empty($event['venue_name']) ? $event['venue_name'] : (!empty($formatText) ? $formatText : 'オンライン');

    // 申し込みURL
    $applyUrl = $CFG->wwwroot . "/custom/app/Views/event/detail.php?id=" . $eventId;

    // 対象の取得
    $targets = $targetModel->getTargets();
    $mail_target = '';
    foreach ($targets as $target) {
        if ($target['id'] == $event['target']) {
            $mail_target = $target['name'];
            break;
        }
    }

    // HTMLメール本文を作成
    $htmlBody = "
        <html>
        <head>
            <title>{$event['name']}のご案内</title>
        </head>
        <body>
            <div style=\"font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto;\">
                <p>平素より格別のご高配を賜り、誠にありがとうございます。<br>
                このたび、新たなイベント 「{$event['name']}」 を開催する運びとなりましたので、ご案内申し上げます。</p>

                <h2 style=\"margin-top: 0; color: #3366cc;\">■ 開催概要</h2>
                
                <p><strong>開催日時:</strong> {$eventDateText}</p>
                
                <p><strong>開催場所:</strong> {$venueInfo}</p>
                
                <p><strong>対象:</strong> {$mail_target}</p>
                
                <p><strong>内容:</strong><br> {$event['description']}</p>
                
                <br>
                
                <div style=\"text-align: center;\">

                    <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">▼ イベントのチェックはこちら ▼

                    <p style=\"text-align: left; font-size: 13px;\">

                    <a href=\"{$applyUrl}\">{$applyUrl}</a>
                
                </div>";
    if ($event['capacity'] > 0) {
        $htmlBody .= "
                    <p>なお、定員に限りがございますので、お早めのご登録をお願い申し上げます。</p>
                    ";
    }
    $htmlBody .= "
                    <p>皆様のご参加を心よりお待ち申し上げております。</p>
                    <hr style=\"border: none; border-top: 1px solid #ddd;\">

                    <p style=\"font-size: 12px; color: #666;\">
                    ※このメールは配信専用メールアドレスから送信されています。<br>
                    ※このメールに返信いただいても、返信内容の確認及びご返信はできません。<br>
                    ※配信停止をご希望の方は、マイページからメール配信設定を変更してください。
                    </p>
                </div>
            </body>
        </html>
    ";

    // プレーンテキストメール本文を作成
    $plainTextBody = "平素より格別のご高配を賜り、誠にありがとうございます。\n"
        . "このたび、新たなイベント 「{$event['name']}」 を開催する運びとなりましたので、ご案内申し上げます。\n\n"
        . "■ 開催概要\n\n"
        . "開催日時: {$eventDateText}\n"
        . "開催場所: {$venueInfo}\n"
        . "対象: {$mail_target}\n"
        . "内容:\n{$event['description']}\n\n"
        . "お申し込みはこちら: {$applyUrl}\n\n";

    // プレーンテキストメール本文修正: 定員に関するメッセージを条件分岐で追加
    if ($event['capacity'] > 0) {
        $plainTextBody .= "なお、定員に限りがございますので、お早めのご登録をお願い申し上げます。\n";
    }
    $plainTextBody .= "皆様のご参加を心よりお待ち申し上げております。\n\n"
        . "※このメールは配信専用メールアドレスから送信されています。\n"
        . "※このメールに返信いただいても、返信内容の確認及びご返信はできません。\n"
        . "※配信停止をご希望の方は、マイページからメール配信設定を変更してください。";

    // メール送信対象者を取得 (notification_kbn = 1 のユーザーのみ)
    $subscribers = $DB->get_records_sql("
                SELECT email 
                FROM {user} 
                WHERE notification_kbn = 1
            ");
    // AWS SES クライアント設定
    $SesClient = new SesClient([
        'version' => 'latest',
        'region'  => 'ap-northeast-1', // 東京リージョン
        'credentials' => [
            'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
            'secret' => $_ENV['AWS_SECRET_ACCESS_KEY_ID'],
        ]
    ]);

    // 送信先アドレスのリストを作成
    $bccAddresses = [];
    // foreach ($subscribers as $subscriber) {
    //     if (filter_var($subscriber->email, FILTER_VALIDATE_EMAIL)) {
    //         $bccAddresses[] = $subscriber->email;
    //     }
    // }
    $bccAddresses[] = 'suzuwork2236@gmail.com';

    // SESでメール送信
    try {
        $result = $SesClient->sendEmail([
            'Destination' => [
                'BccAddresses' => $bccAddresses,
            ],
            'ReplyToAddresses' => ['no-reply@example.com'],
            'Source' => $_ENV['MAIL_FROM_ADDRESS'], // 検証済みアドレス
            'Message' => [
                'Body' => [
                    'Html' => [
                        'Charset' => 'UTF-8',
                        'Data' => $htmlBody,
                    ],
                    'Text' => [
                        'Charset' => 'UTF-8',
                        'Data' => $plainTextBody,
                    ],
                ],
                'Subject' => [
                    'Charset' => 'UTF-8',
                    'Data' => '大阪大学イベント開催のご案内',
                ],
            ],
        ]);
        // 成功時の処理
        echo "メール送信完了。メッセージID: " . $result['MessageId'] . "\n";
    } catch (AwsException $e) {
        // エラー処理
        error_log("メール送信失敗: " . $e->getMessage());
    }
} catch (Exception $e) {
    // write_log("致命的なエラーが発生しました: " . $e->getMessage(), $log_file);
    var_dump($e->getMessage());
    exit(1);
}
