<?php
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

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
    if (empty($movie) || empty($movie->file_name)) {
        throw new Exception('ファイルの削除に失敗しました。');
    }

    $prefix = dirname($movie->file_name);
    $objects = $s3->getPaginator('ListObjectsV2', [
        'Bucket' => $bucket,
        'Prefix' => $prefix,
    ]);

    $deleteObjects = [];
    foreach ($objects as $result) {
        if (!empty($result['Contents'])) {
            foreach ($result['Contents'] as $object) {
                $deleteObjects[] = ['Key' => $object['Key']];
            }
        }
    }

    if (!empty($deleteObjects)) {
        $s3->deleteObjects([
            'Bucket' => $bucket,
            'Delete' => ['Objects' => $deleteObjects],
        ]);
    } else {
        throw new Exception('ファイルの削除に失敗しました。');
    }

    $DB->delete_records('course_movie', ['id' => $id]);
    $transaction->allow_commit();
    $_SESSION['message_success'] = '削除が完了しました';
    header('Location: /custom/admin/app/Views/event/movie.php');
    exit;
} catch (Exception $e) {
    try {
        $transaction->rollback($e);
        error_log('ファイル削除失敗: ' . $e);
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/admin/app/Views/event/movie.php');
        exit;
    }
}
