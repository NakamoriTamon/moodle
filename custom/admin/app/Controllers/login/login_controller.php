<?php
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once($CFG->libdir . '/authlib.php');

use Dotenv\Dotenv;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class LoginController
{
    public function handleLogin()
    {
        global $DB;

        // フォームからのデータを受け取る
        $email = required_param('email', PARAM_EMAIL); // メールアドレス
        $password = required_param('password', PARAM_RAW); // パスワード

        $email_error = validate_custom_email($email);

        if ($email_error) {
            // 認証失敗時のエラーメッセージ
            $this->redirectWithError('メールアドレスまたはパスワードが間違っています。', '/custom/admin/app/Views/login/login.php');
        }

        // ユーザー情報を取得
        $user_list = $DB->get_records('user', ['email' => $email, 'deleted' => 0]);
        foreach ($user_list as $user) {
            $role = $DB->get_record('role_assignments', ['userid' => $user->id]);
            if ($role->roleid != ROLE['USER']) {
                $user = $user;
                break;
            }
        }

        if ($user && password_verify($password, $user->password)) {

            // ユーザーのロールを取得
            $userRoles = $DB->get_records_sql("
                SELECT r.shortname 
                FROM {role_assignments} ra
                JOIN {role} r ON ra.roleid = r.id
                WHERE ra.userid = ?
            ", [$user->id]);

            $roles = array_map(fn($role) => $role->shortname, $userRoles);

            // `user` ロール (ID:7) は `/custom/admin` にアクセス不可
            if (in_array('user', $roles)) {
                redirect('/custom/app/Views/index.php'); // 一般画面へリダイレクト
                exit;
            }
            // 管理者チェック
            if (is_siteadmin($user->id)) {
                // ワンタイムパスワード発行
                $this->sendOTP($user);
                redirect(new moodle_url('/custom/admin/app/Views/login/otp.php', ['userid' => $user->id]));
                exit;
                // // 認証成功: ユーザーをログインさせる
                // complete_user_login($user);
                // // 管理画面にリダイレクト
                // redirect(new moodle_url('/custom/admin/app/Views/management/index.php'));
            } else {
                // 管理者でない場合のエラーメッセージ
                $this->redirectWithError('管理者権限がありません。', '/custom/admin/app/Views/login/login.php');
            }
        } else {
            // 認証失敗時のエラーメッセージ
            $this->redirectWithError('メールアドレスまたはパスワードが間違っています。', '/custom/admin/app/Views/login/login.php');
        }
    }

    private function redirectWithError($message, $redirectUrl)
    {
        global $SESSION;
        $SESSION->login_error = $message; // セッションにエラーメッセージを保存
        redirect(new moodle_url($redirectUrl));
    }

    // ワンタイムパスワード発行
    private function sendOTP($user) {
        global $DB;
    
        // 6桁のランダムOTPを生成
        $otp = random_int(100000, 999999);
        $expires = time() + 300; // 5分有効
    
        // 既存のOTPがあれば更新、なければ新規作成
        $existing = $DB->get_record('local_otp', ['userid' => $user->id]);
        if ($existing) {
            $existing->otp = $otp;
            $existing->expires = $expires;
            $DB->update_record('local_otp', $existing);
        } else {
            $DB->insert_record('local_otp', [
                'userid' => $user->id,
                'otp' => $otp,
                'expires' => $expires
            ]);
        }
    
        // メール送信
        $this->sendOTPEmail($user->email, $user->id, $otp);
    }
    
    private function sendOTPEmail($email, $userid, $otp) {
        global $CFG;
        
        // Moodleのメール送信関数を利用
        // email_to_user((object)['email' => $email], (object)['email' => $CFG->supportemail], $subject, $message);
        
        $reset_url = $CFG->wwwroot . '/custom/admin/app/Views/login/otp.php?userid=' . $userid;
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $test = getenv('MAIL_HOST');
        $mail->Host = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'];
        $mail->Password = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->Port = $_ENV['MAIL_PORT'];

        $mail->setFrom($_ENV['MAIL_FROM_ADRESS'], 'Sender Name');
        $mail->addAddress($email);

        $mail->addReplyTo('no-reply@example.com', 'No Reply');
        $mail->isHTML(true);

        $htmlBody = "
            <div style=\"text-align: center; font-family: Arial, sans-serif;\">
                <P style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">ワンタイムパスワードを発行しました。</P>
                <P style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">5分以内にワンタイムパスワードを入力してください。</P><br /><br />
                <P style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">ワンタイムパスワード: {$otp}</P><br /><br />
                <p style=\"margin-top: 30px; font-size: 13px; text-align: left;\">このメールは、配信専用アドレスで配信されています。<br>このメールに返信いただいても、返信内容の確認及びご返信ができません。
                あらかじめご了承ください。</p>
            </div>
        ";

        $mail->Subject = '大阪大学 知の広場 ワンタイムパスワードの発行';
        $mail->Body = $htmlBody;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->send();
    }
}

// フォーム送信（POST）でコントローラーを呼び出す処理
$action = optional_param('action', '', PARAM_ALPHA); // アクションパラメータを取得

if ($action === 'login') {
    $controller = new LoginController();
    $controller->handleLogin();
}
