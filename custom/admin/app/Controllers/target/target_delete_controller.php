<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

// 接続情報取得
global $DB;

// POSTデータの取得 (バリデーションは別途行う)
$id = $_POST['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = '削除に失敗しました';
        header('Location: /custom/admin/app/Views/master/target/index.php');
        exit;
    }
}

$data = new stdClass();
$data->id = $id;
$data->is_delete = 1;

try {
    $transaction = $DB->start_delegated_transaction();
    $DB->update_record('target', $data);
    $transaction->allow_commit();
    $_SESSION['message_success'] = '削除が完了しました';
    header('Location: /custom/admin/app/Views/master/target/index.php');
    exit;
} catch (Throwable $e) {
    try {
        $transaction->rollback($e);
    } catch (Throwable $e) {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/admin/app/Views/master/target/index.php');
        exit;
    }
}
