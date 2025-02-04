<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

// 接続情報取得
global $DB;

// POSTデータの取得 (バリデーションは別途行う)
$id       = $_POST['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = '削除に失敗しました';
        header('Location: /custom/admin/app/Views/master/category/index.php');
        exit;
    }
}

$data = new stdClass();
$data->id = $id;
$data->is_delete = 1;

try {
    $transaction = $DB->start_delegated_transaction();
    $categoryRecord = $DB->get_record('category', ['id' => $id]);
    if ($categoryRecord && !empty($categoryRecord->path)) {
        $filePath = '/var/www/html/moodle/uploads/category/' . $categoryRecord->path;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $DB->update_record('category', $data);
    $transaction->allow_commit();
    $_SESSION['message_success'] = '削除が完了しました';
    header('Location: /custom/admin/app/Views/master/category/index.php');
    exit;
} catch (Exception $e) {
    if (isset($transaction)) {
        $transaction->rollback($e);
    }
    $_SESSION['message_error'] = '削除に失敗しました';
    header('Location: /custom/admin/app/Views/master/category/index.php');
    exit;
}
