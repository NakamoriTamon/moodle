<?php
header('Content-Type: application/json');
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');

global $DB;

// CSRFチェック
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['message_error'] = '登録に失敗しました。';
}

// 情報チェック
$course_info_id = (int)$_POST['course_info_id'] ?? null;
$course_no = (int)$_POST['course_no'] ?? null;

if (empty($course_info_id) || empty($course_no)) {
    $_SESSION['message_error'] = 'コース情報が不足しています';
}

if (!empty($_SESSION['message_error'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$upload_dir = '/var/www/html/moodle/uploads/movie/' . $course_info_id . '/' . $course_no . '/';
$file_name = $_POST['file_name'];
$chunk_index = $_POST['chunk_index'];
$total_chunks = $_POST['total_chunks'];

// チャンクファイルの保存
$tmpFilePath = $_FILES['file']['tmp_name'];
$targetPath = $upload_dir . $file_name . '.part' . $chunk_index;

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// チェックするディレクトリ
$storage_upload_dir = '/var/www/html/moodle/uploads/movie/';
$total_file_size = $_POST['total_file_size'] ?? 0;
if (!check_storage_limit($storage_upload_dir, $total_file_size)) {
    $_SESSION['message_error'] = 'ストレージ容量が不足しています';
    echo json_encode(['status' => 'error']);
    exit;
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

    // DBへの登録処理
    $data = new stdClass();
    $data->file_name  = $file_name;
    $data->course_info_id = $course_info_id;
    $data->updated_at = date('Y-m-d H:i:s');

    try {
        $transaction = $DB->start_delegated_transaction();
        $id = (int)$_POST['id'] ?? null;

        if (!empty($id)) {
            $data->id = $id;
            $DB->update_record('course_movie', $data);
        } else {
            $data->created_at = date('Y-m-d H:i:s');
            $DB->insert_record('course_movie', $data);
        }

        // 成功時に同ディレクトリ内のすべての動画を削除する
        if (is_dir($upload_dir)) {
            $files = scandir($upload_dir);

            foreach ($files as $file) {
                $file_path = $upload_dir . $file;

                // 「.」「..」はスキップ & AAA.mp4 は削除しない
                if ($file === '.' || $file === '..' || $file === $file_name) {
                    continue;
                }

                // ファイルなら削除
                if (is_file($file_path)) {
                    unlink($file_path);
                }
            }
        }

        $transaction->allow_commit();
        $_SESSION['message_success'] = '登録が完了しました';
        echo json_encode(['status' => 'success']);
        exit;
    } catch (Exception $e) {
        $transaction->rollback($e);
        echo json_encode(['status' => 'error', 'message' => '登録に失敗しました']);
        exit;
    }
} else {
    // チャンクがアップロード中の段階では何も返さない
    echo json_encode(['status' => 'success']);
    exit;
}

function check_storage_limit($upload_dir, $file_size, $max_usage_ratio = 0.9)
{
    // 総ストレージ容量
    $total_space = disk_total_space($upload_dir);
    // 現在の空き容量
    $free_space = disk_free_space($upload_dir);
    // 現在の使用済み容量
    $used_space = $total_space - $free_space;
    // 現在の使用率
    $usage_ratio = $used_space / $total_space;

    // アップロード後の使用率を計算
    $new_usage_ratio = ($used_space + $file_size) / $total_space;

    // ログ出力（デバッグ用）
    error_log("Total Space: {$total_space} bytes");
    error_log("Used Space: {$used_space} bytes");
    error_log("Free Space: {$free_space} bytes");
    error_log("Current Usage: " . ($usage_ratio * 100) . "%");
    error_log("New Usage After Upload: " . ($new_usage_ratio * 100) . "%");

    // 9割を超えたらエラー
    if ($new_usage_ratio >= $max_usage_ratio) {
        return false;
    }
    return true;
}
