<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/lib/accesslib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

use core\context\system as context_system;
use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;

$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();
$name = $_POST['name'] ?? null;
$department = $_POST['department'] ?? null;
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;

// バリデーションチェック
$name_error = validate_text($name, '担当者名', 100, true);
$department_error = validate_text($department, "所属部局", 255, true);
$email_error = validate_custom_email($email);
$password_error = validate_password($password);

// 必要なバリデーションや処理を行う
if ($name_error || $department_error || $email_error || $password_error) {
    // エラーメッセージをセッションに保存
    $_SESSION['errors'] = [
        'name' => $name_error,
        'department' => $department_error,
        'email' => $email_error,
        'password' => $password_error,
    ];
    $_SESSION['old_input'] = $_POST; // 入力内容も保持
    header('Location: /custom/admin/app/Views/login/sign_up.php');
    exit;
} else {
    global $DB, $CFG, $url_secret_key;
    // 入力されたメールアドレスが存在するか確認
    $user = $DB->get_record('user', ['email' => $email, 'deleted' => 0]);

    if (!$user) {
        try {
            $baseModel = new BaseModel();
            $pdo = $baseModel->getPdo();
            $pdo->beginTransaction();

            // ユーザーを作成
            $new_user = new stdClass();
            $new_user->username = strtolower($name . time()); // 例: john.doe1672901234
            $new_user->auth = 'manual'; // 手動認証
            $new_user->confirmed = CONFIRMED['IS_UNCONFIRMED'];
            $new_user->lastname = '';
            $new_user->firstname = '';
            $new_user->email = $email;
            $new_user->password = password_hash($password, PASSWORD_DEFAULT);
            $new_user->department = $department;
            $new_user->timecreated = time();
            $new_user->timemodified = time();
            $new_user->lang = LANG_DEFAULT;
            $new_user->name = $name; // 氏名（姓 名）
            $new_user->name_kana = ''; // 仮で入れる or フォーム入力で受け取る
            $new_user->guardian_kbn = GUARDIAN_KBN_DEFAULT; // 仮
            $new_user->birthday = '2000-01-01 00:00:00'; // 仮


            $user_id = $DB->insert_record_raw('user', $new_user, true);

            // 管理者ロールを割り当てる
            $admin_role = $DB->get_record('role', ['shortname' => 'coursecreator']); // もしくは 'admin'
            $context = context_system::instance(); // システムコンテキスト
            role_assign($admin_role->id, $user_id, $context->id);

            $siteadmins = explode(',', get_config('moodle', 'siteadmins'));
            // 管理者IDがすでに存在しない場合のみ追加
            if (!in_array($user_id, $siteadmins)) {
                $siteadmins[] = $user_id;
                $value = implode(',', $siteadmins);
                set_config('siteadmins', $value);
            } else {
                throw new Exception("Error Processing Request", 1);
            }

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host        = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth    = true;
            $mail->Username    = $_ENV['MAIL_USERNAME'];
            $mail->Password    = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure  = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet     = PHPMailer::CHARSET_UTF8;
            $mail->Port        = $_ENV['MAIL_PORT'];

            $mail->setFrom($_ENV['MAIL_FROM_ADRESS'], 'Sender Name');
            $mail->addAddress($email, 'Recipient Name');
            $mail->addReplyTo('no-reply@example.com', 'No Reply');
            $mail->isHTML(true);

            $encrypt_user_id = encrypt_id($user_id, $url_secret_key);
            $expiration_time = encrypt_id(time() + (24 * 60 * 60), $url_secret_key);
            $confirmUrl = $CFG->wwwroot . "/custom/admin/app/Views/signup/signup_confirm.php?id=" . $encrypt_user_id . "&expiration_time=" . $expiration_time;
            // メール本文（確認URLを表示）
            $htmlBody = "
                <div style=\"text-align: center; font-family: Arial, sans-serif;\">
                    <p style=\"text-align: left; font-weight:bold;\">{$name}様</p><br>
                    <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">管理者登録確認用メールとなります。
                        仮登録が完了しました。</p><br>
                    <p style=\"text-align: left; font-size: 13px; margin:0; \">本登録を完了するには、以下のURLにアクセスしてください。</p>
                    <br>
                    <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">▼ 本登録はこちら
                    <p style=\"text-align: left; font-size: 13px;\">
                        <a href=\"{$confirmUrl}\">{$confirmUrl}</a>
                    </p>
                    <br>
                    <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">※このURLの有効期限は  **24時間以内** です。</P>
                    <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">期限を過ぎると再度仮登録が必要になりますので、ご注意ください。</P><br>
                    <p style=\"margin-top: 30px; font-size: 13px; text-align: left;\">
                        このメールは、配信専用アドレスから送信されています。<br>
                        このメールに返信いただいても、返信内容の確認及びご返信はできません。<br>
                    </p>
                </div>
                ";
            $mail->Subject = '【大阪大学】管理者仮登録完了のお知らせ';
            $mail->Body = $htmlBody;

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                )
            );

            $mail->send();
            $pdo->commit();

            $_SESSION['result_message'] = '仮登録メールを入力されたアドレス宛てに送信しました。';
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['result_message'] = 'エラーが発生しました。再登録してください。';
        }
    } else {
        $_SESSION['result_message'] = '入力したメールアドレスは登録済みです。';
    }
    header('Location: /custom/admin/app/Views/login/result.php');
    exit;
}

// 暗号化
function encrypt_id($id, $key)
{
    $iv = substr(hash('sha256', $key), 0, 16);
    return urlencode(base64_encode(openssl_encrypt($id, 'AES-256-CBC', $key, 0, $iv)));
}
