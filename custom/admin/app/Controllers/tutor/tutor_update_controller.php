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
$email      = trim($_POST['email'] ?? '');
$imagefile  = $_FILES['imagefile'] ?? null;
$path       = $_POST['existing_image'] ?? '';
$overview   = trim($_POST['overview'] ?? '');
$createdAt  = date('Y-m-d H:i:s');
$updatedAt  = date('Y-m-d H:i:s');

// 必要なバリデーションや処理を行う
$tutor_name_error       = validate_tutor_name($name);
$overview_error         = validate_tutor_overview($overview);
$user_removed_image     = (!empty($id) && empty($_POST['existing_image']));
$require_image          = (empty($id) || $user_removed_image);
$has_new_file           = ($imagefile && $imagefile['error'] !== UPLOAD_ERR_NO_FILE);
$email_validate_error   = validate_custom_email($email);
$email_duplicate_error = is_email_duplicate($DB, $email, $id);
$email_error = trim(($email_duplicate_error ?? '') . ' ' . ($email_validate_error ?? '')) ?: null;

if ($require_image || $has_new_file) {
    $image_error = validate_image($imagefile);
} else {
    $image_error = null;
}

if ($email_error || $tutor_name_error || $image_error || $overview_error) {
    $_SESSION['errors'] = [
        'name'       => $tutor_name_error,
        'imagefile'  => $image_error,
        'overview'   => $overview_error,
        'email'      => $email_error,
    ];
    $_SESSION['old_input'] = $_POST;
    $_SESSION['message_error'] = '登録に失敗しました';

    if (!empty($id)) {
        header('Location: /custom/admin/app/Views/master/tutor/upsert.php?id=' . urlencode($id));
    } else {
        header('Location: /custom/admin/app/Views/master/tutor/upsert.php');
    }
    exit;
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message_error'] = '登録に失敗しました';
            header('Location: /custom/admin/app/Views/master/tutor/index.php');
            exit;
        }
    }

    if (isset($_FILES['imagefile']) && $_FILES['imagefile']['error'] === UPLOAD_ERR_OK) {
        $tmp_name       = $_FILES['imagefile']['tmp_name'];
        $original_name  = $_FILES['imagefile']['name'];
        $ext            = pathinfo($original_name, PATHINFO_EXTENSION);
        $newfilename    = uniqid('tutor_') . '.' . $ext;

        $destination_dir = '/var/www/html/moodle/uploads/tutor';
        if (!file_exists($destination_dir)) {
            mkdir($destination_dir, 0777, true);
        }
        $destination = $destination_dir . '/' . $newfilename;

        if (move_uploaded_file($tmp_name, $destination)) {
            $path = $newfilename;
        } else {
            $_SESSION['message_error'] = '登録に失敗しました';
            header('Location: /custom/admin/app/Views/master/tutor/index.php');
            exit;
        }
    }

    $data = new stdClass();
    $data->name       = $name;
    $data->email      = $email;
    $data->path       = $path;
    $data->overview   = $overview;
    $data->created_at = $createdAt;
    $data->updated_at = $updatedAt;

    if ($id) {
        $data->id = $id;
    }

    try {
        $transaction = $DB->start_delegated_transaction();
        if ($id) {
            $DB->update_record('tutor', $data);
        } else {
            $DB->insert_record('tutor', $data);
        }
        $transaction->allow_commit();
        $_SESSION['message_success'] = '登録が完了しました';
        header('Location: /custom/admin/app/Views/master/tutor/index.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/admin/app/Views/master/tutor/index.php');
        exit;
    }
}

function is_email_duplicate($DB, $email, $current_id = null)
{
    if (!empty($current_id)) {
        $sql = "SELECT id FROM {tutor} WHERE email = :email AND id != :id AND is_delete = 0";
        $existing = $DB->get_records_sql($sql, ['email' => $email, 'id' => $current_id]);
    } else {
        $sql = "SELECT id FROM {tutor} WHERE email = :email AND is_delete = 0";
        $existing = $DB->get_records_sql($sql, ['email' => $email]);
    }
    if ($existing) {
        return 'メールアドレスが重複しています。';
    }
    return false;
}
