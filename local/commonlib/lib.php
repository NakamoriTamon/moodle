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
    if (strlen($lastname) >= 101) {
        return '苗字は100文字以下である必要があります。';
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
    if (strlen($firstname) >= 101) {
        return '名前は100文字以下である必要があります。';
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
 * バリデーション: input type="text"
 */
function validate_text($val, $title, $required)
{
    if (empty($val) && $required) {
        return $title . 'は必須です。';
    }
    if (strlen($val) > 255) {
        return $title . 'は255文字以上である必要があります。';
    }
    return null;
}

function validate_text_max225($val, $title, $required)
{
    if (empty($val) && $required) {
        return $title . 'は必須です。';
    }
    if (strlen($val) > 225) {
        return $title . 'は225文字以下である必要があります。';
    }
    return null;
}

function validate_text_max500($val, $title, $required)
{
    if (empty($val) && $required) {
        return $title . 'は必須です。';
    }
    if (strlen($val) > 500) {
        return $title . 'は500文字以下である必要があります。';
    }
    return null;
}

/**
 * バリデーション: textareaタグ
 */
function validate_textarea($val, $title, $required)
{
    if (empty($val) && $required) {
        return $title . 'は必須です。';
    }
    if (strlen($val) > 10000) {
        return $title . 'は10000文字以下である必要があります。';
    }
    return null;
}

/**
 * バリデーション: selectタグ
 */
function validate_select($val, $title, $required)
{
    if (empty($val) && $required) {
        return $title . 'は必須です。';
    }
    return null;
}

/**
 * バリデーション: selectタグmultiple属性
 */
function validate_select_multiple($val, $title, $required)
{
    if (empty($val)) {
        return $title . 'は必須です。';
    }
    return null;
}

/**
 * バリデーション: 整数チェック
 */
function validate_int($val, $title, $required)
{
    if (empty($val) && $required) {
        return $title . 'は必須です。';
    }
    if (is_int($val)) {
        return $title . 'は数字を入力してください。';
    }
    return null;
}

/**
 * バリデーション: input type="date"
 */
function validate_date($val, $title, $required)
{
    if (empty($val) && $required) {
        return $title . 'は必須です。';
    } elseif (empty($val) && !$required) {
        return null;
    }

    $format = 'Y-m-d'; // 期待される日付フォーマット
    $d = DateTime::createFromFormat($format, $val);
    // フォーマットが正しいかつ、有効な日付であることを確認
    if ($d && $d->format($format) === $val) {
        return null;
    } else {
        return $title . "形式が違っています。";
    }
}

/**
 * バリデーション: HH:mm形式をチェック
 */
function validate_time($val, $title, $required)
{
    if (empty($val) && $required) {
        return $title . 'は必須です。';
    } elseif (empty($val) && !$required) {
        return null;
    }

    // 正規表現でHH:mm形式をチェック
    if (preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $val)) {
        return null;
    } else {
        return $title . "形式が違っています。";
    }
}

/**
 * バリデーション: HH:mm形式をチェック
 */
function validate_image_file($val, $title, $required)
{
    if(empty($val['name']) && !$required) {
        return null;
    }
    // ファイルアップロードのチェック
    if (!isset($val)) {
        return $title . 'は必須です。';
    }
    if ($val['error'] != UPLOAD_ERR_OK) {
        return $title . 'はファイルのアップロードに失敗しました。';
    }

    // アップロードされたファイル情報を取得
    $allowedExtensions = ['png', 'jpeg', 'jpg']; // 許可する拡張子
    $maxFileSize = 2 * 1024 * 1024; // 最大ファイルサイズ (2MB)

    // ファイルサイズチェック
    if ($val['size'] > $maxFileSize) {
        return $title . 'のファイルサイズが2MBを超えています。';
    }

    // 拡張子チェック
    $fileExtension = strtolower(pathinfo($val['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        return $title . 'が許可されていないファイル形式です。PNG、JPEG、JPGのみ対応しています。';
    }

    // ファイルが画像かどうかをチェック
    if (!@getimagesize($val['tmp_name'])) {
        return $title . 'のアップロードされたファイルは画像ではありません。';
    }
    return null;
}
