<?php
require_once('/var/www/html/moodle/config.php'); // Moodleの設定を読み込む
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

// POSTリクエストの内容を取得
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// KOMOJUの署名検証（セキュリティ対策）
$headers = getallheaders();
$signature = $headers['X-Komoju-Signature'] ?? '';

// 本番環境のではコメント解除
// if (!hash_equals(hash_hmac('sha256', $input, $komoju_webhook_secret_key), $signature)) {
//     http_response_code(400);
//     exit('Invalid signature');
// }

// 決済完了のステータスをチェック
if ($data['status'] === 'captured') {
    $komoju_id = $data['id'] ?? null;
    $event_application_id = $data['metadata']['event_application_id'] ?? null;

    // 支払日を取得
    $capturedAt = $data['captured_at'] ?? null;

    if ($capturedAt) {
        // UTC → 日本時間に変換
        $capturedAtJP = (new DateTime($capturedAt))
            ->setTimezone(new DateTimeZone('Asia/Tokyo'))
            ->format('Y-m-d H:i:s');
    }

    $baseModel = new BaseModel();
    $pdo = $baseModel->getPdo();

    // mdl_event_applicationのpayment_date(支払日)を更新
    $stmt = $pdo->prepare("
        UPDATE mdl_event_application
        SET 
            komoju_id = :komoju_id,
            payment_date = :payment_date
        WHERE id = :id
    ");

    $stmt->execute([
        ':komoju_id' => $komoju_id,
        ':payment_date' => $capturedAtJP,
        ':id' => $event_application_id // 一意の識別子をWHERE条件として設定
    ]);

}

// レスポンスを返す（KOMOJUに成功を通知）
http_response_code(200);
echo json_encode(['message' => 'Webhook received']);
?>
