<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

// セッション開始
session_start();

// 接続情報取得
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
    $_SESSION['message_error'] = '登録に失敗しましたtt';
    header('Location: /custom/admin/app/Views/event/material.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = '登録に失敗しました33';
        header('Location: /custom/admin/app/Views/event/material.php');
        exit;
    }
}

try {
    $transaction = $DB->start_delegated_transaction();
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
                $_SESSION['message_error'] = '同じ名前の資料が既に存在します';
                header('Location: /custom/admin/app/Views/event/material.php');
                exit;
            }

            $ext         = pathinfo($original_name, PATHINFO_EXTENSION);
            $newfilename = uniqid('material_') . '.' . $ext;
            $destination = $destination_dir . '/' . $newfilename;
            $dbstorepath = 'uploads/material';

            if (!move_uploaded_file($tmp_name, $destination)) {
                $_SESSION['message_error'] = '登録に失敗しましたaa';
                header('Location: /custom/admin/app/Views/event/material.php');
                exit;
            }

            $data = new stdClass();
            $data->file_name  = $original_name;
            $data->file_path  = $newfilename;
            $data->created_at = $createdAt;
            $data->updated_at = $updatedAt;

            if ($idOfThisCourse > 0 && $i == 0) {
                $data->id = $idOfThisCourse;
            }

            if ($idOfThisCourse > 0 && $i == 0) {
                $DB->update_record('course_material', $data);
            } else {
                $DB->insert_record('course_material', $data);
            }
        }
    }

    $transaction->allow_commit();
    $_SESSION['message_success'] = '登録が完了しました';
    header('Location: /custom/admin/app/Views/event/material.php');
    exit;
} catch (Exception $e) {
    $_SESSION['message_error'] = '登録に失敗しましたwww' . $e->getMessage();
    header('Location: /custom/admin/app/Views/event/material.php');
    exit;
}
