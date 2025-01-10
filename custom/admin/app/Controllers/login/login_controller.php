<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->libdir . '/authlib.php');

class LoginController
{
    public function handleLogin() {
        global $DB;

        // フォームからのデータを受け取る
        $email = required_param('email', PARAM_EMAIL); // メールアドレス
        $password = required_param('password', PARAM_RAW); // パスワード

        // ユーザー情報を取得
        $user = $DB->get_record('user', ['email' => $email, 'deleted' => 0], '*');

        if ($user && validate_internal_user_password($user, $password)) {
            // 管理者チェック
            if (is_siteadmin($user->id)) {
                // 認証成功: ユーザーをログインさせる
                complete_user_login($user);
                // 管理画面にリダイレクト
                redirect(new moodle_url('/custom/admin/app/Views/management/index.php'));
            } else {
                // 管理者でない場合のエラーメッセージ
                $this->redirectWithError('管理者権限がありません。', '/custom/admin/app/Views/login/login.php');
            }
        } else {
            // 認証失敗時のエラーメッセージ
            $this->redirectWithError('メールアドレスまたはパスワードが間違っています。', '/custom/admin/app/Views/login/login.php');
        }
    }

    private function redirectWithError($message, $redirectUrl) {
        global $SESSION;
        $SESSION->login_error = $message; // セッションにエラーメッセージを保存
        redirect(new moodle_url($redirectUrl));
    }
}

// フォーム送信（POST）でコントローラーを呼び出す処理
$action = optional_param('action', '', PARAM_ALPHA); // アクションパラメータを取得

if ($action === 'login') {
    $controller = new LoginController();
    $controller->handleLogin();
}
