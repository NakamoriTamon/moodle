<?php
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');

use Dotenv\Dotenv;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();

$email = $_POST['email'] ?? null;

$_SESSION['errors']['email'] = validate_custom_email($email);

if (is_null($_SESSION['errors']['email'])) {
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
                <P style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">以下のリンクをクリックしてパスワードを再設定してください。</P><br /><br />
                <P style=\"text-align: left;  font-size: 13px; margin:0; margin-bottom: 30px; \">" . $reset_url . "</P><br /><br />
                <p style=\"margin-top: 30px; font-size: 13px; text-align: left;\">このメールは、配信専用アドレスで配信されています。<br>このメールに返信いただいても、返信内容の確認及びご返信ができません。
                あらかじめご了承ください。</p>
            </div>
        ";
    
        $mail->Subject = 'パスワード再設定のリクエスト';
        $mail->Body = $htmlBody;
    
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
    
        $mail->send();

        $_SESSION['result_message'] = '再設定用のメールを送信しました。';
        header('Location: /custom/admin/app/Views/login/result.php');
    } else {
        $_SESSION['result_message'] = '入力したメールアドレスは存在しません。';
        header('Location: /custom/admin/app/Views/login/result.php');
    }
} else {
    $_SESSION['old_input'] = $_POST; // 入力内容も保持
    header('Location: /custom/admin/app/Views/login/recipient.php');
}
?>
