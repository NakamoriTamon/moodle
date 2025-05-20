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

// AWS SES クライアント設定
$SesClient = new SesClient([
    'version' => 'latest',
    'region'  => 'ap-northeast-1', // 東京リージョン
    'credentials' => [
        'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
        'secret' => $_ENV['AWS_SECRET_ACCESS_KEY_ID'],
    ]
]);

// バルク送信の最大数（AWS SESのドキュメントによる）
$BATCH_SIZE = 50; // SESの制限に基づく値

// イベント情報の取得
try {
    $stmt = $pdo->prepare("SELECT capacity, name, event_kbn, event_date, start_hour, end_hour, venue_name, description, target, start_event_date, end_event_date FROM mdl_event WHERE id = :id");
    $stmt->execute([':id' => $eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo "エラー: 指定されたイベントID {$eventId} は存在しません。\n";
        exit(1);
    }

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

    // HTMLメールテンプレートを作成
    $htmlTemplate = "
        <html>
        <head>
            <title>{{eventName}}のご案内</title>
        </head>
        <body>
            <div style=\"font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto;\">
                <p>平素より格別のご高配を賜り、誠にありがとうございます。<br>
                このたび、新たなイベント 「{{eventName}}」 を開催する運びとなりましたので、ご案内申し上げます。</p>

                <h2 style=\"margin-top: 0; color: #3366cc; font-size: 18px;\">■ 開催概要</h2>
                
                <p><strong>開催日時:</strong> {{eventDateText}}</p>
                
                <p><strong>開催場所:</strong> {{venueInfo}}</p>
                
                <p><strong>対象:</strong> {{targetText}}</p>
                
                <p><strong>内容:</strong><br> {{description}}</p>
                
                <br>
                
                <div style=\"text-align: center;\">

                    <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">▼ イベントのチェックはこちら ▼

                    <p style=\"text-align: left; font-size: 13px;\">

                    <a href=\"{{applyUrl}}\">{{applyUrl}}</a>
                
                </div>
                {{#hasCapacity}}
                <p>なお、定員に限りがございますので、お早めのご登録をお願い申し上げます。</p>
                {{/hasCapacity}}
                
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

    // プレーンテキストテンプレートを作成
    $textTemplate = "平素より格別のご高配を賜り、誠にありがとうございます。
このたび、新たなイベント 「{{eventName}}」 を開催する運びとなりましたので、ご案内申し上げます。

■ 開催概要

開催日時: {{eventDateText}}
開催場所: {{venueInfo}}
対象: {{targetText}}
内容:
{{description}}

お申し込みはこちら: {{applyUrl}}

{{#hasCapacity}}
なお、定員に限りがございますので、お早めのご登録をお願い申し上げます。
{{/hasCapacity}}
皆様のご参加を心よりお待ち申し上げております。

※このメールは配信専用メールアドレスから送信されています。
※このメールに返信いただいても、返信内容の確認及びご返信はできません。
※配信停止をご希望の方は、マイページからメール配信設定を変更してください。";

    // テンプレート名を生成（一意であることを保証するためにタイムスタンプを含める）
    $templateName = 'event_notification_' . $eventId . '_' . time();

    // SESにテンプレートを作成
    try {
        $SesClient->createTemplate([
            'Template' => [
                'TemplateName' => $templateName,
                'SubjectPart' => '【大阪大学知の広場】イベント開催のご案内',
                'HtmlPart' => $htmlTemplate,
                'TextPart' => $textTemplate,
            ]
        ]);

        echo "メールテンプレート「{$templateName}」を作成しました。\n";
    } catch (AwsException $e) {
        // テンプレートがすでに存在する場合など
        echo "テンプレート作成中にエラーが発生しました: " . $e->getMessage() . "\n";
        // 既存のテンプレートを更新するオプションもあります
        exit(1);
    }

    // テンプレートに渡すデフォルトデータ
    $templateData = [
        'eventName' => $event['name'],
        'eventDateText' => $eventDateText,
        'venueInfo' => $venueInfo,
        'targetText' => $mail_target,
        'description' => $event['description'],
        'applyUrl' => $applyUrl,
        'hasCapacity' => $event['capacity'] > 0
    ];

    // メール送信対象者を取得 - 新しい条件を適用
    $recipients = $DB->get_records_sql("
        SELECT u.id, u.email, u.firstname, u.lastname
        FROM {user} u
        JOIN {role_assignments} ra ON ra.userid = u.id
        WHERE u.notification_kbn = 1
        AND ra.roleid = 7
        AND u.confirmed = 1
        AND u.deleted = 0
        AND u.email != ''
    ");

    // 送信先アドレスのリストを作成
    $validEmails = [];
    foreach ($recipients as $recipient) {
        if (filter_var($recipient->email, FILTER_VALIDATE_EMAIL)) {
            $validEmails[] = [
                'email' => $recipient->email,
                'name' => trim($recipient->firstname . ' ' . $recipient->lastname)
            ];
        }
    }

    $totalRecipients = count($validEmails);
    echo "送信対象者数: {$totalRecipients}人\n";

    if ($totalRecipients === 0) {
        echo "送信対象者がいません。処理を終了します。\n";
        // テンプレートをクリーンアップ
        try {
            $SesClient->deleteTemplate(['TemplateName' => $templateName]);
            echo "テンプレート「{$templateName}」を削除しました。\n";
        } catch (AwsException $e) {
            echo "テンプレート削除中にエラーが発生しました: " . $e->getMessage() . "\n";
        }
        exit(0);
    }

    // バッチ処理でメール送信
    $successCount = 0;
    $failCount = 0;
    $batches = array_chunk($validEmails, $BATCH_SIZE);

    foreach ($batches as $batchIndex => $batch) {
        echo "バッチ " . ($batchIndex + 1) . "/" . count($batches) . " 処理中...\n";

        $destinations = [];

        foreach ($batch as $recipient) {
            $email = $recipient['email'];

            // ドメイン名が有効かDNSチェック（MXレコード確認）
            $domain = substr(strrchr($email, "@"), 1);

            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && $domain && checkdnsrr($domain, "MX")) {
                $destinations[] = [
                    'Destination' => [
                        'ToAddresses' => [$recipient['email']]
                    ],
                    'ReplacementTemplateData' => json_encode([
                        'name' => $recipient['name']
                    ])
                ];
            }
        }

        try {
            $result = $SesClient->sendBulkTemplatedEmail([
                'Source' => "大阪大学知の広場事務局 <{$_ENV['MAIL_FROM_ADDRESS']}>",
                'ReplyToAddresses' => ['no-reply@example.com'],
                'DefaultTemplateData' => json_encode($templateData),
                'Template' => $templateName,
                'Destinations' => $destinations
            ]);

            // 成功したメッセージと失敗したメッセージを集計
            foreach ($result['Status'] as $status) {
                if ($status['Status'] === 'Success') {
                    $successCount++;
                } else {
                    $failCount++;
                    echo "送信失敗: " . $status['Error'] . "\n";
                }
            }

            echo "バッチ " . ($batchIndex + 1) . " 送信完了\n";
        } catch (AwsException $e) {
            echo "バッチ " . ($batchIndex + 1) . " 送信エラー: " . $e->getMessage() . "\n";
            $failCount += count($batch);
        }

        // AWS SESのスロットリング対策として少し待機
        if ($batchIndex < count($batches) - 1) {
            sleep(1);
        }
    }

    // 結果報告
    echo "送信処理完了。成功: {$successCount}件, 失敗: {$failCount}件\n";

    // テンプレートをクリーンアップ
    try {
        $SesClient->deleteTemplate(['TemplateName' => $templateName]);
        echo "テンプレート「{$templateName}」を削除しました。\n";
    } catch (AwsException $e) {
        echo "テンプレート削除中にエラーが発生しました: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "致命的なエラーが発生しました: " . $e->getMessage() . "\n";
    echo "スタックトレース: " . $e->getTraceAsString() . "\n";
    exit(1);
}
