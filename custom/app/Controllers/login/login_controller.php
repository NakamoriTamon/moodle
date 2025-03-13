<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');


$email = $_POST['email'];
// $password = $_POST['password'];

$password = required_param('password', PARAM_RAW); // パスワード
$_SESSION['old_input'] = $_POST;

$email_error = empty($email) ? 'メールアドレスかユーザーIDを入力してください。' : null;
$password_error = empty($password) ? 'パスワードを入力してください。' : null;

// エラーメッセージをセッションに保存
$_SESSION['errors'] = [
    'email' => $email_error,
    'password' => $password_error,
];

foreach ($_SESSION['errors'] as $error) {
    if (!empty($error)) {
        redirect('/custom/app/Views/login/index.php');
        exit;
    }
}

// 接続情報取得
global $DB;
// $password = password_hash($password, PASSWORD_DEFAULT);

// 管理者のメールアカウントも含む
$user_list = $DB->get_records('user', ['email' => $email, 'confirmed' => 1, 'deleted' => 0]);

if (!$user_list) {
    $user_id = ltrim($email, '0');
    $user_list = $DB->get_records('user', ['id' => $user_id, 'confirmed' => 1, 'deleted' => 0]);
}
// ユーザー情報がなければログイン不可
if (!$user_list) {
    $_SESSION['message_error'] = 'ログインに失敗しました。';
    header('Location: /custom/app/Views/login/index.php');
    exit;
}
foreach ($user_list as $user) {
    $login_user = $DB->get_record('role_assignments', ['userid' => $user->id]);

    if (
        $login_user
        && validate_internal_user_password($user, $password) // パスワードが通らない時は一時的にこの行をコメントアウト後userのpasswordをUIで変更してください。
    ) {
        complete_user_login($user); // 追加　セッションに$USER情報を入れる
        $_SESSION['user_id'] = $user->id; // DBから取得したユーザーIDを保存
        unset($_SESSION['old_input']['email']);
        header('Location: /custom/app/Views/index.php');
        exit;
    }
}

$_SESSION['message_error'] = 'ログインに失敗しました。';
header('Location: /custom/app/Views/login/index.php');
exit;
