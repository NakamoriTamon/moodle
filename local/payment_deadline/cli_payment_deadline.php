<?php
define('CLI_SCRIPT', true);
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');

$baseModel = new BaseModel();
$model = new EventApplicationModel();
$applications = $model->getEventApplicationByPaymentKbn_Zero();

foreach ($applications as $row) {
    try {
        $application_date = $row['application_date']; // 申込日
        $payment_date = $row['payment_date']; // 支払日
        // 1 => 'コンビニ決済', 2 => 'クレジット', 3 => '銀行振込'
        $pay_method = $row['pay_method'];

        // 支払い区分：0(待機),1(払い済み),2(未払いキャンセル)
        $payment_kbn = 0;

        if ($payment_date) {
            // 支払日が登録されている場合、支払い区分を "1（払い済み）" に更新
            $payment_kbn = 1;
        } else {
            // 支払期限を超過している場合、"2（未払いキャンセル）" に設定
            $now = new DateTime(); // 現在日時
            $limit_hours = match ($pay_method) {
                1 => 72,  // コンビニ払い → 72時間（3日）
                2 => 24,  // クレジットカード → 24時間（1日）
                3 => 168, // 銀行振込 → 168時間（7日）
                default => 0
            };

            // 申込日からの支払期限を算出
            if ($limit_hours > 0) {
                $application_date = new DateTime($application_date);
                $application_date->modify("+{$limit_hours} hours");

                if ($now > $application_date) {
                    $payment_kbn = 2; // 支払期限超過 → 未払いキャンセル
                }
            }
        }

        if (!empty($payment_kbn)) {
            $pdo = $baseModel->getPdo();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                UPDATE mdl_event_application
                SET 
                    payment_kbn = :payment_kbn
                WHERE id = :id
            ");

            $id = $row['id'];
            $stmt->execute([
                ':payment_kbn' => $payment_kbn,
                ':id' => $id
            ]);
            // キャンセル扱いの場合はログに残すようにする
            if ($payment_kbn == 2) {
                error_log("未払いキャンセルに変更します : id=" . $id);
            }
            $pdo->commit();
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating event application ID {$row['id']}: " . $e->getMessage());
        continue;
    }
}
