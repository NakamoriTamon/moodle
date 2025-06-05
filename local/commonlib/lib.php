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
    foreach ($emails as $email) {
        if (empty($email)) {
            return $text . 'メールアドレスは必須です。';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '無効なメールアドレスです。';
        }
    }
    return null;
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
function validate_textarea($val, $title, $required, $size = 10000)
{
    if (empty($val) && $required) {
        return $title . 'は必須です。';
    } elseif (empty($val) && !$required) {
        return null;
    }
    // 改行を除去
    $val_without_newlines = str_replace(["\r", "\n"], '', $val);

    if (mb_strlen($val_without_newlines) > $size) {
        return $title . 'は' . $size . '文字以下である必要があります。';
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
    } elseif (is_null($val) && !$required) {
        return null;
    }
    if (is_int($val)) {
        return $title . 'は数字を入力してください。';
    }

    $limt_32bit = 2147483647;
    if ($val > $limt_32bit) {
        return $title . 'は制限内の値を超えることはできません。';
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
    } elseif (is_null($val) && !$required) {
        return null;
    }
    if (!is_numeric($val)) {
        return $title . 'は数字を入力してください。';
    }

    $limt_32bit = 2147483647;
    if ($val > $limt_32bit) {
        return $title . 'は制限内の値を超えることはできません。';
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

    if ($required && !is_null($val)) {
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
    if (!isset($val) || empty($val['name'])) {
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
    if (mb_strlen($val) > 50) {
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

/**
 * バリデーション: アンケート　選択
 */
function validate_input($input)
{
    if (empty($input)) {
        return '上記は必須です。';
    }
    return null;
}

/**
 * バリデーション: アンケート　入力フォーム
 */
function validate_text_input($text_input)
{
    if (empty($text_input)) {
        return '上記は必須です。';
    }

    // mb_strlen を使ってUTF-8エンコーディングで文字数をカウント
    if (mb_strlen($text_input, 'UTF-8') > 200) {
        return '上記は200文字以内である必要があります。';
    }
    return null;
}

/**
 * バリデーション: アンケート　その他含む
 */
function validate_other_input($input, $other_input)
{
    if (empty($input) && empty($other_input)) {
        return '上記は必須です。';
    }

    if (mb_strlen($other_input, 'UTF-8') > 200) {
        return '上記は200文字以内である必要があります。';
    }
    return null;
}

// 日付の比較　同日可
function validate_date_comparison($start_date, $end_date, $text1, $text2)
{
    // 日付を DateTime オブジェクトに変換
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);

    // $start_date が $end_date より未来ならエラー
    if ($start > $end) {
        return $text1 . 'が' . $text2 . 'より前の日付になっています。';
    }

    return null;
}

// 日付の比較　同日不可
function validate_date_comparison_not_same_day($start_date, $end_date, $text1, $text2)
{
    // 日付を DateTime オブジェクトに変換
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);

    // $start_date が $end_date より未来ならエラー
    if ($start >= $end) {
        return $text1 . 'を' . $text2 . 'より後の日付にしてください。';
    }

    return null;
}

function validate_google_map($embed_code, $title, $required)
{
    if (empty($embed_code) && $required) {
        return $title . 'は必須です。';
    } elseif (empty($embed_code) && !$required) {
        return null;
    }
    $allowed_tags = '<iframe>'; // iframe のみ許可

    $embed_code = strip_tags($embed_code, $allowed_tags); // 許可タグ以外を削除

    if (!preg_match('/<iframe.*src=["\']https:\/\/www\.google\.com\/maps\/embed?.*["\'].*><\/iframe>/', $embed_code)) {
        return 'Google Mapの埋め込みコードが無効です';
    }

    return null;
}

function validate_url($url, $title, $required)
{
    if (empty($url) && $required) {
        return $title . 'は必須です。';
    } elseif (empty($url) && !$required) {
        return null;
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return $title . 'に入力されたURLは無効な形式です。';
    }
    return null;
}

// チケット枚数
function validate_ticket($input, $limit_num = null)
{
    if (empty($input)) {
        return '枚数選択は必須です。';
    }
    if (is_int($input)) {
        return '枚数選択は数字を入力してください。';
    }
    if (!is_null($limit_num)) {
        if ($input > $limit_num) {
            return '枚数選択は空き枠を超えることはできません。';
        }
    }

    // $limt_32bit = 2147483647;
    // if ($input > $limt_32bit) {
    //     return '枚数選択は21億を超えることはできません。';
    // }
    if ($input > DEFAULT_MAX_TICKET) {
        return '枚数選択は50を超えることはできません。';
    }

    return null;
}
