<?php
// 4/1に対象の適塾ユーザーのpaid_statusを期限切れにするバッジ　毎日4/1 0:00に実行想定
define('CLI_SCRIPT', true);
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require(__DIR__ . '/../../config.php');

use Dotenv\Dotenv;

error_log("会員資格期限切れバッジを開始します");
$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();

global $DB;

// 現在の日付を取得
$current_date = new DateTime();
$current_year = (int)$current_date->format('Y');
$current_month = (int)$current_date->format('n');

// 年度の計算（4月1日を年度の始まりとする）
$fiscal_year = $current_year;
if ($current_month < 4) {
    $fiscal_year = $current_year - 1;
}

// is_deposit_{fiscal_year}が存在する年度かどうかを確認
$deposit_condition = "";
if ($fiscal_year >= 2024 && $fiscal_year <= 2030) {
    $deposit_condition = "AND is_deposit_{$fiscal_year} = 0";
}

// 更新対象のレコードを取得するSQL
$sql = "
    UPDATE mdl_tekijuku_commemoration
    SET paid_status = 1
    WHERE 
        paid_status = 3
        AND is_subscription = 0
        {$deposit_condition}
";

try {
    // トランザクション開始
    $transaction = $DB->start_delegated_transaction();

    // 更新実行
    $result = $DB->execute($sql);

    // トランザクション確定
    $transaction->allow_commit();
} catch (Exception $e) {
    // エラー発生時にはロールバック
    if (isset($transaction)) {
        $transaction->rollback($e);
    }
    error_log("エラーが発生しました: " . $e->getMessage());
}
error_log("会員資格期限切れバッジを終了します");
