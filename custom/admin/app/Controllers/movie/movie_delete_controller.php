<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

// 接続情報取得
global $DB;

// POSTデータの取得 (バリデーションは別途行う)
$id = $_POST['id'] ?? '';
$course_info_id = $_POST['course_info_id'] ?? '';
$course_no = $_POST['course_no'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = '削除に失敗しました';
        header('Location: /custom/admin/app/Views/event/movie.php');
        exit;
    }
}

try {
    $transaction = $DB->start_delegated_transaction();
    $movie = $DB->get_record('course_movie', ['id' => $id]);
    $DB->delete_records('course_movie', ['id' => $id]);

    if ($movie && !empty($movie->file_name)) {
        $file_path = '/var/www/html/moodle/uploads/movie/' . $course_info_id . '/' . $course_no . '/' . $movie->file_name;
        if (file_exists($file_path)) {
            unlink($file_path);
        } else {
            throw new Exception('ファイルの削除に失敗しました。');
        }
    }

    $transaction->allow_commit();
    $_SESSION['message_success'] = '削除が完了しました';
    header('Location: /custom/admin/app/Views/event/movie.php');
    exit;
} catch (Exception $e) {
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/admin/app/Views/event/movie.php');
        exit;
    }
}
