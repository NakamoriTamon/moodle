<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');

// セッション開始
session_start();

// 接続情報取得
global $DB;

// POSTデータの取得 (バリデーションは別途行う)
$id         = $_POST['id'] ?? '';
$name       = trim($_POST['name'] ?? '');
$_SESSION['old_input'] = $_POST;

// 必要なバリデーションや処理を行う
$target_name_error = validate_target_name($name);

if ($target_name_error) {
    $_SESSION['errors'] = ['name' => $target_name_error];
    $_SESSION['message_error'] = '登録に失敗しました';

    header('Location: /custom/admin/app/Views/master/target/upsert.php' . (!empty($id) ? '?id=' . urlencode($id) : ''));
    exit;
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message_error'] = '登録に失敗しました';
            header('Location: /custom/admin/app/Views/master/target/index.php');
            exit;
        }
    }

    if (!empty($id)) {
        $count = $DB->count_records_select('target', "name = ? AND is_delete = 0 AND id <> ?", [$name, $id]);
    } else {
        $count = $DB->count_records_select('target', "name = ? AND is_delete = 0", [$name]);
    }

    if ($count > 0) {
        $_SESSION['message_error'] = '登録に失敗しました';
        $_SESSION['errors'] = ['name' => '既に使用されています。'];
        header('Location: /custom/admin/app/Views/master/target/upsert.php' . (!empty($id) ? '?id=' . urlencode($id) : ''));
        exit;
    }

    try {
        $transaction = $DB->start_delegated_transaction();

        $data = new stdClass();
        $data->name       = $name;
        $data->created_at = date('Y-m-d H:i:s');
        $data->updated_at = date('Y-m-d H:i:s');

        if (!$id) {
            $DB->insert_record('target', $data);
        } else {
            $data->id = $id;
            $DB->update_record('target', $data);
        }
        $transaction->allow_commit();
        $_SESSION['message_success'] = '登録が完了しました';
        header('Location: /custom/admin/app/Views/master/target/index.php');
        exit;
    } catch (Throwable $e) {
        try {
            $transaction->rollback($e);
        } catch (Throwable $e) {
            $_SESSION['message_error'] = '登録に失敗しました';
            header('Location: /custom/admin/app/Views/master/target/upsert.php' . (!empty($id) ? '?id=' . urlencode($id) : ''));
            exit;
        }
    }
}
