<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

session_start();

$baseModel = new BaseModel();
$pdo = $baseModel->getPdo();
global $DB;

$ids        = $_POST['ids']       ?? [];
$files      = $_FILES['pdf_files'] ?? null;
$fileData   = $_POST['fileData']   ?? null;
$createdAt  = date('Y-m-d H:i:s');
$updatedAt  = date('Y-m-d H:i:s');

// バリデーション
$validate_material_file = validate_material_file($files);
if ($validate_material_file) {
    $_SESSION['errors'] = [
        'pdf_files' => $validate_material_file,
    ];
    $_SESSION['old_input'] = $_POST;
    $_SESSION['message_error'] = '登録に失敗しました: ' . $validate_material_file;
    header('Location: /custom/admin/app/Views/event/material.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = 'トークンが不正です。';
        header('Location: /custom/admin/app/Views/event/material.php');
        exit;
    }
}

try {
    $pdo->beginTransaction();
    $destination_dir = '/var/www/html/moodle/uploads/material';
    if (!file_exists($destination_dir)) {
        mkdir($destination_dir, 0777, true);
    }

    foreach ($files['name'] as $courseIndex => $fileNames) {
        $idOfThisCourse = isset($ids[$courseIndex]) ? (int)$ids[$courseIndex] : 0;

        foreach ($fileNames as $i => $original_name) {
            $errCode  = $files['error'][$courseIndex][$i] ?? UPLOAD_ERR_NO_FILE;
            $tmp_name = $files['tmp_name'][$courseIndex][$i] ?? '';

            if ($errCode === UPLOAD_ERR_NO_FILE || empty($original_name)) {
                continue;
            }

            $exists = $DB->record_exists_sql("SELECT 1 FROM {course_material} WHERE file_name = ? AND is_delete = ?", [$original_name, 0]);

            if ($exists) {
                $pdo->rollBack();
                $_SESSION['message_error'] = '同じファイルがあります: ' . $original_name;
                header('Location: /custom/admin/app/Views/event/material.php');
                exit;
            }

            $ext         = pathinfo($original_name, PATHINFO_EXTENSION);
            $newfilename = uniqid('material_') . '.' . $ext;
            $destination = rtrim($destination_dir, '/') . '/' . $newfilename;
            $dbstorepath = 'uploads/material';
            $dbpath      = $dbstorepath . '/' . $newfilename;

            if (!move_uploaded_file($tmp_name, $destination)) {
                $pdo->rollBack();
                $_SESSION['message_error'] = 'ファイルアップロードに失敗しました。';
                header('Location: /custom/admin/app/Views/event/material.php');
                exit;
            }

            if ($idOfThisCourse > 0 && $i == 0) {
                $sql = "UPDATE mdl_course_material
               SET file_name = ?, 
                   file_path = ?, 
                   created_at = ?, 
                   updated_at = ?
             WHERE id = ?";
                $params = [$original_name, $dbpath, $createdAt, $updatedAt, $idOfThisCourse];
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            } else {
                $sql = "INSERT INTO mdl_course_material
                (file_name, file_path, created_at, updated_at)
            VALUES
                (?, ?, ?, ?)";
                $params = [$original_name, $dbpath, $createdAt, $updatedAt];
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }
        }
    }

    $pdo->commit();
    $_SESSION['message_success'] = '登録が完了しました';
    header('Location: /custom/admin/app/Views/event/material.php');
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
    header('Location: /custom/admin/app/Views/event/material.php');
    exit;
}
