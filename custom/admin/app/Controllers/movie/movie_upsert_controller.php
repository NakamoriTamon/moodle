<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

session_start();

global $DB;
$_SESSION['old_input'] = $_POST;

$ids        = $_POST['ids']       ?? [];
$files      = $_FILES['video_files'] ?? null;
$createdAt  = date('Y-m-d H:i:s');
$updatedAt  = date('Y-m-d H:i:s');

// 講義動画のアップロードはここだけなのでここでバリデーション
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['file'];
    $fileTmpPath = $file['tmp_name'];
    $fileMimeType = mime_content_type($fileTmpPath); // MIMEタイプ取得

    if (strpos($fileMimeType, 'video/') !== 0) {
        $_SESSION['errors']['file'] = '許可されていない形式です。動画ファイルをアップロードしてください。';
        header('Location: /custom/admin/app/Views/event/movie.php');
    }
} else {
    $_SESSION['errors']['file'] = '講義動画は必須です。';
}

if ($_SESSION['errors']['file']) {
    $_SESSION['message_error'] = '登録に失敗しました';
    header('Location: /custom/admin/app/Views/event/movie.php');
    exit;
}

if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['message_error'] = '登録に失敗しました';
    header('Location: /custom/admin/app/Views/event/movie.php');
    exit;
}

try {
    $transaction = $DB->start_delegated_transaction();
    $destination_dir = '/var/www/html/moodle/uploads/movie';
    if (!file_exists($destination_dir)) {
        mkdir($destination_dir, 0755, true);
    }

    foreach ($files['name'] as $movieIndex => $fileNames) {
        $idOfThisVideo = isset($ids[$movieIndex]) ? (int)$ids[$movieIndex] : 0;

        foreach ($fileNames as $i => $original_name) {
            $errCode  = $files['error'][$movieIndex][$i] ?? UPLOAD_ERR_NO_FILE;
            $tmp_name = $files['tmp_name'][$movieIndex][$i] ?? '';

            if ($errCode === UPLOAD_ERR_NO_FILE || empty($original_name)) {
                continue;
            }

            $exists = $DB->record_exists_sql("SELECT 1 FROM {course_movie} WHERE file_name = ? AND is_delete = ?", [$original_name, 0]);

            if ($exists) {
                $_SESSION['message_error'] = '同じ名前の動画が既に存在します';
                header('Location: /custom/admin/app/Views/event/movie.php');
                exit;
            }

            $ext         = pathinfo($original_name, PATHINFO_EXTENSION);
            $newfilename = uniqid('video_') . '.' . $ext;
            $destination = $destination_dir . '/' . $newfilename;
            $dbstorepath = 'uploads/video';

            if (!move_uploaded_file($tmp_name, $destination)) {
                $_SESSION['message_error'] = '登録に失敗しました';
                header('Location: /custom/admin/app/Views/event/movie.php');
                exit;
            }

            $data = new stdClass();
            $data->file_name  = $original_name;
            $data->file_path  = $newfilename;
            $data->created_at = $createdAt;
            $data->updated_at = $updatedAt;

            if ($idOfThisVideo > 0 && $i == 0) {
                $data->id = $idOfThisVideo;
            }

            if ($idOfThisVideo > 0 && $i == 0) {
                $DB->update_record('course_movie', $data);
            } else {
                $DB->insert_record('course_movie', $data);
            }
        }
    }
    $transaction->allow_commit();
    $_SESSION['message_success'] = '登録が完了しました';
    header('Location: /custom/admin/app/Views/event/movie.php');
    exit;
} catch (Exception $e) {
    $_SESSION['message_error'] = '登録に失敗しました';
    header('Location: /custom/admin/app/Views/event/movie.php');
    exit;
}
