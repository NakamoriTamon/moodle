<?php
define('CLI_SCRIPT', true);
// ファイル名: test_komoju_webhook.php
require '/var/www/vendor/autoload.php';
require(__DIR__ . '/../../config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

// コマンドライン引数を解析
$options = getopt("", ["type:", "id:", "amount:", "payment_method:"]);

// 引数の検証
if (empty($options['type']) || empty($options['id'])) {
    echo "Usage: php test_komoju_webhook.php --type=[tekijuku|event] --id=ID_NUMBER --amount=PAYMENT_AMOUNT --payment_method=PAYMENT_METHOD\n";
    echo "Example for tekijuku: php test_komoju_webhook.php --type=tekijuku --id=123 --amount=10000 --payment_method=2\n";
    echo "Example for event: php test_komoju_webhook.php --type=event --id=456 --amount=5000 --payment_method=2\n";
    exit(1);
}

// デフォルト値の設定
$amount = $options['amount'] ?? 10000;
$payment_method = $options['payment_method'] ?? 2;

// 現在のUTC時間を取得
$utcTime = new DateTime('now', new DateTimeZone('UTC'));
$capturedAt = $utcTime->format('Y-m-d\TH:i:s\Z'); // ISO 8601形式
// テストデータを作成
if ($options['type'] === 'tekijuku') {
    $data = [
        'id' => 'test_payment_' . time(),
        'status' => 'captured',
        'amount' => $amount,
        'currency' => 'JPY',
        'captured_at' => $capturedAt,
        'metadata' => [
            'tekujuku_id' => $options['id'],
            'payment_method' => $payment_method,
            'payment_method_type' => 'credit_card'
        ]
    ];
    simulateTekijukuPayment($data);
} elseif ($options['type'] === 'event') {
    // イベント用のテストデータは必要に応じて追加
    echo "Event payment simulation is not implemented yet.\n";
    exit(1);
} else {
    echo "Invalid type. Use 'tekijuku' or 'event'.\n";
    exit(1);
}

function simulateTekijukuPayment($data)
{
    global $DB;

    $baseModel = new BaseModel();
    $pdo = $baseModel->getPdo();

    try {
        $pdo->beginTransaction();

        $tekijuku_id = $data['metadata']['tekujuku_id'];
        $payment_method = $data['metadata']['payment_method'];

        // UTCから日本時間に変換
        $capturedAtJP = (new DateTime($data['captured_at']))
            ->setTimezone(new DateTimeZone('Asia/Tokyo'))
            ->format('Y-m-d H:i:s');

        var_dump($capturedAtJP);
        echo "Processing payment for tekijuku ID: {$tekijuku_id}\n";
        echo "Payment method: {$payment_method}\n";
        echo "Captured at (JP): {$capturedAtJP}\n";
        echo "Amount: {$data['amount']} {$data['currency']}\n";

        // 履歴テーブルにINSERT
        $stmt = $pdo->prepare("
            INSERT INTO mdl_tekijuku_commemoration_history (
                fk_tekijuku_commemoration_id,
                created_at,
                updated_at,
                paid_date,
                price,
                payment_method
            ) VALUES (
                :fk_tekijuku_commemoration_id,
                NOW(),
                NOW(),
                :paid_date,
                :price,
                :payment_method
            )
        ");

        $stmt->execute([
            ':fk_tekijuku_commemoration_id' => $tekijuku_id,
            ':paid_date' => $capturedAtJP,
            ':price' => $data['amount'],
            ':payment_method' => $payment_method
        ]);

        echo "History record inserted successfully.\n";

        // メインテーブルのステータスを更新
        $stmt = $pdo->prepare("
            UPDATE mdl_tekijuku_commemoration
            SET 
                paid_status = 3,
                payment_start_date = null
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $tekijuku_id
        ]);

        echo "Main record updated successfully.\n";

        $pdo->commit();
        echo "Transaction committed. Payment simulation complete.\n";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage() . "\n";
    }
}
