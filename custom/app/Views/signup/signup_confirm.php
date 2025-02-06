<?php
require_once('/var/www/html/moodle/config.php');
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/local/commonlib/lib.php');

global $DB;

// GET パラメータから idnumber を取得
$hash  = $_GET['idnumber'] ?? '';
$expires  = (int) $_GET['expires'];

if (empty($hash)) {
    echo "不正なリクエストです。";
    exit;
}

// 現在時刻が有効期限を超えている場合はエラー表示
if (time() > $expires) {
    die('URLの有効期限が切れています。');
}

// hash をキーに、対象ユーザーを取得（テーブル名やフィールド名は環境に合わせて調整）
$sql = "SELECT * FROM {user} WHERE user_id = :user_id";
$user = $DB->get_record_sql($sql, ['user_id' => $hash]);

if (!$user) {
    echo "認証に失敗しました。ユーザーが見つかりません。";
    exit;
}

// すでに本登録済みの場合はその旨を表示
if ($user->confirmed == 1) {
    echo "このユーザーはすでに本登録されています。";
    exit;
}

$sql = "SELECT MAX(user_id) AS max_user_id FROM {user} 
        WHERE confirmed = 1 AND user_id IS NOT NULL";
$maxRecord = $DB->get_record_sql($sql);
if ($maxRecord && isset($maxRecord->max_user_id) && $maxRecord->max_user_id > 0) {
    $newUserId = $maxRecord->max_user_id + 1;
} else {
    $newUserId = 1;
}

$data = new stdClass();
$data->id        = $user->id;
$data->confirmed = 1;
$data->user_id   = sprintf("%08d", $newUserId);

if ($DB->update_record_raw('user', $data)) {
?>
    <!DOCTYPE html>
    <html lang="ja">

    <head>
        <meta charset="UTF-8">
        <title>本登録完了</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f9f9f9;
                margin: 0;
                padding: 0;
            }

            .container {
                width: 80%;
                max-width: 600px;
                margin: 100px auto;
                padding: 20px;
                background-color: #fff;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                text-align: center;
            }

            h1 {
                color: #333;
            }

            p {
                color: #555;
            }

            a {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 20px;
                background-color: #2CD8D5;
                color: #fff;
                text-decoration: none;
                border-radius: 4px;
            }

            a:hover {
                background-color: #27bdbd;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <h1>本登録が完了しました</h1>
            <p>ご登録いただいたメールアドレスの認証が完了しました。<br>
                ログイン画面からログインしてください。</p>
            <a href="/custom/app/Views/signup/index.php">ログインページへ</a>
        </div>
    </body>

    </html>
<?php
} else {
    echo "認証に失敗しました。システム管理者にお問い合わせください。";
    exit;
}
