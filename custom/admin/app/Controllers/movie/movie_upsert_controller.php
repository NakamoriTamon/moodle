<?php
header('Content-Type: application/json');
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');

use Aws\S3\S3Client;

global $DB;

// AWS SDK でS3操作用の接続ハンドラ作成
$s3 = new S3Client([
    'version' => 'latest',
    'region'  => 'ap-northeast-1',
    'credentials' => [
        'key'    => $_ENV['S3_AWS_ACCESS_KEY_ID'],
        'secret' => $_ENV['S3_AWS_SECRET_ACCESS_KEY_ID'],
    ]
]);

$bucket = 'osakauniv-movie-uploads';
$mode = $_POST['mode'] ?? null;
$course_info_id = $_POST['course_info_id'] ?? null;
$course_no = $_POST['course_no'] ?? null;

// デフォルト設定
$_SESSION['message_error'] = '登録に失敗しました';

switch ($mode) {
    case 'init':
        $file_name = $_POST['file_name'] ?? '';
        if (empty($file_name) || empty($course_info_id) || empty($course_no)) {
            echo json_encode(['status' => 'error', 'message' => '必要な情報が不足しています']);
            exit;
        }

        $key = "videos/{$course_info_id}/{$course_no}/" . basename($file_name);

        try {
            // マルチパートアップロードのための初期化処理
            $result = $s3->createMultipartUpload([
                'Bucket' => $bucket,
                'Key'    => $key,
                'ACL'    => 'private',
                'ContentType' => 'video/mp4'
            ]);

            echo json_encode([
                'uploadId' => $result['UploadId'], // マルチパートアップロード識別ID 
                'key' => $key
            ]);
        } catch (Throwable $e) {
            error_log('createMultipartUploadエラー: ' . $e->getMessage() . $course_info_id);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'presign':
        $uploadId = $_POST['uploadId'] ?? '';
        $key = $_POST['key'] ?? '';
        $partNumber = (int)$_POST['partNumber'];

        try {
            // S3 API リクエスト構築
            $cmd = $s3->getCommand('UploadPart', [
                'Bucket' => $bucket,
                'Key' => $key,
                'UploadId' => $uploadId,
                'PartNumber' => $partNumber
            ]);
            header('Content-Type: application/json');
            $url = (string)$s3->createPresignedRequest($cmd, '+1 hour')->getUri(); // 署名付きURL作成
            echo json_encode(['url' => $url]);
        } catch (Throwable $e) {
            error_log('動画アップロードエラー: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'complete':
        // CSRF & 情報チェック
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            echo json_encode(['status' => 'error', 'message' => 'CSRFトークンが無効です']);
            exit;
        }

        $course_info_id = (int)($_POST['course_info_id'] ?? 0);
        $course_no = (int)($_POST['course_no'] ?? 0);
        $file_name = $_POST['file_name'] ?? '';
        if (empty($course_info_id) || empty($course_no)) {
            error_log('動画アップロードエラー: ' . 'コース情報が不足しています');
            echo json_encode(['status' => 'error', 'message' => 'コース情報が不足しています']);
            exit;
        }

        $uploadId = $_POST['uploadId'] ?? '';
        $key = $_POST['key'] ?? '';
        $parts = json_decode($_POST['parts'] ?? '[]', true);

        if (!$uploadId || !$key || empty($parts)) {
            error_log('動画アップロードエラー: ' . 'パラメータが不足しています');
            echo json_encode(['status' => 'error', 'message' => 'パラメータが不足しています']);
            exit;
        }

        try {
            // すべてのチャンクを1つのファイルとして結合
            $s3->completeMultipartUpload([
                'Bucket' => $bucket,
                'Key' => $key,
                'UploadId' => $uploadId,
                'MultipartUpload' => [
                    'Parts' => $parts
                ]
            ]);

            $file_name = "videos/{$course_info_id}/{$course_no}/" . basename($file_name) . '.m3u8';

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

                $transaction->allow_commit();
                $_SESSION['message_success'] = '登録が完了しました';
                unset($_SESSION['message_error']);
                echo json_encode(['status' => 'success']);
                exit;
            } catch (Exception $e) {
                $transaction->rollback($e);
                error_log('動画アップロードエラー: ' . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => '登録に失敗しました']);
                exit;
            }
            echo json_encode(['status' => 'success']);
        } catch (Throwable $e) {
            error_log('動画アップロードエラー: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        error_log('動画アップロードエラー: ' . '不正なモードです');
        echo json_encode(['status' => 'error', 'message' => '不正なモードです']);
        break;
}
