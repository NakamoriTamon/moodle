<?php
// 滞留決済レコードを削除するバッジ 毎時間実行想定
define('CLI_SCRIPT', true);
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require(__DIR__ . '/../../config.php');

use Dotenv\Dotenv;

error_log("決済滞留レコード削除バッジを開始します");
$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();

global $DB;

// ======= クレジット決済の処理（1時間以上経過） =======
try {
    // 現在の時刻から1時間前の時刻を計算
    $one_hour_ago = new DateTime();
    $one_hour_ago->modify('-1 hour');
    $one_hour_ago_str = $one_hour_ago->format('Y-m-d H:i:s');

    // トランザクション開始
    $transaction1 = $DB->start_delegated_transaction();
    // 削除前に対象レコード数をカウント
    $count_sql = "
        SELECT COUNT(*) 
        FROM mdl_tekijuku_commemoration
        WHERE 
            paid_status = 2
            AND payment_method = 2
            AND payment_start_date IS NOT NULL
            AND payment_start_date < :one_hour_ago
    ";
    $params = [
        'one_hour_ago' => $one_hour_ago_str
    ];
    $count_before = $DB->count_records_sql($count_sql, $params);

    // 削除実行
    $delete_sql = "
        DELETE FROM mdl_tekijuku_commemoration
        WHERE 
            paid_status = 2
            AND payment_method = 2
            AND payment_start_date IS NOT NULL
            AND payment_start_date < :one_hour_ago
    ";
    $result = $DB->execute($delete_sql, $params);

    // トランザクション確定
    $transaction1->allow_commit();

    error_log("クレジット決済滞留レコード削除が完了しました。{$count_before}件のレコードが削除されました。");
} catch (Exception $e) {
    // エラー発生時にはロールバック
    if (isset($transaction1)) {
        $transaction1->rollback($e);
    }
    error_log("クレジット決済レコード削除中にエラーが発生しました: " . $e->getMessage());
}

// ======= コンビニ・銀行振込の処理（7日以上経過） =======
try {
    // 現在の時刻から7日前の時刻を計算
    $seven_days_ago = new DateTime();
    $seven_days_ago->modify('-7 days');
    $seven_days_ago_str = $seven_days_ago->format('Y-m-d H:i:s');

    // トランザクション開始
    $transaction2 = $DB->start_delegated_transaction();

    // 削除前に対象レコード数をカウント
    $count_sql2 = "
        SELECT COUNT(*) 
        FROM mdl_tekijuku_commemoration
        WHERE 
            paid_status = 2
            AND (payment_method = 1 OR payment_method = 3)
            AND payment_start_date IS NOT NULL
            AND payment_start_date < :seven_days_ago
    ";
    $params2 = [
        'seven_days_ago' => $seven_days_ago_str
    ];
    $count_before2 = $DB->count_records_sql($count_sql2, $params2);

    // 削除実行
    $delete_sql2 = "
        DELETE FROM mdl_tekijuku_commemoration
        WHERE 
            paid_status = 2
            AND (payment_method = 1 OR payment_method = 3)
            AND payment_start_date IS NOT NULL
            AND payment_start_date < :seven_days_ago
    ";
    $result2 = $DB->execute($delete_sql2, $params2);

    // トランザクション確定
    $transaction2->allow_commit();

    error_log("コンビニ・銀行振込滞留レコード削除が完了しました。{$count_before2}件のレコードが削除されました。");
} catch (Exception $e) {
    // エラー発生時にはロールバック
    if (isset($transaction2)) {
        $transaction2->rollback($e);
    }
    error_log("コンビニ・銀行振込レコード削除中にエラーが発生しました: " . $e->getMessage());
}

error_log("決済滞留レコード削除バッジを終了します");
