<?php
require_once('../../../../../config.php');
require_once('../../../lib/moodlelib.php');

$token = $_POST['token'] ?? null;
$new_password = $_POST['password'] ?? null;

if ($token && $new_password) {
    global $DB;

    // トークンを確認
    $reset_data = $DB->get_record('user_password_resets', ['token' => $token]);

    if ($reset_data && $reset_data->expiry > time()) {
        // ユーザーのパスワードを更新
        $user = $DB->get_record('user', ['id' => $reset_data->userid]);

        if ($user) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $user->password = $hashed_password;
            $DB->update_record('user', $user);

            // トークンを削除
            $DB->delete_records('user_password_resets', ['token' => $token]);

            echo "パスワードが更新されました。";
        }
    } else {
        echo "無効なまたは期限切れのトークンです。";
    }
}
?>
