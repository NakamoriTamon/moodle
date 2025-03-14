<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/lib/classes/context/system.php');
require '/var/www/vendor/autoload.php';

use Dotenv\Dotenv;
use core\context\system;
use PHPMailer\PHPMailer\PHPMailer;

// 接続情報取得
global $DB;

$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();

// POSTパラメータの取得
$name = $_POST['name'] ?? null;
$kana = $_POST['kana'] ?? null;
$city = $_POST['city'] ?? null;
$email = $_POST['email'] ?? null;
$email_confirm = $_POST['email_confirm'] ?? null;
$password = $_POST['password'] ?? null;
$password_confirm = $_POST['password_confirm'] ?? null;
$phone = $_POST['phone'] ?? null;
$birthday = $_POST['birthdate'] ?? null;
$guardian_name = $_POST['guardian_name'] ?? null;
$guardian_email = $_POST['guardian_email'] ?? null;
$guardian_phone = $_POST['guardian_phone'] ?? null;
$child_name =  $_POST['child_name'] ?? null;
$description = $_POST['discription'] ?? null;

$_SESSION['old_input'] = $_POST;

// バリデーション
$name_size = 50;
$name_error = validate_max_text($name, 'お名前', $name_size, true);
$kana_error = validate_max_text($kana, 'フリガナ', $name_size, true);
$city_error = validate_select($city, 'お住まいの都道府県', true);
$email_error = validate_custom_email($email);
$password_error = validate_password($password);
$phone_error = validate_tel_number($phone);
$birthday_error = validate_date($birthday, '生年月日', true);
$child_name_error = validate_max_text($birthday, 'お子様の氏名', $name_size);

// 確認用入力内容のバリデーションチェック
$confirm_email_error = validate_custom_email($email_confirm);
$confirm_password_error = validate_password($password_confirm);

// 共通確認用バリデーション
$confirm_email_error = confirm_validation($email_confirm, $email, 'メールアドレス', $confirm_email_error);
$confirm_password_error = confirm_validation($password_confirm, $password, 'パスワード', $confirm_password_error);

// メールアドレス重複チェック(管理者含む)
$user_list = $DB->get_records('user', ['email' => $email, 'deleted' => 0]);
if (!empty($user_list)) {
    foreach ($user_list as $user) {
        $general_user = $DB->get_record('role_assignments', ['userid' => $user->id, 'roleid' => 7]);
        if ($general_user) {
            $email_error = '既に使用されています。';
            break;
        }
    }
}
// ユーザー重複チェック(管理者含む)
$timestamp_format = date("Y-m-d H:i:s", strtotime($birthday));
$user_list = $DB->get_records('user', ['phone1' => $phone, 'birthday' => $timestamp_format, 'name_kana' => $kana, 'deleted' => 0]);
if (!empty($user_list)) {
    foreach ($user_list as $user) {
        $general_user = $DB->get_record('role_assignments', ['userid' => $user->id, 'roleid' => 7]);
        if ($general_user) {
            $email_error = '既に使用されています。';
            break;
        }
    }
}
// 生年月日整合性チェック
if (empty($birthday_error) && strtotime($timestamp_format) >= strtotime(date("Y-m-d H:i:s"))) {
    $birthday_error = '生年月日は過去の日付を入れてください。';
}
// 年齢取得
$guardian_name_error = null;
$guardian_email_error = null;
$guardian_phone_error = null;
if (empty($birthday_error)) {
    $current_date = new DateTime();
    $birthday_obj = new DateTime($birthday);
    $age = $current_date->diff($birthday_obj)->y;
    if ($age < 13) {
        if (empty($guardian_name)) {
            $guardian_name_error = '保護者の氏名は必須です。';
        }
        if (empty($guardian_email)) {
            $guardian_email_error = '保護者メールアドレスは必須です。';
        }
        if (empty($guardian_phone)) {
            $guardian_phone_error = '保護者電話番号は必須です。';
        } else {
            if (strlen($guardian_phone) > 15) {
                $guardian_phone_error = '無効な電話番号です。';
            }
            if (!preg_match('/^\d+$/', $guardian_phone)) {
                $guardian_phone_error = '無効な電話番号です。';
            }
        }
    }
}

// CSRF チェック
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/app/Views/user/index.php');
        exit;
    }
}

// エラーメッセージをセッションに保存
$_SESSION['errors'] = [
    'name' => $name_error,
    'kana' => $kana_error,
    'city' => $city_error,
    'email' => $email_error,
    'email_confirm' => $confirm_email_error,
    'password' => $password_error,
    'password_confirm' => $confirm_password_error,
    'phone' => $phone_error,
    'birthdate' => $birthday_error,
    'guardian_name' => $guardian_name_error,
    'guardian_email' => $guardian_email_error,
    'guardian_phone' => $guardian_phone_error,
    'child_name' => $child_name_error
];

foreach ($_SESSION['errors'] as $error) {
    if (!empty($error)) {
        header('Location: /custom/app/Views/user/index.php');
        exit;
    }
}

// お子様の氏名が入力されていれば保護者とする
$guardian_kbn = !empty($child_name) ? 1 : 0;

try {
    $transaction = $DB->start_delegated_transaction();
    $record = new stdClass();
    $record->username = $name . uniqid();
    $record->password = password_hash($password, PASSWORD_DEFAULT);
    $record->email = $email;
    $record->phone1 = $phone;
    $record->city = $city;
    $record->lang = 'ja';
    $record->timecreated = time();
    $record->timemodified = time();
    $record->birthday = $timestamp_format;
    $record->guardian_kbn = $guardian_kbn;
    $record->name = $name;
    $record->name_kana = $kana;
    $record->guardian_name = $guardian_name;
    $record->guardian_email = $guardian_email;
    $record->guardian_phone = $guardian_phone;
    $record->child_name = $child_name;
    $record->description = $description;

    $id = $DB->insert_record_raw('user', $record, true);

    // 管理者ロールを割り当てる
    $admin_role = $DB->get_record('role', ['shortname' => 'user']);
    $context = system::instance(); // システムコンテキスト
    role_assign($admin_role->id, $id, $context->id);

    $user_id = encrypt_id($id, $url_secret_key);
    $expiration_time = encrypt_id(time() + (24 * 60 * 60), $url_secret_key);

    // 本登録確認URLの作成
    $confirmUrl = $CFG->wwwroot . "/custom/app/Views/signup/signup_confirm.php?id=" . $user_id . "&expiration_time=" . $expiration_time;

    // メール送信処理
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

    // メール本文（確認URLを表示）
    $htmlBody = "
        <div style=\"text-align: center; font-family: Arial, sans-serif;\">
            <p style=\"text-align: left; font-weight:bold;\">{$name}様</p><br>
            <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">このたびは「知の広場」にご登録いただき、ありがとうございます。
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
    $mail->Subject = '【知の広場】仮登録完了のお知らせ';
    $mail->Body = $htmlBody;

    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true
        )
    );

    $mail->send();
    $transaction->allow_commit();
    redirect('/custom/app/Views/user/complete.php');
    exit;
} catch (Throwable $e) {
    try {
        $transaction->rollback($e);
    } catch (Throwable $e) {
        $_SESSION['message_error'] = '登録に失敗しました';
        redirect('/custom/app/Views/user/index.php');
        exit;
    }
}


// 確認項目バリデーション
function confirm_validation($value, $comparison_value, $title, $error)
{
    if (empty($value)) {
        return  $title . '(確認用)は必須です。';
    }
    if (empty($error)) {
        if ($value !== $comparison_value) {
            return  $title . 'が異なります。';
        }
    }
    return $error;
}

// 暗号化
function encrypt_id($id, $key)
{
    $iv = substr(hash('sha256', $key), 0, 16);
    return urlencode(base64_encode(openssl_encrypt($id, 'AES-256-CBC', $key, 0, $iv)));
}
