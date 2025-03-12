<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');

$name = $_POST['name'];
$kana = $_POST['kana'];
$post_code = $_POST['post_code'];
$address = $_POST['address'];
$tell_number = $_POST['tell_number'];
$email = $_POST['email'];
$unit = $_POST['unit'];
$price = $_POST['price'];
$is_university_member = $_POST['is_university_member'] ?? 0;
$department = $_POST['department'];
$major = $_POST['major'];
$official = $_POST['official'];
$payment_method = $_POST['payment_method'];
$note = $_POST['note'];
$_SESSION['old_input'] = $_POST;

// バリデーションチェック
$name_size = 50;
$size = 500;
$name_error = validate_max_text($name, 'お名前', $name_size, true);
$kana_error = validate_max_text($kana, 'フリガナ', $name_size, true);
$address_error = validate_max_text($address, '住所', $size, true);
$email_error = validate_custom_email($email, $text = "");
$unit_error = validate_int($unit, '口数', true);
$price_error = validate_int($price, '金額', true);
$note_error = validate_note($note, '備考', $size, false);
$department_error = validate_max_text($department, '所属部局（学部・研究科）', $name_size, $is_university_member === 0 ? false : true);
$major_error = validate_max_text($major, '講座/部課/専攻名', $name_size, false);
$official_error = validate_max_text($official, '職名・学年', $name_size, $is_university_member === 0 ? false : true);
$tell_number_error = validate_tel_number($tell_number);
$tekijuku_commem_count = $DB->count_records('tekijuku_commemoration', ['is_delete' => false, 'email' => $email]);
if ($tekijuku_commem_count > 0) {
    $email_error = '既に登録されています。';
}

// 郵便番号形式チェック
if ($post_code && !preg_match('/^\d+$/', $post_code)) {
    $post_code_error =  '郵便番号は数値で入力してください';
}

if (empty($post_code)) {
    $post_code_error =  '郵便番号は必須です。';
}

// エラーメッセージをセッションに保存
$_SESSION['errors'] = [
    'name' => $name_error,
    'kana' => $kana_error,
    'address' => $address_error,
    'email' => $email_error,
    'tell_number' => $tell_number_error,
    'post_code' => $post_code_error,
    'note' => $note_error,
    'unit' => $unit_error,
    'price' => $price_error,
    'department' => $department_error,
    'major' => $major_error,
    'official' => $official_error,
];
foreach ($_SESSION['errors'] as $error) {
    if (!empty($error)) {
        header('Location: /custom/app/Views/tekijuku/registrate.php');
        exit;
    }
}
$_SESSION['old_input']['combine_tell_number'] = $tell_number;
header('Location: /custom/app/Views/tekijuku/confirm.php');
exit;
