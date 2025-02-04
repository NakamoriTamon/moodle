<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

session_start();

// 接続情報取得
global $DB;

// POSTデータの取得 (バリデーションは別途行う)
$id         = $_POST['id'] ?? '';
$name       = trim($_POST['name'] ?? '');
$imagefile  = $_FILES['image_file'] ?? null;
$path       = $_POST['existing_image'] ?? '';
$createdAt  = date('Y-m-d H:i:s');
$updatedAt  = date('Y-m-d H:i:s');

// 必要なバリデーションや処理を行う
$category_name_error       = validate_category_name($name);
$user_removed_image = (!empty($id) && empty($_POST['existing_image']));
$require_image = (empty($id) || $user_removed_image);
$has_new_file = ($imagefile && $imagefile['error'] !== UPLOAD_ERR_NO_FILE);

if ($require_image || $has_new_file) {
    $image_error = validate_image($imagefile);
}

if ($category_name_error || $image_error) {
    $_SESSION['errors'] = [
        'name'       => $category_name_error,
        'image_file'  => $image_error,
    ];
    $_SESSION['old_input'] = $_POST;
    $_SESSION['message_error'] = '登録に失敗しました';

    if (!empty($id)) {
        header('Location: /custom/admin/app/Views/master/category/upsert.php?id=' . urlencode($id));
    } else {
        header('Location: /custom/admin/app/Views/master/category/upsert.php');
    }
    exit;
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message_error'] = '登録に失敗しました';
            header('Location: /custom/admin/app/Views/master/category/index.php');
            exit;
        }
    }

    try {
        if (!empty($id)) {
            $count = $DB->count_records_select('category', "name = ? AND is_delete = 0 AND id <> ?", [$name, $id]);
        } else {
            $count = $DB->count_records_select('category', "name = ? AND is_delete = 0", [$name]);
        }

        if ($count > 0) {
            $_SESSION['message_error'] = '登録に失敗しました';
            $_SESSION['errors'] = ['name' => '同じ名前のカテゴリが存在します'];
            $_SESSION['old_input'] = $_POST;
            if (!empty($id)) {
                header('Location: /custom/admin/app/Views/master/category/upsert.php?id=' . urlencode($id));
            } else {
                header('Location: /custom/admin/app/Views/master/category/upsert.php');
            }
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/admin/app/Views/master/category/index.php');
        exit;
    }

    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $tmp_name       = $_FILES['image_file']['tmp_name'];
        $original_name  = $_FILES['image_file']['name'];
        $ext            = pathinfo($original_name, PATHINFO_EXTENSION);
        $newfilename    = uniqid('category_') . '.' . $ext;

        $destination_dir = '/var/www/html/moodle/uploads/category';
        if (!file_exists($destination_dir)) {
            mkdir($destination_dir, 0777, true);
        }
        $destination = $destination_dir . '/' . $newfilename;

        if (move_uploaded_file($tmp_name, $destination)) {
            $path = $newfilename;
        } else {
            $_SESSION['message_error'] = '登録に失敗しました';
            header('Location: /custom/admin/app/Views/master/category/index.php');
            exit;
        }
    }

    $data = new stdClass();
    $data->name       = $name;
    $data->path       = $path;
    $data->created_at = $createdAt;
    $data->updated_at = $updatedAt;

    if ($id) {
        $data->id = $id;
    }

    try {
        $transaction = $DB->start_delegated_transaction();
        if ($id) {
            $DB->update_record('category', $data);
        } else {
            $DB->insert_record('category', $data);
        }
        $transaction->allow_commit();
        $_SESSION['message_success'] = '登録が完了しました';
        header('Location: /custom/admin/app/Views/master/category/index.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/admin/app/Views/master/category/index.php');
        exit;
    }
}
