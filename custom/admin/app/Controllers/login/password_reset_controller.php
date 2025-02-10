<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');

$email = $_POST['email'] ?? null;

if ($email) {
    global $DB;

    // 入力されたメールアドレスが存在するか確認
    $user = $DB->get_record('user', ['email' => $email]);

    if ($user) {
        $userRoles = $DB->get_records_sql("
                SELECT r.shortname 
                FROM {role_assignments} ra
                JOIN {role} r ON ra.roleid = r.id
                WHERE ra.userid = ?
            ", [$user->id]);

        $roles = array_map(fn($role) => $role->shortname, $userRoles);
        // 権限がadminかcoursecreatorでない場合
        if(!in_array('admin', $roles) && !in_array('coursecreator', $roles)) {
            $_SESSION['result_message'] = '入力したメールアドレスは存在しません。';
            header('Location: /custom/admin/app/Views/login/result.php');
            return;
        }

        // 現在時刻を取得
        $current_time = time();

        // ユーザーの既存のリクエスト確認
        $existing_request = $DB->get_record('user_password_resets', ['userid' => $user->id]);
        $token = null;

        if ($existing_request) {
            // 前回リクエストから1時間以上経過しているか確認
            if ($current_time - $existing_request->timerequested < 3600) {
                $_SESSION['result_message'] = 'パスワードリセットリクエストは1時間に1回のみ可能です。';
                header('Location: /custom/admin/app/Views/login/result.php');
            }

            // リクエストが許可される場合、データを更新
            $existing_request->timerequested = $current_time;
            $token = bin2hex(random_bytes(16)); // 32文字のランダムなトークン
            $existing_request->token = $token;
            $DB->update_record('user_password_resets', $existing_request);
        } else {
            // 新規リクエストを作成
            $new_request = new stdClass();
            $new_request->userid = $user->id;
            $new_request->timerequested = $current_time;
            $new_request->timererequested = 0; // 初回リクエストの場合は 0
            $token = bin2hex(random_bytes(16)); // 32文字のランダムなトークン
            $new_request->token = $token;
            $DB->insert_record('user_password_resets', $new_request);
        }

        // 再設定URLを生成
        $reset_url = $CFG->wwwroot . '/custom/admin/app/Views/login/reset.php?token=' . $token;

        // メール送信
        $subject = "パスワード再設定のリクエスト";
        $message = "以下のリンクをクリックしてパスワードを再設定してください。\n\n$reset_url";
        email_to_user($user, core_user::get_support_user(), $subject, $message);

        $_SESSION['result_message'] = '再設定用のメールを送信しました。';
        header('Location: /custom/admin/app/Views/login/result.php');
    } else {
        $_SESSION['result_message'] = '入力したメールアドレスは存在しません。';
        header('Location: /custom/admin/app/Views/login/result.php');
    }
}
?>
