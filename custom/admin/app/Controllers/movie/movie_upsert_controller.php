<?php
header('Content-Type: application/json');
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');

global $DB;

// CSRFチェック
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['message_error'] = '登録に失敗しました。';
    echo json_encode(['status' => 'error']);
    exit;
}

$upload_dir = '/var/www/html/moodle/uploads/movie/';
$file_name = $_POST['file_name'];
$chunk_index = $_POST['chunk_index'];
$total_chunks = $_POST['total_chunks'];

$tmpFilePath = $_FILES['file']['tmp_name'];
$targetPath = $upload_dir . $file_name . '.part' . $chunk_index;

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// チャンクを保存
move_uploaded_file($tmpFilePath, $targetPath);

// すべてのチャンクがアップロードされたら結合
if ($chunk_index == $total_chunks - 1) {
    $finalFilePath = $upload_dir . $file_name;
    $outputFile = fopen($finalFilePath, 'wb');

    // チャンクの結合
    for ($i = 0; $i < $total_chunks; $i++) {
        $chunk_file = $upload_dir . $file_name . '.part' . $i;
        fwrite($outputFile, file_get_contents($chunk_file));
        unlink($chunk_file); // チャンク削除
    }

    fclose($outputFile);

    $id = $_POST['id'] ?? null;
    $course_info_id = (int)$_POST['course_info_id'] ?? null;

    try {
        $transaction = $DB->start_delegated_transaction();

        $data = new stdClass();
        $data->file_name  = $file_name;
        $data->course_info_id = $course_info_id;
        $data->updated_at = date('Y-m-d H:i:s');

        if (!empty($id)) {
            $data->id = $id;
            $DB->update_record('course_movie', $data);
        } else {
            $data->created_at = date('Y-m-d H:i:s');
            $DB->insert_record('course_movie', $data);
        }
        $transaction->allow_commit();
        $_SESSION['message_success'] = '登録が完了しました';
        $response = ['status' => 'success'];
        echo json_encode($response);
        exit;
    } catch (Exception $e) {
        try {
            $transaction->rollback($e);
        } catch (Exception $rollbackException) {
            $_SESSION['message_error'] = '登録に失敗しました。';
            $unlink_file = $upload_dir . $file_name;
            if (isset($unlink_file) && file_exists($unlink_file)) {
                unlink($unlink_file);
            }
            echo json_encode(['status' => 'error', 'message' => $unlink_file]);
            exit;
        }
    }
} else {
    // チャンクがアップロード中の段階では何も返さない
    echo json_encode(['status' => 'success']);
    exit;
}
