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
function validate_custom_email($email, $text = "")
{
    if (empty($email)) {
        return $text . 'メールアドレスは必須です。';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return '無効なメールアドレスです。';
    }
    return null;
}

/**
 * バリデーション: メールアドレス
 */
function validate_emails_count($emails, $count, $text = "")
{
    if (count($emails) == $count) {
        return $text . 'メールアドレスは必須です。';
    }
    foreach ($emails as $$email) {
        if (empty($email)) {
            return $text . 'メールアドレスは必須です。';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '無効なメールアドレスです。';
        }
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
    if (strlen($password) < 8 || strlen($password) > 20) {
        return 'パスワードは8文字以上20文字以下である必要があります。';
    }
    // 英字（大文字・小文字）と数字の使用必須
    if (!preg_match('/[A-Za-z]/', $password)) {
        return 'パスワードにはアルファベットが含まれている必要があります。';
    }
    if (!preg_match('/[0-9]/', $password)) {
        return 'パスワードには数字が含まれている必要があります。';
    }
    return null;
}

/*
 * バリデーション: input type="text"
 */
function validate_text($val, $title, $size, $required = false)
{
    if (empty($val) && $required) {
        return $title . 'は必須です。';
    }
    if (strlen($val) > $size) {
        return $title . 'は' . $size . '文字以上である必要があります。';
    }
    return null;
}

/**
 * バリデーション: input type="text"
 */
function validate_phone($val, $title, $required = false)
{
    if (empty($val) && $required) {
        return $title . 'は必須です。';
    }
    if (strlen($val) > 20) {
        return $title . 'は20文字以上である必要があります。';
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
    return null;
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
    return null;
}

/**
 * バリデーション: HH:mm形式をチェック
 */
function validate_image_file($val, $title, $required)
{
    if (empty($val['name']) && !$required) {
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

/**
 * バリデーション: HH:mm形式をチェック
 */
function validate_array($array, $title, $required)
{
    if (empty($array) && $required) {
        return $title . 'は必須です。';
    }

    return null;
}

/*
 * バリデーション: マスタ画像
 */
function validate_image($image)
{
    if (empty($image) || $image['error'] === UPLOAD_ERR_NO_FILE) {
        return '画像は必須です。';
    }
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'svg'];
    $file_extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        return '許可されていない画像形式です。jpg, jpeg, png, svgのいずれかをアップロードしてください。';
    }
    $maxFileSize = 10 * 1024 * 1024; // 最大ファイルサイズ (10MB)
    if ($image['size'] > $maxFileSize) {
        return '画像サイズが2MBを超えています。';
    }
    return null;
}

/*
 * バリデーション: 文字数制限
 */
function validate_max_text($val, $title, $size, $required = false)
{
    if (empty($val) && $required) {
        return $title . 'は必須です。';
    }
    if (mb_strlen($val) > $size) {
        return $title . 'は' . $size . '文字以下である必要があります。';
    }
    return null;
}

/*
 * バリデーション: 電話番号
 */
function validate_tel_number($tel_number)
{
    if (empty($tel_number)) {
        return '電話番号は必須です。';
    }
    if (strlen($tel_number) > 15) {
        return '無効な電話番号です。';
    }
    if (!preg_match('/^\d+$/', $tel_number)) {
        return '無効な電話番号です。';
    }
    return null;
}

/*
 * バリデーション: フリガナ
 */
function validate_kana($val, $size)
{
    if (empty($tel_number)) {
        return 'フリガナは必須です。';
    }
    if (strlen($tel_number) > 50) {
        return 'フリガナは50文字以下である必要があります。';
    }
    if (!preg_match('/^[ァ-ヶーｦ-ﾟ]+$/u', $val)) {
        return '指定されている形式で入力してください。';
    }
    return null;
}

/**
 * バリデーション: 備考
 */
function validate_note($val, $title, $size, $required)
{
    if (empty($val) && $required) {
        return $title . 'は必須です。';
    }
    if (mb_strlen($val) > $size) {
        return $title . 'は' . $size . '文字以下である必要があります。';
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
function validate_program_time($time)
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
 * バリデーション: 快適ではなかった点
 */
function validate_no_good_enviroment_reason($holding_enviroment, $no_good_enviroment_reason)
{
    if (empty($holding_enviroment) && empty($no_good_enviroment_reason)) {
        return '快適ではなかった点は必須です。';
    }

    if (strlen($no_good_enviroment_reason) > 200) {
        return '快適ではなかった点は200文字以内である必要があります。';
    }
    return null;
}

/**
 * バリデーション: 話題やテーマ
 */
function validate_lecture_suggestions($lecture_suggestions)
{
    if (empty($lecture_suggestions)) {
        return '話題やテーマは必須です。';
    }

    if (strlen($lecture_suggestions) > 200) {
        return '話題やテーマは200文字以内である必要があります。';
    }
    return null;
}

/**
 * バリデーション: 話を聞いてみたい大阪大学の教員や研究者
 */
function validate_speaker_suggestions($speaker_suggestions)
{
    if (empty($speaker_suggestions)) {
        return '話を聞いてみたい大阪大学の教員や研究者は必須です。';
    }

    if (strlen($speaker_suggestions) > 200) {
        return '話を聞いてみたい大阪大学の教員や研究者は200文字以内である必要があります。';
    }
    return null;
}
