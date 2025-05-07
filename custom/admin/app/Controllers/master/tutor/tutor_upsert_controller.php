<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

// 接続情報取得
global $DB;

// POSTデータの取得 (バリデーションは別途行う)
$id = $_POST['id'] ?? null;
$name = trim($_POST['name'] ?? '');
$image_file = $_FILES['path'] ?? null;
$path = $_POST['path'] ?? '';
$existing_path = $_POST['existing_path'] ?? '';
$overview = trim($_POST['overview'] ?? '');

// バリデーション処理
$name_error = validate_text($name, '講師名', 50, true);
$overview_error = validate_textarea($overview, '講師概要', true);
$errors = [$name_error, $image_error, $overview_error];

if (array_filter($errors)) {
    $_SESSION['errors'] = [
        'name'       => $name_error,
        'imagefile'  => $image_error,
        'overview'   => $overview_error
    ];
    $_SESSION['old_input'] = $_POST;
    $_SESSION['message_error'] = '登録に失敗しました';

    if (!empty($id)) {
        header('Location: /custom/admin/app/Views/master/tutor/upsert.php?id=' . urlencode($id));
    } else {
        header('Location: /custom/admin/app/Views/master/tutor/upsert.php');
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/admin/app/Views/master/tutor/index.php');
        exit;
    }
}

// 講師画像登録
if (isset($image_file) && $image_file['error'] === UPLOAD_ERR_OK) {
    $new_file_name    = uniqid('tutor_') . '.' . pathinfo($image_file['name'], PATHINFO_EXTENSION);
    $destination_dir = '/var/www/html/moodle/uploads/tutor';
    if (!file_exists($destination_dir)) {
        mkdir($destination_dir, 0777, true);
    }
    $destination = $destination_dir . '/' . $new_file_name;

    if (move_uploaded_file($image_file['tmp_name'], $destination)) {
        $path = $new_file_name;
    } else {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/admin/app/Views/master/tutor/index.php');
        exit;
    }
}

$data = new stdClass();
$data->name       = $name;
$data->overview   = $overview;
$data->created_at = date('Y-m-d H:i:s');
$data->updated_at = date('Y-m-d H:i:s');

// 既に画像が登録されている際は上書きをしない
if (empty($existing_path)) {
    $data->path = $path;
}

try {
    $transaction = $DB->start_delegated_transaction();
    if ($id) {
        $data->id = $id;
        $DB->update_record_raw('tutor', $data);
    } else {
        $DB->insert_record_raw('tutor', $data);
    }
    // 今後ユーザー側で操作できるように使用変更するのであれば、変更前の画像を削除する処理も必要となる
    $transaction->allow_commit();
    $_SESSION['message_success'] = '登録が完了しました';
    header('Location: /custom/admin/app/Views/master/tutor/index.php');
    exit;
} catch (Exception $e) {
    $_SESSION['message_error'] = '登録に失敗しました';
    header('Location: /custom/admin/app/Views/master/tutor/index.php');
    exit;
}
