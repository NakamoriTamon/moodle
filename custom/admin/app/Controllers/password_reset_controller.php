<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');

$email = $_POST['email'] ?? null;

if ($email) {
    global $DB;

    // 入力されたメールアドレスが存在するか確認
    $user = $DB->get_record('user', ['email' => $email]);

    if ($user) {
        // 現在時刻を取得
        $current_time = time();

        // ユーザーの既存のリクエスト確認
        $existing_request = $DB->get_record('user_password_resets', ['userid' => $user->id]);

        if ($existing_request) {
            // 前回リクエストから1時間以上経過しているか確認
            if ($current_time - $existing_request->timerequested < 3600) {
                echo "パスワードリセットリクエストは1時間に1回のみ可能です。";
                exit;
            }

            // リクエストが許可される場合、データを更新
            $existing_request->timerequested = $current_time;
            $existing_request->token = bin2hex(random_bytes(16)); // 32文字のランダムなトークン
            $DB->update_record('user_password_resets', $existing_request);
        } else {
            // 新規リクエストを作成
            $new_request = new stdClass();
            $new_request->userid = $user->id;
            $new_request->timerequested = $current_time;
            $new_request->timererequested = 0; // 初回リクエストの場合は 0
            $new_request->token = bin2hex(random_bytes(16)); // 32文字のランダムなトークン
            $DB->insert_record('user_password_resets', $new_request);
        }

        // 再設定URLを生成
        $reset_url = $CFG->wwwroot . '/custom/admin/app/Views/reset.php?token=' . $new_request->token;

        // メール送信
        $subject = "パスワード再設定のリクエスト";
        $message = "以下のリンクをクリックしてパスワードを再設定してください。\n\n$reset_url";
        email_to_user($user, core_user::get_support_user(), $subject, $message);

        echo "再設定用のメールを送信しました。";
    } else {
        echo "指定されたメールアドレスは存在しません。";
    }
}
?>
