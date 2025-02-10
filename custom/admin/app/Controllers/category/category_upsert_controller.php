<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');

// 接続情報取得
global $DB;

// POSTデータの取得 (バリデーションは別途行う)
$id         = $_POST['id'] ?? '';
$name       = trim($_POST['name'] ?? '');
$imagefile  = $_FILES['image_file'] ?? null;
$is_deleted = $_POST['is_deleted'] ?? null;

// 必要なバリデーションや処理を行う
$category = $DB->get_record('category', ['id' => $id]);
$existing_path = $DB->get_record('category', ['id' => $id])->path;

// 登録時または画像ファイル入れ替え時チェック
if (empty($id) || (!empty($id) && $is_deleted) || $imagefile['size'] > 0) {
    $image_error = validate_image($imagefile);
}
$category_name_error = validate_category_name($name);

// カテゴリ名重複チェック
$where = !empty($id) ? "name = ? AND is_delete = 0 AND id <> ?" : "name = ? AND is_delete = 0";
$params = !empty($id) ? [$name, $id] : [$name];
if ($DB->count_records_select('category', $where, $params) > 0) {
    $_SESSION['message_error'] = '登録に失敗しました';
    $_SESSION['errors'] = ['name' => '既に登録されています'];
}

if ($category_name_error || $image_error) {
    $_SESSION['errors'] = [
        'name'       => $category_name_error,
        'image_file'  => $image_error,
    ];
    $_SESSION['message_error'] = '登録に失敗しました';
}

if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['message_error'] = '登録に失敗しました';
}

if ($_SESSION['message_error']) {
    $_SESSION['old_input'] = $_POST;
    $id_param = !empty($id) ? '?id=' . urlencode($id) : '';
    header('Location: /custom/admin/app/Views/master/category/upsert.php' . $id_param);
    exit;
}

if ($imagefile['error'] === UPLOAD_ERR_OK) {
    $tmp_name       = $_FILES['image_file']['tmp_name'];
    $original_name  = $_FILES['image_file']['name'];
    $ext            = pathinfo($original_name, PATHINFO_EXTENSION);
    $newfilename    = uniqid('category_') . '.' . $ext;

    $destination_dir = '/var/www/html/moodle/uploads/category';
    if (!file_exists($destination_dir)) {
        mkdir($destination_dir, 0755, true);
    }
    $destination = $destination_dir . '/' . $newfilename;

    if (move_uploaded_file($tmp_name, $destination)) {
        $path = $newfilename;
    } else {
        $_SESSION['message_error'] = '登録に失敗しました';
        $_SESSION['old_input'] = $_POST;
        $id_param = !empty($id) ? '?id=' . urlencode($id) : '';
        header('Location: /custom/admin/app/Views/master/category/upsert.php' . $id_param);
        exit;
    }
}

try {
    $transaction = $DB->start_delegated_transaction();

    $data = new stdClass();
    $data->name       = $name;
    $data->path       = isset($path) ? $path : $existing_path;
    $data->created_at = date('Y-m-d H:i:s');
    $data->updated_at = date('Y-m-d H:i:s');

    if ($id) {
        $data->id = $id;
        $DB->update_record('category', $data);
    } else {
        $DB->insert_record('category', $data);
    }
    $transaction->allow_commit();

    // 編集時は画像が変更された場合は元画像を削除する
    if (!empty($id) && $imagefile['size'] > 0) {
        $filePath = '/var/www/html/moodle/uploads/category/' . $existing_path;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    $_SESSION['message_success'] = '登録が完了しました';
    header('Location: /custom/admin/app/Views/master/category/index.php');
    exit;
} catch (Exception $e) {
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        // アップロードしたファイルを削除する
        if ($destination && file_exists($destination)) {
            unlink($destination);
        }
        $_SESSION['message_error'] = '登録に失敗しました';
        $id_param = !empty($id) ? '?id=' . urlencode($id) : '';
        header('Location: /custom/admin/app/Views/master/category/upsert.php' . $id_param);
        exit;
    }
}
