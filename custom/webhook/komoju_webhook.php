<?php
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/config.php'); // Moodleの設定を読み込む
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');

use Dotenv\Dotenv;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();

// POSTリクエストの内容を取得
$input = file_get_contents('php://input');
$data_list = json_decode($input, true);
$data = $data_list['data'];

// KOMOJUの署名検証（セキュリティ対策）
$headers = getallheaders();
$signature = $headers['X-Komoju-Signature'] ?? '';

// 本番環境のではコメント解除
if (!hash_equals(hash_hmac('sha256', $input, $komoju_webhook_secret_key), $signature)) {
    http_response_code(400);
    exit('Invalid signature');
}

// 決済完了のステータスをチェック
if ($data['status'] === 'captured') {
    $baseModel = new BaseModel();
    $pdo = $baseModel->getPdo();
    $pdo->beginTransaction();
    try {
        if (!empty($data['metadata']['tekujuku_id'])) {
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
                    paid_date = :paid_date
                WHERE id = :id
            ");

            $stmt->execute([
                ':paid_date' => $capturedAtJP,
                ':id' => $data['metadata']['tekujuku_id']
            ]);
        } else {

            $name = $data['metadata']['user_name'] ?? null;
            $event_id = $data['metadata']['event_id'] ?? null;
            $event_application_id = $data['metadata']['event_application_id'] ?? null;
            $payment_method_type = $data['metadata']['payment_method_type'] ?? null;

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

            foreach ($eventApplication['course_infos'] as $course) {
                global $url_secret_key;
                $encrypt_event_application_course_info_id = encrypt($course['id'], $url_secret_key);
                // QR生成
                $baseUrl = $CFG->wwwroot; // MoodleのベースURL（本番環境では自動で変更される）
                $qrCode = new QrCode($encrypt_event_application_course_info_id);
                $writer = new PngWriter();
                $qrCodeImage = $writer->write($qrCode)->getString();
                $temp_file = tempnam(sys_get_temp_dir(), 'qr_');
                $qrCodeBase64 = base64_encode($qrCodeImage);
                $dataUri = 'data:image/png;base64,' . $qrCodeBase64;
                file_put_contents($temp_file, $qrCodeImage);

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

                $mail->setFrom($_ENV['MAIL_FROM_ADRESS'], 'Sender Name');
                $mail->addAddress($course['participant_mail'], 'Recipient Name');

                $mail->addReplyTo('no-reply@example.com', 'No Reply');
                $mail->isHTML(true);

                $day = new DateTime($course["course_date"]);
                $course_date = $day->format('Ymd');
                $ymd = $day->format('Y/m/d');
                $dateTime = DateTime::createFromFormat('H:i:s', $event['start_hour']);
                $start_hour = $dateTime->format('H:i'); // "00:00"
                $dateTime = DateTime::createFromFormat('H:i:s', $event['end_hour']);
                $end_hour = $dateTime->format('H:i'); // "00:00"
                $qr_img = 'qr_code_' . $course_date . '.png';
                // QRをインライン画像で追加
                $mail->addEmbeddedImage($temp_file, 'qr_code_cid', $qr_img);

                $htmlBody = "
                <div style=\"text-align: center; font-family: Arial, sans-serif;\">
                    <p style=\"text-align: left; font-weight:bold;\">" . $name . "様</p>
                    <P style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">ご購入ありがとうございます。チケットのご購入が完了いたしました。</P>
                    <P style=\"text-align: left;  font-size: 13px; margin:0; margin-bottom: 30px; \">QRはマイページでも確認できます。</P>
                    <div>
                        <img src=\"cid:qr_code_cid\" alt=\"QR Code\" style=\"width: 150px; height: 150px; display: block; margin: 0 auto;\" />
                    </div>
                    <p style=\"margin-top: 20px; font-size: 14px;\">" . $event["name"] . "</p>
                    <p style=\"margin-top: 20px; font-size: 14px;\">開催日：" . $ymd . "</p>
                    <p style=\"margin-top: 20px; font-size: 14px;\">時間　：" . $start_hour . "～" . $end_hour . "</p>
                    <p style=\"margin-top: 30px; font-size: 13px; text-align: left;\">このメールは、配信専用アドレスで配信されています。<br>このメールに返信いただいても、返信内容の確認及びご返信ができません。
                    あらかじめご了承ください。</p>
                </div>
            ";

                $name = "";

                $mail->Subject = 'チケットの購入が完了しました';
                $mail->Body = $htmlBody;

                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $mail->send();
                unlink($temp_file);
            }
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(["error" => "Invalid signature"]);
        exit;
    }

    // レスポンスを返す（KOMOJUに成功を通知）
    http_response_code(200);
    echo json_encode(['message' => 'Webhook received']);
}

function encrypt($id, $key)
{
    $iv = substr(hash('sha256', $key), 0, 16);
    return urlencode(base64_encode(openssl_encrypt($id, 'AES-256-CBC', $key, 0, $iv)));
}
