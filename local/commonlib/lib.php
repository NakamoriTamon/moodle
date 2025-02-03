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
 * バリデーション: 資料
 */
function validate_material_file($files)
{
    $maxSize = 30 * 1024 * 1024;
    if (!isset($files['name'])) {
        return 'ファイルが送信されていません。';
    }

    foreach ($files['name'] as $courseIndex => $fileNames) {
        if (!is_array($fileNames)) {
            continue;
        }
        foreach ($fileNames as $i => $fileName) {
            if (empty($fileName)) {
                continue;
            }

            // エラーコード取得
            $errCode = $files['error'][$courseIndex][$i];
            if ($errCode === UPLOAD_ERR_NO_FILE) {
                // 未選択
                continue;
            }
            if ($errCode !== UPLOAD_ERR_OK) {
                return "ファイルアップロード時にエラーが発生しました。（エラーコード: {$errCode}）";
            }

            // 拡張子チェック
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                return "許可されていないファイル形式です。PDFをアップロードしてください。";
            }
            // サイズチェック
            $fileSize = $files['size'][$courseIndex][$i] ?? 0;
            if ($fileSize > $maxSize) {
                return "30MBを超える資料はアップロードできません。";
            }
        }
    }
    // 問題なし
    return false;
}
