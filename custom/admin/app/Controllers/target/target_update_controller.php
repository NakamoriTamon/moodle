<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

// セッション開始
session_start();

// 接続情報取得
global $DB;

// POSTデータの取得 (バリデーションは別途行う)
$id         = $_POST['id'] ?? '';
$name       = trim($_POST['name'] ?? '');
$createdAt  = date('Y-m-d H:i:s');
$updatedAt  = date('Y-m-d H:i:s');

// 必要なバリデーションや処理を行う
$target_name_error       = validate_target_name($name);

if ($target_name_error) {
    $_SESSION['errors'] = [
        'name'       => $target_name_error,
    ];
    $_SESSION['old_input'] = $_POST;
    $_SESSION['message_error'] = '登録に失敗しました';

    if (!empty($id)) {
        header('Location: /custom/admin/app/Views/master/target/upsert.php?id=' . urlencode($id));
    } else {
        header('Location: /custom/admin/app/Views/master/target/upsert.php');
    }
    exit;
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message_error'] = '登録に失敗しました';
            header('Location: /custom/admin/app/Views/master/target/index.php');
            exit;
        }
    }

    try {
        if (!empty($id)) {
            $count = $DB->count_records_select('target', "name = ? AND is_delete = 0 AND id <> ?", [$name, $id]);
        } else {
            $count = $DB->count_records_select('target', "name = ? AND is_delete = 0", [$name]);
        }

        if ($count > 0) {
            $_SESSION['message_error'] = '登録に失敗しました';
            $_SESSION['errors'] = ['name' => '同じ名前の対象が存在します'];
            $_SESSION['old_input'] = $_POST;
            if (!empty($id)) {
                header('Location: /custom/admin/app/Views/master/target/upsert.php?id=' . urlencode($id));
            } else {
                header('Location: /custom/admin/app/Views/master/target/upsert.php');
            }
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/admin/app/Views/master/target/index.php');
        exit;
    }

    $data = new stdClass();
    $data->name       = $name;
    $data->created_at = $createdAt;
    $data->updated_at = $updatedAt;

    if ($id) {
        $data->id = $id;
    }

    try {
        $transaction = $DB->start_delegated_transaction();
        if ($id) {
            $DB->update_record('target', $data);
        } else {
            $DB->insert_record('target', $data);
        }
        $transaction->allow_commit();
        $_SESSION['message_success'] = '登録が完了しました';
        header('Location: /custom/admin/app/Views/master/target/index.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/admin/app/Views/master/target/index.php');
        exit;
    }
}
