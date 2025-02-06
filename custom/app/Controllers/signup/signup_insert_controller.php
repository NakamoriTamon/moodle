<?php
require_once('/var/www/html/moodle/config.php');
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/local/commonlib/lib.php');

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 接続情報取得
global $DB;

$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();

function getPostValue(string $key): string
{
    $value = $_POST[$key] ?? '';
    if (is_array($value)) {
        error_log("getPostValue: Expected string for key '$key', got array");
        return '';
    }
    return trim($value);
}

// POSTパラメータの取得
$name             = getPostValue('name');
$kana             = getPostValue('kana');
$prefecture       = getPostValue('prefecture');
$email            = getPostValue('email');
$email_confirm    = getPostValue('email_confirm');
$password         = getPostValue('password');
$password_confirm = getPostValue('password_confirm');
$phone            = getPostValue('phone');
$birthdate        = getPostValue('birthdate');
$guardian_name    = getPostValue('guardian_name');
$guardian_contact = getPostValue('guardian_contact');
$guardian_consent = $_POST['guardian_consent'] ?? '';
$policy_agreement = $_POST['policy_agreement'] ?? '';

// バリデーション
$name_error             = validate_signup_name($name);
$kana_error             = validate_signup_kana($kana);
$prefecture_error       = validate_signup_prefecture($prefecture);
$email_error            = validate_signup_email($email);
$email_confirm_error    = validate_signup_email_confirm($email_confirm, $email);
$password_error         = validate_signup_password($password);
$password_confirm_error = validate_signup_password_confirm($password_confirm, $password);
$birthdate_error        = validate_signup_birthdate($birthdate);
if (!empty($birthdate)) {
    $today     = new DateTime();
    $birthDate = new DateTime($birthdate);
    $age       = $today->diff($birthDate)->y;

    if ($age < 13) {
        $guardian_name_error    = validate_signup_guardian_name($guardian_name);
        $guardian_contact_error = validate_signup_guardian_contact($guardian_contact);
    } elseif ($age >= 13 && $age < 18) {
        $guardian_consent_error = validate_signup_guardian_consent($guardian_consent);
    }
}
$policy_agreement_error = validate_signup_policy_agreement($policy_agreement);

// 重複チェック：メールアドレスまたは（ユーザー名と電話番号）の組み合わせが一致する場合
$sql = "SELECT id FROM {user}
        WHERE ( email = :email OR ( username = :username AND phone1 = :phone ) )";
$params = [
    'email'    => $email,
    'username' => $name,
    'phone'    => $phone,
];

if ($DB->record_exists_sql($sql, $params)) {
    $email_error = "すでにユーザー登録されています";
}

if (
    $name_error || $kana_error || $prefecture_error || $email_error || $email_confirm_error ||
    $password_error || $password_confirm_error || $birthdate_error || $policy_agreement_error ||
    $guardian_name_error || $guardian_contact_error || $guardian_consent_error
) {

    $_SESSION['errors'] = [
        'name'             => $name_error,
        'kana'             => $kana_error,
        'prefecture'       => $prefecture_error,
        'email'            => $email_error,
        'email_confirm'    => $email_confirm_error,
        'password'         => $password_error,
        'password_confirm' => $password_confirm_error,
        'birthdate'        => $birthdate_error,
        'policy_agreement' => $policy_agreement_error,
        'guardian_name'    => $guardian_name_error ?? null,
        'guardian_contact' => $guardian_contact_error ?? null,
        'guardian_consent' => $guardian_consent_error ?? null,
    ];
    $_SESSION['old_input'] = $_POST;
    $_SESSION['message_error'] = '登録に失敗しました';
    header('Location: /custom/app/Views/signup/index.php');
    exit;
} else {
    // CSRF チェック
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message_error'] = '登録に失敗しました';
            header('Location: /custom/app/Views/signup/index.php');
            exit;
        }
    }

    $mail = new PHPMailer(true);
    $data = new stdClass();
    $data->username     = $name;
    $data->email        = $email;
    $data->password     = password_hash($password, PASSWORD_DEFAULT);
    $data->phone1       = $phone;
    $data->policyagreed = $policy_agreement;

    try {
        $transaction = $DB->start_delegated_transaction();
        $datetime = date('Y-m-d H:i:s');
        $insertedUserId = $DB->insert_record('user', $data, true);
        $formattedUserId = sprintf('%u', crc32(sprintf("%08d", $insertedUserId)));

        $updateData = new stdClass();
        $updateData->id       = $insertedUserId;
        $updateData->user_id  = $formattedUserId;
        $updateData->timecreated  = time();

        $DB->update_record_raw('user', $updateData);
        $expirationTime = time() + (24 * 60 * 60);

        // 本登録確認URLの作成
        $confirmUrl = "http://localhost:8000/custom/app/Views/signup/signup_confirm.php?idnumber=" . urlencode($formattedUserId) . "&expires=" . urlencode($expirationTime);

        // メール送信処理
        $mail->SMTPDebug   = 0; // デバッグ出力オフ
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
            <p style=\"text-align: left; font-weight:bold;\">{$name} さん</p>
            <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">仮登録ありがとうございます。</p>
            <p style=\"text-align: left; font-size: 13px; margin:0; margin-bottom: 30px;\">以下のURLにアクセスして、本登録を完了してください。</p>
            <p style=\"text-align: left; font-size: 13px;\">
                <a href=\"{$confirmUrl}\">{$confirmUrl}</a>
            </p>
            <p style=\"margin-top: 30px; font-size: 13px; text-align: left;\">
                このメールは、配信専用アドレスから送信されています。<br>
                このメールに返信いただいても、返信内容の確認及びご返信はできません。<br>
                ※本確認URLの有効期限等の管理は各自の仕様に合わせて実装してください。
            </p>
        </div>
        ";
        $mail->Subject = '【仮登録完了】本登録確認のご案内';
        $mail->Body    = $htmlBody;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            )
        );

        $mail->send();

        // コミットして仮登録完了とする
        $transaction->allow_commit();

        echo "<div>
            <p>仮登録が完了しました。</p>
            <p>ご登録のメールアドレス宛に本登録確認のメールを送信しました。メール内のURLをクリックして本登録を完了してください。</p>
            <a href='/custom/app/Views/signup/index.php'>イベント詳細画面へ</a>
        </div>";
        exit;
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/app/Views/signup/index.php');
        exit;
    }
}
