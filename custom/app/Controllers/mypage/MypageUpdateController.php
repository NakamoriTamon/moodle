<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/helpers/form_helpers.php');

$user_id = $_SESSION['USER']->id;
$lastname = htmlspecialchars(required_param('lastname', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['lastname'] = validate_text($lastname, '苗字', 100, true);
$firstname = htmlspecialchars(required_param('firstname', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['firstname'] = validate_text($firstname, '名前', 100, true);
$lastname_kana = htmlspecialchars(required_param('lastname_kana', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['lastname_kana'] = validate_text($lastname_kana, '苗字フリガナ', 100, true);
$firstname_kana = htmlspecialchars(required_param('firstname_kana', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['firstname_kana'] = validate_text($firstname_kana, '名前フリガナ', 100, true);
$birthday = empty($_POST['birthday']) ? null : $_POST['birthday']; // 生年月日
$_SESSION['errors']['birthday'] = validate_date($birthday, '生年月日', true);
$city = htmlspecialchars(required_param('city', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['city'] = validate_select($city, '都道府県', true);
$email = required_param('email', PARAM_EMAIL); // メールアドレス
$_SESSION['errors']['email'] = validate_custom_email($email);
$phone = htmlspecialchars(required_param('phone', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$phone = str_replace('ー', '-', $phone);
$_SESSION['errors']['phone'] = validate_phone($phone, '電話番号', true);
$change_password = htmlspecialchars(required_param('change_password', PARAM_INT), ENT_QUOTES, 'UTF-8');
$password = htmlspecialchars(required_param('password', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
if(!empty($change_password)){
    $_SESSION['errors']['password'] = validate_password($password);
} else {
    $_SESSION['errors']['password'] = null;
}
$note = htmlspecialchars(required_param('note', PARAM_TEXT), ENT_QUOTES, 'UTF-8'); // その他
$_SESSION['errors']['note'] = validate_textarea($note, '備考', false);
$guardian_kbn = htmlspecialchars(required_param('guardian_kbn', PARAM_INT), ENT_QUOTES, 'UTF-8');
$age = htmlspecialchars(required_param('age', PARAM_INT), ENT_QUOTES, 'UTF-8');
// 保護者情報
$guardian_lastname = "";
$guardian_firstname = "";
$guardian_lastname_kana = "";
$guardian_firstname_kana = "";
$guardian_email = "";
if(!empty($guardian_kbn) || $age < 14) {
    $guardian_lastname = htmlspecialchars(required_param('guardian_lastname', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
    $_SESSION['errors']['guardian_lastname'] = validate_text($guardian_lastname, '保護者の苗字', 100, true);
    $guardian_firstname = htmlspecialchars(required_param('guardian_firstname', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
    $_SESSION['errors']['guardian_firstname'] = validate_text($guardian_firstname, '保護者の名前', 100, true);
    $guardian_lastname_kana = htmlspecialchars(required_param('guardian_lastname_kana', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
    $_SESSION['errors']['guardian_lastname_kana'] = validate_text($guardian_lastname_kana, '保護者の苗字フリガナ', 100, true);
    $guardian_firstname_kana = htmlspecialchars(required_param('guardian_firstname_kana', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
    $_SESSION['errors']['guardian_firstname_kana'] = validate_text($guardian_firstname_kana, '保護者の名前フリガナ', 100, true);
    $guardian_email = required_param('guardian_email', PARAM_EMAIL); // メールアドレス
    $_SESSION['errors']['guardian_email'] = validate_custom_email($guardian_email, '保護者の');
}

$notification_kbn = htmlspecialchars(optional_param('notification_kbn', 1, PARAM_TEXT));

$result = false;
// エラーがある場合
if($_SESSION['errors']['lastname']
    || $_SESSION['errors']['firstname']
    || $_SESSION['errors']['lastname_kana']
    || $_SESSION['errors']['firstname_kana']
    || $_SESSION['errors']['birthday']
    || $_SESSION['errors']['city']
    || $_SESSION['errors']['email']
    || $_SESSION['errors']['phone']
    || $_SESSION['errors']['password']
    || $_SESSION['errors']['note']) {
    $result = true;
}
if( $age < 14) {
    if($_SESSION['errors']['guardian_lastname']
    || $_SESSION['errors']['guardian_firstname']
    || $_SESSION['errors']['guardian_lastname_kana']
    || $_SESSION['errors']['guardian_firstname_kana']
    || $_SESSION['errors']['guardian_email']) {
        $result = true;
    }
}
// バリデーションチェックの結果
if($result) {
    $_SESSION['old_input'] = $_POST; // 入力内容も保持

    header('Location: /custom/app/Views/mypage/index.php');
    return;
}

try{
    if (isloggedin() && isset($_SESSION['USER'])) {
        // 接続情報取得
        $baseModel = new BaseModel();
        $pdo = $baseModel->getPdo();
        $pdo->beginTransaction();

        if(!empty($change_password)){
            $stmt = $pdo->prepare("
                UPDATE mdl_user
                SET 
                    lastname = :lastname,
                    firstname = :firstname,
                    lastname_kana = :lastname_kana,
                    firstname_kana = :firstname_kana,
                    birthday = :birthday,
                    city = :city,
                    email = :email,
                    phone1 = :phone,
                    password = :password,
                    note = :note,
                    guardian_lastname = :guardian_lastname,
                    guardian_firstname = :guardian_firstname,
                    guardian_lastname_kana = :guardian_lastname_kana,
                    guardian_firstname_kana = :guardian_firstname_kana,
                    guardian_email = :guardian_email,
                    notification_kbn = :notification_kbn
                WHERE id = :id
            ");

            $stmt->execute([
                ':lastname' => $lastname,
                ':firstname' => $firstname,
                ':lastname_kana' => $lastname_kana,
                ':firstname_kana' => $firstname_kana,
                ':birthday' => $birthday,
                ':city' => $city,
                ':email' => $email,
                ':phone' => $phone,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':note' => $note,
                ':guardian_lastname' => $guardian_lastname,
                ':guardian_firstname' => $guardian_firstname,
                ':guardian_lastname_kana' => $guardian_lastname_kana,
                ':guardian_firstname_kana' => $guardian_firstname_kana,
                ':guardian_email' => $guardian_email,
                ':notification_kbn' => $notification_kbn,
                ':id' => $user_id // 一意の識別子をWHERE条件として設定
            ]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE mdl_user
                SET 
                    lastname = :lastname,
                    firstname = :firstname,
                    lastname_kana = :lastname_kana,
                    firstname_kana = :firstname_kana,
                    birthday = :birthday,
                    city = :city,
                    email = :email,
                    phone1 = :phone,
                    note = :note,
                    guardian_lastname = :guardian_lastname,
                    guardian_firstname = :guardian_firstname,
                    guardian_lastname_kana = :guardian_lastname_kana,
                    guardian_firstname_kana = :guardian_firstname_kana,
                    guardian_email = :guardian_email,
                    notification_kbn = :notification_kbn
                WHERE id = :id
            ");

            $stmt->execute([
                ':lastname' => $lastname,
                ':firstname' => $firstname,
                ':lastname_kana' => $lastname_kana,
                ':firstname_kana' => $firstname_kana,
                ':birthday' => $birthday,
                ':city' => $city,
                ':email' => $email,
                ':phone' => $phone,
                ':note' => $note,
                ':guardian_lastname' => $guardian_lastname,
                ':guardian_firstname' => $guardian_firstname,
                ':guardian_lastname_kana' => $guardian_lastname_kana,
                ':guardian_firstname_kana' => $guardian_firstname_kana,
                ':guardian_email' => $guardian_email,
                ':notification_kbn' => $notification_kbn,
                ':id' => $user_id // 一意の識別子をWHERE条件として設定
            ]);
        }

        $pdo->commit();
        $_SESSION['message_success'] = '登録が完了しました';
        header('Location: /custom/app/Views/mypage/index.php');
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
    header('Location: /custom/app/Views/mypage/index.php');
}
?>