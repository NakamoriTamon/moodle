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
 * バリデーション: プログラムの紹介元
 */
function validate_found_method($found_method, $other_found_method)
{
    if (empty($found_method) && empty($other_found_method)) {
        return 'プログラムの紹介元は必須です。';
    }

    if (strlen($other_found_method) > 200) {
        return 'プラグラムの紹介元は200文字以内である必要があります。';
    }
    return null;
}

/**
 * バリデーション: プログラムの受講理由
 */
function validate_reason($reason, $other_reason)
{
    if (empty($reason) && empty($other_reason)) {
        return 'プログラムの受講理由は必須です。';
    }

    if (strlen($other_reason) > 200) {
        return 'プラグラムの受講理由は200文字以内である必要があります。';
    }
    return null;
}

/**
 * バリデーション: プログラムの満足度
 */
function validate_satisfaction($satisfaction)
{
    if (empty($satisfaction)) {
        return 'プログラムの満足度は必須です。';
    }
    return null;
}

/**
 * バリデーション: プログラムの理解度
 */
function validate_understanding($understanding)
{
    if (empty($understanding)) {
        return 'プログラムの理解度は必須です。';
    }
    return null;
}

/**
 * バリデーション: プログラムの特に良かった点　その他
 */
function validate_good_point($good_point, $other_good_point)
{
    if (empty($good_point) && empty($other_good_point)) {
        return 'プログラムの良かった点は必須です。';
    }

    if (strlen($other_good_point) > 200) {
        return 'プラグラムの良かった点は200文字以内である必要があります。';
    }
    return null;
}

/**
 * バリデーション: プログラムの開催時間
 */
function validate_time($time)
{
    if (empty($time)) {
        return 'プログラムの開催時間は必須です。';
    }
    return null;
}

/**
 * バリデーション: プログラムの開催環境
 */
function validate_holding_enviroment($holding_enviroment)
{
    if (empty($holding_enviroment)) {
        return 'プログラムの開催環境は必須です。';
    }
    return null;
}

/**
 * バリデーション: プログラムの特に良かった点　その他
 */
function validate_no_good_enviroment_reason($holding_enviroment, $no_good_enviroment_reason)
{
    if (empty($holding_enviroment) && empty($no_good_enviroment_reason)) {
        return 'プログラムの良かった点は必須です。';
    }

    if (strlen($no_good_enviroment_reason) > 200) {
        return 'プラグラムの良かった点は200文字以内である必要があります。';
    }
    return null;
}
