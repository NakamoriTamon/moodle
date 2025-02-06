<?php
defined('MOODLE_INTERNAL') || die();

/**
 * バリデーション: 苗字
 */
function validate_last_name($lastname)
{
    if (empty($lastname)) {
        return '苗字は必須です。';
    }
    if (strlen($lastname) < 255) {
        return '苗字は255文字以上である必要があります。';
    }
    return null;
}

/**
 * バリデーション: 名前
 */
function validate_first_name($firstname)
{
    if (empty($firstname)) {
        return '名前は必須です。';
    }
    if (strlen($firstname) < 255) {
        return '名前は255文字以上である必要があります。';
    }
    return null;
}

/**
 * バリデーション: メールアドレス
 */
function validate_custom_email($email)
{
    if (empty($email)) {
        return 'メールアドレスは必須です。';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return '無効なメールアドレスです。';
    }
    return null;
}

/**
 * バリデーション: パスワード
 */
function validate_password($password)
{
    if (empty($password)) {
        return 'パスワードは必須です。';
    }
    if (strlen($password) < 8) {
        return 'パスワードは8文字以上である必要があります。';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return 'パスワードには大文字が含まれている必要があります。';
    }
    if (!preg_match('/[0-9]/', $password)) {
        return 'パスワードには数字が含まれている必要があります。';
    }
    return null;
}

/**
 * バリデーション: ユーザ登録 氏名
 */
function validate_signup_name($name)
{
    if (empty($name)) {
        return '氏名は必須です。';
    }
    if (strlen($name) > 50) {
        return '氏名は50文字以内である必要があります。';
    }
    return null;
}

/**
 * バリデーション: ユーザ登録 フリガナ
 */
function validate_signup_kana($kana)
{
    if (empty($kana)) {
        return 'フリガナは必須です。';
    }
    if (strlen($kana) > 50) {
        return 'フリガナは50文字以内である必要があります。';
    }
    if (!preg_match('/^[ア-ンー]+$/u', $kana)) {
        return 'フリガナは全角カタカナで入力してください。';
    }
    return null;
}

/**
 * バリデーション: ユーザ登録 都道府県
 */
function validate_signup_prefecture($prefecture)
{
    if (empty($prefecture)) {
        return '都道府県は必須です。';
    }
    return null;
}

/**
 * バリデーション: ユーザ登録 メールアドレス
 */
function validate_signup_email($email)
{
    if (empty($email)) {
        return 'メールアドレスは必須です。';
    }
    if (strlen($email) > 255) {
        return 'メールアドレスは255文字以内である必要があります。';
    }
    return null;
}

/**
 * バリデーション: ユーザ登録 メールアドレス（確認用）
 */
function validate_signup_email_confirm($email_confirm, $email)
{
    if (empty($email_confirm)) {
        return 'メールアドレス（確認用）は必須です。';
    }
    if (strlen($email_confirm) > 255) {
        return 'メールアドレス（確認用）は255文字以内である必要があります。';
    }
    if ($email_confirm !== $email) {
        return 'メールアドレスとメールアドレス（確認用）が一致していません。';
    }
    return null;
}

/**
 * バリデーション: ユーザ登録 パスワード
 *
 * ルール:
 * - 8文字以上20文字以内であること
 * - 英字（A-Z, a-z）と数字（0-9）を各1文字以上含むこと
 * - 使用できる記号は !"#$%&'()*+,-./:;<=>?@[]\^_`{|}~ のみ（英数字と併せた全体のみ許容）
 *
 * @param string $password
 * @return string|null エラーメッセージ。問題なければ null を返す。
 */
function validate_signup_password($password)
{
    // 空の場合
    if (empty($password)) {
        return 'パスワードは必須です。';
    }

    // 文字数チェック（※英数字・記号のみの場合は strlen で問題ありません）
    $len = strlen($password);
    if ($len < 8) {
        return 'パスワードは8文字以上である必要があります。';
    }
    if ($len > 20) {
        return 'パスワードは20文字以内である必要があります。';
    }

    // 英字と数字をそれぞれ1文字以上含むかチェック
    if (!preg_match('/(?=.*[A-Za-z])(?=.*\d)/', $password)) {
        return 'パスワードは数字とアルファベットの両方を含む必要があります。';
    }

    // 使用可能な文字は、英数字および下記記号のみとする
    // 許容する記号: !"#$%&'()*+,-./:;<=>?@[]\^_`{|}~
    // 正規表現では以下のようにエスケープする必要があります。
    $pattern = '/^[A-Za-z\d!"#$%&\'()*+,\-\.\/:;<=>?@\[\]\\\^_`\{\|\}~]+$/';
    if (!preg_match($pattern, $password)) {
        return 'パスワードに使用できない記号が含まれています。';
    }

    return null;
}

/**
 * バリデーション: ユーザ登録 パスワード（確認用）
 */
function validate_signup_password_confirm($password_confirm, $password)
{
    if (empty($password_confirm)) {
        return 'パスワード（確認用）は必須です。';
    }
    if ($password_confirm !== $password) {
        return 'パスワードとパスワード（確認用）が一致していません。';
    }
    return null;
}
/**
 * バリデーション: ユーザ登録 生年月日
 */
function validate_signup_birthdate($birthdate)
{
    if (empty($birthdate)) {
        return '生年月日は必須です。';
    }
    return null;
}

/**
 * バリデーション: ユーザ登録 プライバシーポリシー
 */
function validate_signup_policy_agreement($policy_agreement)
{
    if (empty($policy_agreement)) {
        return 'プライバシーポリシーの同意は必須です。';
    }
    return null;
}

/**
 * バリデーション: ユーザ登録 保護者の氏名
 */
function validate_signup_guardian_name($guardian_name)
{
    if (empty($guardian_name)) {
        return '保護者の氏名は必須です。';
    }
    if (strlen($guardian_name) > 50) {
        return '保護者の氏名は50文字以内である必要があります。';
    }
    return null;
}

/**
 * バリデーション: ユーザ登録 保護者連絡先
 */
function validate_signup_guardian_contact($guardian_contact)
{
    if (empty($guardian_contact)) {
        return '保護者連絡先は必須です。';
    }
    if (strlen($guardian_contact) > 50) {
        return '保護者連絡先は50文字以内である必要があります。';
    }
    return null;
}

/**
 * バリデーション: ユーザ登録 保護者の同意
 */
function validate_signup_guardian_consent($guardian_consent)
{
    if (empty($guardian_consent)) {
        return '保護者の同意は必須です。';
    }
    return null;
}
