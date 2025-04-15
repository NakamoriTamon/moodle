<?php
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/config.php'); // Moodleの設定を読み込む
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');

use Dotenv\Dotenv;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();

// POSTリクエストの内容を取得
$input = file_get_contents('php://input');
$data_list = json_decode($input, true);

// イベントタイプとデータの取得
$event_type = $data_list['type'] ?? '';
$data = $data_list['data'] ?? [];

// デバッグ用ログ
error_log('コモジュ:' . json_encode($data_list));

// KOMOJUの署名検証（セキュリティ対策）
$headers = getallheaders();
$signature = $headers['X-Komoju-Signature'] ?? '';

// 本番環境のではコメント解除
// if (!hash_equals(hash_hmac('sha256', $input, $komoju_webhook_secret_key), $signature)) {
//     http_response_code(400);
//     exit('Invalid signature');
// }

// イベントタイプに基づいて処理を分岐
switch ($event_type) {
    case 'payment.captured':
        // 決済完了の処理
        handlePaymentCaptured($data);
        break;

    case 'customer.created':
        // 顧客作成の処理
        handleCustomerCreated($data);
        break;

    case 'customer.updated':
        // 顧客情報更新の処理
        handleCustomerUpdated($data);
        break;

    default:
        // 未対応のイベントタイプ
        error_log('未対応のKOMOJUイベント: ' . $event_type);
        http_response_code(200); // 処理しなくても成功として返す
        echo json_encode(['message' => 'Event type not handled']);
        exit;
}

// レスポンスを返す（KOMOJUに成功を通知）
http_response_code(200);
echo json_encode(['message' => 'Webhook received']);

/**
 * 決済完了時の処理
 * 
 * @param array $data 決済データ
 */
function handlePaymentCaptured($data)
{
    if ($data['status'] !== 'captured') {
        return; // キャプチャされた支払いでない場合は終了
    }

    $baseModel = new BaseModel();
    $pdo = $baseModel->getPdo();
    $pdo->beginTransaction();

    try {
        if (!empty($data['metadata']['tekujuku_id'])) {
            processTekijukuPayment($data, $pdo);
        } else {
            processEventPayment($data, $pdo);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('支払い処理エラー: ' . $e->getMessage());
        http_response_code(400);
        echo json_encode(["error" => "Payment processing failed"]);
        exit;
    }
}

/**
 * 適塾関連の支払い処理
 * 
 * @param array $data 決済データ
 * @param PDO $pdo データベース接続
 */
function processTekijukuPayment($data, $pdo)
{
    $payment_method_type = $data['metadata']['payment_method_type'] ?? null;
    // クレジットの2重送信を回避する
    if (empty($payment_method_type)) {
        exit;
    }

    $capturedAt = $data['captured_at'] ?? null;
    if ($capturedAt) {
        // UTC → 日本時間に変換
        $capturedAtJP = (new DateTime($capturedAt))
            ->setTimezone(new DateTimeZone('Asia/Tokyo'))
            ->format('Y-m-d H:i:s');
    }

    $stmt = $pdo->prepare("
        UPDATE mdl_tekijuku_commemoration
        SET 
            paid_date = :paid_date,
            paid_status = :paid_status
        WHERE id = :id
    ");

    $stmt->execute([
        ':paid_date' => $capturedAtJP,
        ':paid_status' => PAID_STATUS['COMPLETED'],
        ':id' => $data['metadata']['tekujuku_id']
    ]);

    $stmt = $pdo->prepare("
        INSERT INTO mdl_tekijuku_commemoration_history (
            paid_date,
            price,
            fk_tekijuku_commemoration_id, 
            payment_method
        ) VALUES (
            :paid_date,
            :price,
            :fk_tekijuku_commemoration_id,
            :payment_method
        )
    ");

    $stmt->execute([
        ':paid_date' => $capturedAtJP,
        ':price' => $data['amount'],
        ':fk_tekijuku_commemoration_id' => $data['metadata']['tekujuku_id'],
        ':payment_method' => $data['metadata']['payment_method_type']
    ]);
}

/**
 * イベント関連の支払い処理
 * 
 * @param array $data 決済データ
 * @param PDO $pdo データベース接続
 */
function processEventPayment($data, $pdo)
{
    global $CFG, $url_secret_key;

    $name = $data['metadata']['user_name'] ?? null;
    $event_id = $data['metadata']['event_id'] ?? null;
    $event_application_id = $data['metadata']['event_application_id'] ?? null;
    $payment_method_type = $data['metadata']['payment_method_type'] ?? null;
    $user_email = $data['metadata']['user_email'] ?? null;

    // クレジットの2重送信を回避する
    if (empty($payment_method_type)) {
        exit;
    }

    $eventModel = new EventModel();
    $event = $eventModel->getEventById($event_id);
    $eventApplicationModel = new EventApplicationModel();
    $eventApplication = $eventApplicationModel->getEventApplicationByEventId($event_application_id);

    // 支払日を取得
    $capturedAt = $data['captured_at'] ?? null;

    if ($capturedAt) {
        // UTC → 日本時間に変換
        $capturedAtJP = (new DateTime($capturedAt))
            ->setTimezone(new DateTimeZone('Asia/Tokyo'))
            ->format('Y-m-d H:i:s');
    }

    // mdl_event_applicationのpayment_date(支払日)を更新
    $stmt = $pdo->prepare("
    UPDATE mdl_event_application
    SET 
        payment_date = :payment_date
    WHERE id = :id
    ");

    $stmt->execute([
        ':payment_date' => $capturedAtJP,
        ':id' => $event_application_id // 一意の識別子をWHERE条件として設定
    ]);

    // ISO 8601形式の日時を MySQL の DATETIME 形式に変換
    $captured_at = date('Y-m-d H:i:s', strtotime($data['captured_at']));

    // KOMOJUの情報を登録
    $stmt2 = $pdo->prepare("INSERT INTO moodle.mdl_komojus 
    (id, status, amount, currency, payment_method_type, captured_at, metadata, event_application_id, created_at, updated_at)
    VALUES(:id, :status, :amount, :currency, :payment_method_type, :captured_at, :metadata, :event_application_id,  CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);");
    $stmt2->execute([
        ':id' => $data['id'],
        ':status' => $data['status'],
        ':amount' => $data['amount'],
        ':currency' => $data['currency'],
        ':payment_method_type' => $payment_method_type,
        ':captured_at' => $captured_at,
        ':metadata' => json_encode($data['metadata']),
        ':event_application_id' => $event_application_id
    ]);

    // QRコード生成とメール送信
    sendQRCodeEmails($eventApplication, $event, $user_email, $name);
}

/**
 * QRコード生成とメール送信
 * 
 * @param array $eventApplication イベント申込情報
 * @param array $event イベント情報
 * @param string $user_email ユーザーメールアドレス
 * @param string $name ユーザー名
 */
function sendQRCodeEmails($eventApplication, $event, $user_email, $name)
{
    global $CFG, $url_secret_key;

    foreach ($eventApplication['course_infos'] as $course) {
        global $url_secret_key;
        $encrypt_event_application_course_info_id = encrypt($course['id'], $url_secret_key);

        // SESのクライアント設定
        $SesClient = new SesClient([
            'version' => 'latest',
            'region'  => 'ap-northeast-1', // 東京リージョン
            'credentials' => [
                'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY_ID'],
            ]
        ]);

        $recipients = [$course['participant_mail']];

        $ymd = "";
        if($event['event_kbn'] == EVERY_DAY_EVENT) {
            $ymd = (new DateTime($event['start_event_date']))->format('Y年m月d日') . "～" . (new DateTime($event['end_event_date']))->format('Y年m月d日');
        } else {
            $day = new DateTime($course["course_date"]);
            $course_date = $day->format('Ymd');
            $ymd = $day->format('Y/m/d');
        }
        $dateTime = DateTime::createFromFormat('H:i:s', $event['start_hour']);
        $start_hour = $dateTime->format('H:i'); // "00:00"
        $dateTime = DateTime::createFromFormat('H:i:s', $event['end_hour']);
        $end_hour = $dateTime->format('H:i'); // "00:00"

        // ✅ QRコードを生成（バイナリデータ）
        $qrCode = new QrCode($encrypt_event_application_course_info_id);
        $writer = new PngWriter();
        $qrCodeImage = $writer->write($qrCode)->getString();
        $qr_base64 = base64_encode($qrCodeImage);

        // ✅ MIME メッセージの作成
        $boundary = md5(time());

        $rawMessage = "From: 知の広場 <{$_ENV['MAIL_FROM_ADDRESS']}>\r\n";
        $rawMessage .= "To: " . implode(',', $recipients) . "\r\n";
        $rawMessage .= "Subject: =?UTF-8?B?" . base64_encode("チケットの購入が完了しました") . "?=\r\n";
        $rawMessage .= "MIME-Version: 1.0\r\n";
        $rawMessage .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";

        $ticket_type = TICKET_TYPE['SELF'];
        if ($user_email !== $course['participant_mail']) {
            $ticket_type = TICKET_TYPE['ADDITIONAL'];
        }

        $dear = !empty($name) ? '様' : '';
        $htmlBody = "
                <div style=\"text-align: center; font-family: Arial, sans-serif;\">
                    <p style=\"text-align: left; font-weight:bold;\">" . $name . $dear . "</p><br />
                    <P style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">ご購入ありがとうございます。チケットのご購入が完了いたしました。</P>
                    <P style=\"text-align: left;  font-size: 13px; margin:0; margin-bottom: 30px; \">QRはマイページでも確認できます。</P>
                    <div>
                        <img src=\"cid:qr_code_cid\" alt=\"QR Code\" style=\"width: 150px; height: 150px; display: block; margin: 0 auto;\" />
                    </div>
                    <p style=\"margin-top: 20px; font-size: 14px;\">" . $event["name"] . "</p>
                    <p style=\"margin-top: 20px; font-size: 14px;\">開催回数：第" . $course['no'] . "回</p>
                    <p style=\"margin-top: 20px; font-size: 14px;\">開催日：" . $ymd . "</p>
                    <p style=\"margin-top: 20px; font-size: 14px;\">時間　：" . $start_hour . "～" . $end_hour . "</p><br />
                    <p style=\"margin-top: 30px; font-size: 13px; text-align: left;\">このメールは、配信専用アドレスで配信されています。<br>このメールに返信いただいても、返信内容の確認及びご返信ができません。
                    あらかじめご了承ください。</p>
                </div>";

        $rawMessage .= "--{$boundary}\r\n";
        $rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n";
        $rawMessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $rawMessage .= $htmlBody . "\r\n\r\n";

        // ✅ QRコード画像の添付（インライン）
        $rawMessage .= "--{$boundary}\r\n";
        $rawMessage .= "Content-Type: image/png; name=\"qr_code.png\"\r\n";
        $rawMessage .= "Content-Description: QR Code\r\n";
        $rawMessage .= "Content-Disposition: inline; filename=\"qr_code.png\"\r\n";
        $rawMessage .= "Content-ID: <qr_code_cid>\r\n";
        $rawMessage .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $rawMessage .= chunk_split($qr_base64) . "\r\n\r\n";

        $rawMessage .= "--{$boundary}--";

        // ✅ SES で送信
        try {
            $result = $SesClient->sendRawEmail([
                'RawMessage' => [
                    'Data' => $rawMessage
                ],
                'ReplyToAddresses' => ['no-reply@example.com'],
                'Source' => $_ENV['MAIL_FROM_ADDRESS'],
                'Destinations' => $recipients
            ]);
        } catch (AwsException $e) {
            $_SESSION['message_error'] = '送信に失敗しました: ' . $e->getMessage();
            redirect('/custom/app/Views/user/pass_mail.php');
            exit;
        }
    }
}

/**
 * 顧客作成イベントの処理
 * 
 * @param array $data 顧客データ
 */
function handleCustomerCreated($data)
{
    global $DB;
    $external_payment_reference = $data['id'] ?? null;
    $email = $data['email'] ?? null;

    if (!$external_payment_reference || !$email) {
        error_log('顧客IDまたは適塾IDがありません');
        return;
    }

    $user = $DB->get_record('tekijuku_commemoration', ['email' => $email, 'is_delete' => 0]);
    $id = $user->id;

    $baseModel = new BaseModel();
    $pdo = $baseModel->getPdo();

    try {
        $pdo->beginTransaction();

        // ユーザーテーブルを顧客IDで更新
        $stmt = $pdo->prepare("
            UPDATE mdl_tekijuku_commemoration
            SET external_payment_reference = :external_payment_reference
            WHERE id = :id
        ");

        $stmt->execute([
            ':external_payment_reference' => $external_payment_reference,
            ':id' => $id
        ]);
        $pdo->commit();
        error_log('顧客ID保存成功: ' . $id . ' -> ' . $external_payment_reference);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('顧客ID保存エラー: ' . $e->getMessage());
    }
}

/**
 * 顧客情報更新イベントの処理
 * 
 * @param array $data 顧客データ
 */
function handleCustomerUpdated($data)
{
    $external_payment_reference = $data['id'] ?? null;
    $email = $data['email'] ?? null;
    $source = $data['source'] ?? null;

    if (!$external_payment_reference || !$email) {
        error_log('顧客IDまたはメールアドレスがありません');
        return;
    }

    $baseModel = new BaseModel();
    $pdo = $baseModel->getPdo();

    try {
        // ユーザーの支払い方法情報を更新
        $stmt = $pdo->prepare("
            UPDATE mdl_tekijuku_commemoration
            SET external_payment_reference = :external_payment_reference
            WHERE email = :email
        ");

        $stmt->execute([
            ':external_payment_reference' => $external_payment_reference,
            ':email' => $email
        ]);

        error_log('顧客情報更新成功: ' . $email);
    } catch (Exception $e) {
        error_log('顧客情報更新エラー: ' . $e->getMessage());
    }
}

/**
 * データを暗号化する
 * 
 * @param mixed $id 暗号化するID
 * @param string $key 暗号化キー
 * @return string 暗号化された文字列
 */
function encrypt($id, $key)
{
    $iv = substr(hash('sha256', $key), 0, 16);
    return urlencode(base64_encode(openssl_encrypt($id, 'AES-256-CBC', $key, 0, $iv)));
}
