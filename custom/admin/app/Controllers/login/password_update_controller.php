<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');

$token = $_POST['token'] ?? null;
$new_password = $_POST['password'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $password_confirmation = $_POST['password_confirmation'] ?? '';
    $token = $_POST['token'] ?? '';

    $password_error = validate_password($new_password);

    if($password_error) {
        $_SESSION['result_message'] = $password_error;
        header('Location: /custom/admin/app/Views/login/reset.php?token=' . $token);
        exit;
    }

    // 必要なバリデーションや処理を行う
    if (empty($password) || $password !== $password_confirmation) {
        $_SESSION['result_message'] = 'パスワードが一致しません。';
        header('Location: /custom/admin/app/Views/login/reset.php?token=' . $token);
        exit;
    }

    if ($token && $new_password) {
        global $DB;

        try {
            // トークンを確認
            $reset_data = $DB->get_record('user_password_resets', ['token' => $token]);

            $timerequested = $reset_data->timerequested;
            $current_time = time();

            if ($reset_data && $current_time - $timerequested < 3600) {
                // パスワードのハッシュ化と保存
                try {
                    // ユーザーのパスワードを更新
                    $user = $DB->get_record('user', ['id' => $reset_data->userid]);

                    if ($user) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $user->password = $hashed_password;
                        $DB->update_record('user', $user);

                        // トークンを削除
                        $DB->delete_records('user_password_resets', ['token' => $token]);
                    }
                    $_SESSION['result_message'] = 'パスワードが正常に更新されました。';
                } catch (Exception $e) {
                    $_SESSION['result_message'] = 'エラーが発生しました。再試行してください。';
                }
            } else {
                $_SESSION['result_message'] = '有効時間が過ぎています。再度パスワード再設定のメールを受信してください。';
            }

            // 結果ページにリダイレクト
            header('Location: /custom/admin/app/Views/login/result.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['result_message'] = 'エラーが発生しました。再試行してください。';
        }
    }
} else {
    $_SESSION['result_message'] = 'エラーが発生しました。再試行してください。';
    header('Location: /custom/admin/app/Views/login/reset.php?token=' . $token);
    exit;
}
