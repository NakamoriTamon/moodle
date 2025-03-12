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
    $local_part = explode('@', $email)[0];
    $domain = explode('@', $email)[1];  // @の前がローカルパート
    if (strlen($email) > 255) {
        return 'メールアドレスは255文字以下にしてください。';
    } else if (strlen($local_part) > 64) {
        return 'メールアドレスのローカルパート(@より前)は64文字以下にしてください。';
    } else if (strlen($domain) > 255) {
        return 'メールアドレスのドメイン(@より後)は255文字以下にしてください。';
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
    if (mb_strlen($val) > $size) {
        return $title . 'は' . $size . '文字以下である必要があります。';
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
    if (mb_strlen($val) > 10000) {
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
    } elseif(is_null($val) && !$required) {
        return null;
    }
    if (is_int($val)) {
        return $title . 'は数字を入力してください。';
    }

    $limt_32bit = 2147483647;
    if ($val > $limt_32bit) {
        return $title . 'は21億を超えることはできません。';
    }
    return null;
}

/**
 * バリデーション: 整数チェック
 */
function validate_int_zero_ok($val, $title, $required)
{
    if (empty($val) && $required) {
        return $title . 'は必須です。';
    } elseif(is_null($val) && !$required) {
        return null;
    }
    if (!is_numeric($val)) {
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

    if($required && !is_null($val)) {
        $format = 'Y-m-d'; // 期待される日付フォーマット
        $d = DateTime::createFromFormat($format, $val);
        // フォーマットが正しいかつ、有効な日付であることを確認
        if ($d && $d->format($format) === $val) {
            return null;
        } else {
            return $title . "形式が違っています。";
        }
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
    if (empty($val)) {
        return 'フリガナは必須です。';
    }
    if (strlen($size) > 50) {
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
 * バリデーション: 資料
 */
function validate_material_file($files)
{
    $max_size = 50 * 1024 * 1024; // 50MB（ファイルごとの上限）
    $total_limit = 1 * 1024 * 1024 * 1024; // 1GB（合計上限）
    $error_messages = [];
    $total_file_size = 0;

    if ($total_file_size >= $total_limit) {
        return 'アップロード上限は1GBまで';
    }

    foreach ($files as $course_index => $file_names) {
        if (!is_array($file_names)) {
            continue;
        }
        foreach ($file_names as $i => $file_name) {
            // 各ファイルのサイズを累積
            $current_file_size = isset($files['size'][$course_index][$i]) ? $files['size'][$course_index][$i] : 0;
            $total_file_size += $current_file_size;

            // ファイルが選択されていない場合はスキップ
            if (empty($file_name)) {
                continue;
            }

            // エラーコード取得
            $err_code = $files['error'][$course_index][$i] ?? UPLOAD_ERR_NO_FILE;
            if ($err_code === UPLOAD_ERR_NO_FILE) {
                // 未選択の場合はスキップ
                continue;
            }
            if ($err_code !== UPLOAD_ERR_OK) {
                $error_messages[$course_index][$i] = "ファイルアップロード時にエラーが発生しました。（エラーコード: {$err_code}）";
                continue;
            }

            // 拡張子チェック（小文字に変換）
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                $error_messages[$course_index][$i] = "許可されていないファイル形式です。PDFをアップロードしてください。";
                continue;
            }

            // 個々のファイルサイズチェック
            if ($current_file_size > $max_size) {
                $error_messages[$course_index][$i] = "30MBを超える資料はアップロードできません。";
                continue;
            }
        }
    }

    return !empty($error_messages) ? $error_messages : false;
}
