<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

session_start();

// 接続情報取得
$baseModel = new BaseModel();
$pdo = $baseModel->getPdo();

// POSTデータの取得 (バリデーションは別途行う)
$id         = $_POST['id'] ?? '';
$name       = trim($_POST['name'] ?? '');
$imagefile  = $_FILES['imagefile'] ?? null;
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
        'imagefile'  => $image_error,
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
            $_SESSION['message_error'] = 'トークンが不正です。';
            header('Location: /custom/admin/app/Views/master/category/index.php');
            exit;
        }
    }

    if (isset($_FILES['imagefile']) && $_FILES['imagefile']['error'] === UPLOAD_ERR_OK) {
        $tmp_name       = $_FILES['imagefile']['tmp_name'];
        $original_name  = $_FILES['imagefile']['name'];
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
            $_SESSION['message_error'] = '画像アップロードに失敗しました。';
            header('Location: /custom/admin/app/Views/master/category/index.php');
            exit;
        }
    }

    if (!empty($id)) {
        $sql = "UPDATE mdl_category
                SET
                  name       = ?,
                  path       = ?,
                  created_at = ?,
                  updated_at = ?
                WHERE id = ?";
        $params = [$name, $path, $createdAt, $updatedAt, $id];
    } else {
        $sql = "INSERT INTO mdl_category
                  (name, path, created_at, updated_at)
                VALUES
                  (?, ?, ?, ?)";
        $params = [$name, $path, $createdAt, $updatedAt];
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $pdo->commit();
        $_SESSION['message_success'] = '登録が完了しました';
        header('Location: /custom/admin/app/Views/master/category/index.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
        header('Location: /custom/admin/app/Views/master/category/index.php');
        exit;
    }
}
