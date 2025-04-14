<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/TekijukuCommemorationHistoryModel.php');
require_once('/var/www/html/moodle/custom/helpers/form_helpers.php');

/**
 * 適塾情報の更新メソッド
 */

global $DB;

$user_id = htmlspecialchars(required_param('user_id', PARAM_INT), ENT_QUOTES, 'UTF-8');
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
    ['email' => $email, 'fk_user_id' => $user_id]
);

// 結果が空でないかをチェック
if (!empty($techiku_commem_count)) {
    $_SESSION['errors']['email'] = '既に登録されています。';
}

$tell_number = htmlspecialchars(required_param('tell_number', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$tell_number = str_replace('ー', '-', $tell_number);
$_SESSION['errors']['tell_number'] = validate_tel_number($tell_number);

$is_published = htmlspecialchars(required_param('is_published', PARAM_INT), ENT_QUOTES, 'UTF-8');

$is_university_member = optional_param('is_university_member', 0, PARAM_INT);
$department = htmlspecialchars(required_param('department', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['department'] = validate_text($department, '所属部局（学部・研究科）', $name_size, $is_university_member === 0 ? false : true);
$major = htmlspecialchars(required_param('major', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['major'] = validate_text($major, '講座/部課/専攻名', $name_size, false);
$official = htmlspecialchars(required_param('official', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['official'] = validate_text($official, '職名・学年', $name_size, $is_university_member === 0 ? false : true);

foreach ($_SESSION['errors'] as $error) {
    if (!empty($error)) {
        $_SESSION['old_input'] = $_POST;

        header('Location: /custom/admin/app/Views/management/paying_cush.php');
        exit;
    }
}

try {
    if (isloggedin() && isset($_SESSION['USER'])) {
        // 接続情報取得
        $baseModel = new BaseModel();
        $tekijukuCommemorationHistoryModel = new TekijukuCommemorationHistoryModel();
        $pdo = $baseModel->getPdo();
        $pdo->beginTransaction();

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

        $DB->update_record_raw('tekijuku_commemoration', $data);

        if($old_paid_status != PAID_STATUS['COMPLETED'] && $paid_status == PAID_STATUS['COMPLETED']) {
            // UTC → 日本時間に変換
            $capturedAtJP = (new DateTime())
                ->setTimezone(new DateTimeZone('Asia/Tokyo'))
                ->format('Y-m-d H:i:s');
    
            $stmt = $pdo->prepare("
                UPDATE mdl_tekijuku_commemoration
                SET 
                    paid_date = :paid_date,
                    paid_status = :paid_status,
                    payment_method =:payment_method
                WHERE id = :id
            ");
    
            $stmt->execute([
                ':paid_date' => $capturedAtJP,
                ':paid_status' => $paid_status,
                ':payment_method' => 5, // 現金払い
                ':id' => $id
            ]);

            $stmt = $pdo->prepare("
                INSERT INTO mdl_tekijuku_commemoration_history (
                    paid_date,
                    price,
                    fk_tekijuku_commemoration_id, 
                    payment_method
                ) VALUES (
                    :paid_date,
                    (select price from mdl_tekijuku_commemoration WHERE id = :fk_tekijuku_commemoration_id),
                    :fk_tekijuku_commemoration_id,
                    :payment_method
                )
            ");

            $stmt->execute([
                ':paid_date' => $capturedAtJP,
                ':fk_tekijuku_commemoration_id' => $id,
                ':payment_method' => 5 // 現金払い
            ]);
        }

        $pdo->commit();
        $_SESSION['message_success'] = '登録が完了しました';
        header('Location: /custom/admin/app/Views/management/paying_cush.php');
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('適塾情報更新エラー: ' . $e->getMessage());
    $_SESSION['message_error'] = '登録に失敗しました';
    header('Location: /custom/admin/app/Views/management/paying_cush.php');
}