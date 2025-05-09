<?php
header('Content-Type: application/json');
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');

use Aws\S3\S3Client;

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

$s3 = new S3Client([
    'version' => 'latest',
    'region'  => 'ap-northeast-1',
    'credentials' => [
        'key'    => $_ENV['S3_AWS_ACCESS_KEY_ID'],
        'secret' => $_ENV['S3_AWS_SECRET_ACCESS_KEY_ID'],
    ]
]);

$bucket = 'osakauniv-movie-uploads';

$file_name = basename($_POST['file_name']);
$chunk_index = $_POST['chunk_index'];
$total_chunks = $_POST['total_chunks'];
$total_file_size = $_POST['total_file_size'] ?? 0;
$tmpFilePath = $_FILES['file']['tmp_name'];

$key_prefix = "videos/{$course_info_id}/{$course_no}/";
$chunk_key = $key_prefix . $file_name . '.part' . $chunk_index;

try {
    $s3->putObject([
        'Bucket' => $bucket,
        'Key' => $chunk_key,
        'SourceFile' => $tmpFilePath,
        'ACL' => 'private',
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => '登録に失敗しました']);
    exit;
}

// すべてのチャンクがアップロードされたら結合
if ($chunk_index == $total_chunks - 1) {
    $final_data = '';

    // 各チャンクをS3から読み込み
    for ($i = 0; $i < $total_chunks; $i++) {
        $part_key = $key_prefix . $file_name . '.part' . $i;
        try {
            $result = $s3->getObject([
                'Bucket' => $bucket,
                'Key'    => $part_key
            ]);
            // バイナリとして結合
            $final_data .= $result['Body'];
            // チャンクを削除
            $s3->deleteObject([
                'Bucket' => $bucket,
                'Key'    => $part_key
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => '登録に失敗しました']);
            exit;
        }
    }

    // 完成した動画ファイルを S3 に保存
    try {
        $s3->putObject([
            'Bucket'      => $bucket,
            'Key'         => $key_prefix . $file_name,
            'Body'        => $final_data,
            'ACL'         => 'private',
            'ContentType' => 'video/mp4',
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => '登録に失敗しました']);
        exit;
    }

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
