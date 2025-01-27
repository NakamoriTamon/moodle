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
 * バリデーション: カテゴリー名
 */
function validate_category_name($category_name)
{
    if (empty($category_name)) {
        return 'カテゴリー名必須です。';
    }
    if (strlen($category_name) >= 50) {
        return 'カテゴリー名は50文字以下である必要があります。';
    }
    return null;
}

/**
 * バリデーション: 画像
 */
function validate_image($image)
{
    if (empty($image) || $image['error'] === UPLOAD_ERR_NO_FILE) {
        return '画像は必須です。';
    }
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    $file_extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        return '許可されていない画像形式です。jpg, jpeg, pngのいずれかをアップロードしてください。';
    }
    return null;
}
