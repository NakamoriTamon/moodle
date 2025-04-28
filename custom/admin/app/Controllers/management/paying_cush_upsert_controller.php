<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/helpers/form_helpers.php');

/**
 * 適塾情報の更新メソッド
 */

global $DB;

$fk_user_id = htmlspecialchars(required_param('fk_user_id', PARAM_INT), ENT_QUOTES, 'UTF-8');
$type_code = htmlspecialchars(required_param('type_code', PARAM_INT), ENT_QUOTES, 'UTF-8');
$name_size = 50;
$size = 500;
// 決済状態
$paid_status = htmlspecialchars(required_param('paid_status', PARAM_INT), ENT_QUOTES, 'UTF-8');
$old_paid_status = htmlspecialchars(required_param('old_paid_status', PARAM_INT), ENT_QUOTES, 'UTF-8');
$id = htmlspecialchars(required_param('tekijuku_commemoration_id', PARAM_INT), ENT_QUOTES, 'UTF-8');
$name = htmlspecialchars(required_param('tekijuku_name', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['tekijuku_name'] = validate_text($name, 'お名前', $name_size, true);
$kana = htmlspecialchars(required_param('kana', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
if (!empty($kana)) {
    $kana = preg_replace('/[\x{3000}\s]/u', '', $kana);
}
$_SESSION['errors']['kana'] = validate_kana($kana, $name_size);

$post_code = htmlspecialchars(required_param('post_code', PARAM_TEXT), ENT_QUOTES, 'UTF-8');

// 郵便番号形式チェック
if ($post_code && !preg_match('/^\d+$/', $post_code)) {
    $_SESSION['errors']['post_code'] = '郵便番号は数値で入力してください';
}

if (empty($post_code)) {
    $_SESSION['errors']['post_code'] = '郵便番号は必須です。';
}

$address = htmlspecialchars(required_param('address', PARAM_TEXT), ENT_QUOTES, 'UTF-8');

$_SESSION['errors']['address'] = validate_max_text($address, '住所', $size, true);
$email = required_param('tekijuku_email', PARAM_TEXT);
$_SESSION['errors']['tekijuku_email'] = validate_custom_email($email);
$techiku_commem_count = $DB->get_records_select(
    'tekijuku_commemoration',
    'email = :email AND fk_user_id != :fk_user_id AND is_delete = 0',
    ['email' => $email, 'fk_user_id' => $fk_user_id]
);

// 結果が空でないかをチェック
if (!empty($techiku_commem_count)) {
    $_SESSION['errors']['email'] = '既に登録されています。';
}

if (empty($id)) {
    $unit = htmlspecialchars(required_param('unit', PARAM_INT), ENT_QUOTES, 'UTF-8');
    $_SESSION['errors']['unit'] = validate_int($unit, '口数', true);
}
$price = htmlspecialchars(required_param('price', PARAM_INT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['price'] = validate_int($price, '金額', true);


$tell_number = htmlspecialchars(required_param('tell_number', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$tell_number = str_replace('ー', '-', $tell_number);
$_SESSION['errors']['tell_number'] = validate_tel_number($tell_number);

$is_published = htmlspecialchars(required_param('is_published', PARAM_INT), ENT_QUOTES, 'UTF-8');

$note = htmlspecialchars(required_param('note', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['note'] = validate_textarea($note, '備考', false, 200); // バリデーションチェック
$is_university_member = optional_param('is_university_member', 0, PARAM_INT);
$department = htmlspecialchars(required_param('department', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['department'] = validate_text($department, '所属部局（学部・研究科）', $name_size, $is_university_member === 0 ? false : true);
$major = htmlspecialchars(required_param('major', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['major'] = validate_text($major, '講座/部課/専攻名', $name_size, false);
$official = htmlspecialchars(required_param('official', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['official'] = validate_text($official, '職名', $name_size, $is_university_member === 0 ? false : true);

foreach ($_SESSION['errors'] as $error) {
    if (!empty($error)) {
        $_SESSION['old_input'] = $_POST;

        header('Location: /custom/admin/app/Views/management/paying_cush.php');
        exit;
    }
}

try {
    $transaction = $DB->start_delegated_transaction();
    if (!empty($id)) {
        $data = new stdClass();
        $data->id = (int)$id;
        $data->name = $name;
        $data->kana = $kana;
        $data->post_code = $post_code;
        $data->address = $address;
        $data->tell_number = $tell_number;
        $data->email = $email;
        $data->is_published = $is_published;
        $data->department = $department;
        $data->major = $major;
        $data->official = $official;
        $data->is_university_member = $is_university_member;
        $data->note = $note;

        $_SESSION['message_success'] = '登録が完了しました';
        $DB->update_record_raw('tekijuku_commemoration', $data);
    } else {
        $tekijuku_commemoration = new stdClass();
        $tekijuku_commemoration->created_at = date('Y-m-d H:i:s');
        $tekijuku_commemoration->updated_at = date('Y-m-d H:i:s');
        $tekijuku_commemoration->number = $fk_user_id;
        $tekijuku_commemoration->type_code = $type_code;
        $tekijuku_commemoration->name = $name;
        $tekijuku_commemoration->kana = $kana;
        $tekijuku_commemoration->post_code = $post_code;
        $tekijuku_commemoration->address = $address;
        $tekijuku_commemoration->tell_number = $tell_number;
        $tekijuku_commemoration->email = $email;
        $tekijuku_commemoration->note = $note;
        $tekijuku_commemoration->is_published = $is_published;
        $tekijuku_commemoration->fk_user_id = $fk_user_id;

        $tekijuku_commemoration->department = $department;
        $tekijuku_commemoration->major = $major;
        $tekijuku_commemoration->official = $official;
        $tekijuku_commemoration->unit = $unit;
        $tekijuku_commemoration->price = $price;
        $tekijuku_commemoration->is_university_member = $is_university_member;
        $tekijuku_commemoration->paid_status = PAID_STATUS['PROCESSING']; // 決済中
        $tekijuku_commemoration->payment_start_date = date('Y-m-d H:i:s'); // 決済開始時刻

        $_SESSION['message_success'] = '登録が完了しました';
        $id = $DB->insert_record_raw('tekijuku_commemoration', $tekijuku_commemoration, true);
    }

    if ($old_paid_status != PAID_STATUS['COMPLETED'] && $paid_status == PAID_STATUS['COMPLETED']) {
        // UTC → 日本時間に変換
        $capturedAtJP = (new DateTime())
            ->setTimezone(new DateTimeZone('Asia/Tokyo'))
            ->format('Y-m-d H:i:s');

        $tekijuku_commemoration = new stdClass();
        $tekijuku_commemoration->paid_date = $capturedAtJP;
        $tekijuku_commemoration->paid_status = $paid_status;
        $tekijuku_commemoration->payment_method = 5; // 現金払い
        $tekijuku_commemoration->id = $id;
        $DB->update_record_raw('tekijuku_commemoration', $tekijuku_commemoration);

        $history = new stdClass();
        $history->paid_date = $capturedAtJP;
        $history->price = $price;
        $history->fk_tekijuku_commemoration_id = $id;
        $history->payment_method = 5; // 現金払い
        $DB->insert_record_raw('tekijuku_commemoration_history', $history, true);
    }
    $transaction->allow_commit();
    header('Location: /custom/admin/app/Views/management/paying_cush.php');
} catch (PDOException $e) {
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/admin/app/Views/management/paying_cush.php');
        exit;
    }
}
