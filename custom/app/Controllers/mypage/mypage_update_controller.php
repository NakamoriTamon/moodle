<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/helpers/form_helpers.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_kbn = isset($_POST['post_kbn']) ? $_POST['post_kbn'] : '';
    $controller = new MypageUpdateController(); // コントローラーのインスタンスを作成
    switch ($post_kbn) {
        case 'update_user':
            $controller->updateUserInfo();
            break;
        case 'update_membership':
            $controller->updateMembershipInfo();
            break;
        case 'email_notification':
            $result = $controller->changeEmailNotifications();

            // 結果をJSON形式で返す
            echo json_encode(['message' => $result ? '設定が保存されました' : '設定の保存に失敗しました']);
            break;

        // 他のリクエストタイプに応じた処理を追加可能
        default:
            die('不正なリクエストです');
            break;
    }
}

class MypageUpdateController {
    public function updateUserInfo () {
        global $DB; 

        $user_id = $_SESSION['USER']->id;
        $name_size = 50;
        $name = htmlspecialchars(required_param('name', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
        $_SESSION['errors']['name'] = validate_text($name, 'お名前', $name_size, true);
        $name_kana = htmlspecialchars(required_param('name_kana', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
        $_SESSION['errors']['name_kana'] = validate_kana($name_kana, $name_size);
        $city = htmlspecialchars(required_param('city', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
        $_SESSION['errors']['city'] = validate_select($city, 'お住いの都道府県', true);
        
        $email = required_param('email', PARAM_TEXT);
        $_SESSION['errors']['email'] = validate_custom_email($email);
        $user_list = $DB->get_records_select('user', 'email = :email AND id != :user_id', ['email' => $email, 'user_id' => $user_id]);
        
        if (!empty($user_list)) {
            foreach ($user_list as $user) {
                $general_user = $DB->get_record('role_assignments', ['userid' => $user->id, 'roleid' => 7]);
                if ($general_user) {
                    $email_error = '既に使用されています。';
                    $_SESSION['errors']['email'] = $email_error;
                    break;
                }
            }
        }
        $birthday = empty($_POST['birthday']) ? null : $_POST['birthday']; // 生年月日
        // ユーザー重複チェック(管理者含む)
        $timestamp_format = date("Y-m-d H:i:s", strtotime($birthday));
        $user_list = $DB->get_records('user', ['phone1' => $phone, 'birthday' => $timestamp_format, 'name_kana' => $kana]);
        if (!empty($user_list)) {
            foreach ($user_list as $user) {
                $general_user = $DB->get_record('role_assignments', ['userid' => $user->id, 'roleid' => 7]);
                if ($general_user) {
                    $email_error = '既に使用されています。';
                    $_SESSION['errors']['email'] = $email_error;
                    break;
                }
            }
        }
        
        $password = htmlspecialchars(required_param('password', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
        if(!empty($password)){
            $_SESSION['errors']['password'] = validate_password($password);
        } else {
            $_SESSION['errors']['password'] = null;
        }
        $phone = htmlspecialchars(required_param('phone', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
        $phone = str_replace('ー', '-', $phone);
        $_SESSION['errors']['phone'] = validate_tel_number($phone);
        $_SESSION['errors']['birthday'] = validate_date($birthday, '生年月日', true);
        
        // 生年月日整合性チェック
        if (strtotime($timestamp_format) >= strtotime(date("Y-m-d H:i:s"))) {
            $_SESSION['errors']['birthday'] = '生年月日は過去の日付を入れてください。';
        }
        
        $description = htmlspecialchars(required_param('description', PARAM_TEXT), ENT_QUOTES, 'UTF-8'); // その他
        $_SESSION['errors']['description'] = validate_textarea($description, '備考', false);
   
        $current_date = new DateTime();
        $birthday_obj = new DateTime($birthday);
        $age = $current_date->diff($birthday_obj)->y;

        // 保護者情報
        $guardian_name = "";
        $guardian_email = "";
        if($age < 13) {
            $guardian_name = htmlspecialchars(required_param('guardian_name', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
            $_SESSION['errors']['guardian_name'] = validate_text($guardian_name, '保護者の苗字', $name_size, true);
            $guardian_email = required_param('guardian_email', PARAM_EMAIL); // メールアドレス
            $_SESSION['errors']['guardian_email'] = validate_custom_email($guardian_email, '保護者の');
        }
        
        // $notification_kbn = htmlspecialchars(optional_param('notification_kbn', 1, PARAM_TEXT));
        
        $result = false;
        // エラーがある場合
        if($_SESSION['errors']['name']
            || $_SESSION['errors']['name_kana']
            || $_SESSION['errors']['city']
            || $_SESSION['errors']['email']
            || $_SESSION['errors']['password']
            || $_SESSION['errors']['phone']
            || $_SESSION['errors']['birthday']
            || $_SESSION['errors']['description']) {
            $result = true;
        }
        if( $age < 13) {
            if($_SESSION['errors']['guardian_name']
            || $_SESSION['errors']['guardian_email']) {
                $result = true;
            }
        }
        // バリデーションチェックの結果
        if($result) {
            $_SESSION['old_input'] = $_POST; // 入力内容も保持
        
            header('Location: /custom/app/Views/mypage/index.php#user_form');
            return;
        }
        
        try{
            if (isloggedin() && isset($_SESSION['USER'])) {
                // 接続情報取得
                $baseModel = new BaseModel();
                $pdo = $baseModel->getPdo();
                $pdo->beginTransaction();
        
                $data = new stdClass();
                $data->id = (int)$user_id;
                $data->name = $name;
                $data->name_kana = $name_kana;
                $data->city = $city;
                $data->email = $email;
                $data->phone1 = $phone;
                $data->birthday = $birthday;
                $data->description = $description;
                $data->guardian_name = $guardian_name;
                $data->guardian_email = $guardian_email;
        
                if (!empty($password)) {
                    $data->password = password_hash($password, PASSWORD_DEFAULT);
                }
                
                $DB->update_record_raw('user', $data);
        
                $pdo->commit();
                $_SESSION['message_success'] = '登録が完了しました';
                header('Location: /custom/app/Views/mypage/index.php#user_form');
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
            header('Location: /custom/app/Views/mypage/index.php#user_form');
        }
    }    
    
    public function updateMembershipInfo () {
        global $DB; 
        
        $user_id = $_SESSION['USER']->id;
        $name_size = 50;
        $size = 500;
        $id = htmlspecialchars(required_param('tekijuku_commemoration_id', PARAM_INT), ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars(required_param('tekijuku_name', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
        $_SESSION['errors']['tekijuku_name'] = validate_text($name, 'お名前', $name_size, true);
        $kana = htmlspecialchars(required_param('kana', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
        $_SESSION['errors']['kana'] = validate_kana($kana, $name_size);

        $post_code = htmlspecialchars(required_param('post_code', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
        
        // 郵便番号形式チェック
        if ($post_code && !preg_match('/^\d+$/', $post_code)) {
            $post_code_error =  '郵便番号は数値で入力してください';
        }

        if (empty($post_code)) {
            $post_code_error =  '郵便番号は必須です。';
        }
        
        $address = htmlspecialchars(required_param('address', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
        
        $_SESSION['errors']['address'] = validate_max_text($address, '住所', $size, true);
        $email = required_param('tekijuku_email', PARAM_TEXT);
        $_SESSION['errors']['tekijuku_email'] = validate_custom_email($email);
        $tekijuku_commem_count = $DB->get_records_select('tekijuku_commemoration', 'email = :email AND fk_user_id != :fk_user_id', ['is_delete' => false, 'email' => $email, 'fk_user_id' => $user_id]);
        
        if (count($tekijuku_commem_count) > 0) {
            $_SESSION['errors']['email'] = '既に登録されています。';
        }

        $tell_number = htmlspecialchars(required_param('tell_number', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
        $tell_number = str_replace('ー', '-', $tell_number);
        $_SESSION['errors']['phone'] = validate_tel_number($tell_number);
        
        
        $note = htmlspecialchars(required_param('note', PARAM_TEXT), ENT_QUOTES, 'UTF-8'); // その他
        $_SESSION['errors']['note'] = validate_max_text($note, '備考', $size, false);

        $payment_method = htmlspecialchars(required_param('payment_method', PARAM_INT), ENT_QUOTES, 'UTF-8');
        $is_published = htmlspecialchars(required_param('is_published', PARAM_INT), ENT_QUOTES, 'UTF-8');
        $is_subscription = htmlspecialchars(required_param('is_subscription', PARAM_INT), ENT_QUOTES, 'UTF-8');

        foreach ($_SESSION['errors'] as $error) {
            if (!empty($error)) {
                $_SESSION['old_input'] = $_POST;

                header('Location: /custom/app/Views/mypage/index.php#tekijuku_form');
                exit;
            }
        }
        
        try{
            if (isloggedin() && isset($_SESSION['USER'])) {
                // 接続情報取得
                $baseModel = new BaseModel();
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
                $data-> payment_method = $payment_method;
                $data->note = $note;
                $data->is_published = $is_published;
                $data->is_subscription = $is_subscription;
        
                
                $DB->update_record('tekijuku_commemoration', $data);
        
                $pdo->commit();
                $_SESSION['message_success'] = '登録が完了しました';
                header('Location: /custom/app/Views/mypage/index.php#tekijuku_form');
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
            header('Location: /custom/app/Views/mypage/index.php#tekijuku_form');
        }

    }

    // お知らせメール設定API
    public function changeEmailNotifications() {
        global $DB; 
        
        $user_id = $_SESSION['USER']->id;
        $email_notification = $_POST['email_notification'] ?? 0;

        try {
            if (isloggedin() && isset($_SESSION['USER'])) {
                $baseModel = new BaseModel();
                $pdo = $baseModel->getPdo();
                $pdo->beginTransaction();
                $data = new stdClass();
                $data->id = (int)$user_id;
                $data->notification_kbn = $email_notification;

                $DB->update_record_raw('user', $data);

                $pdo->commit();
                $_SESSION['message_success'] = '登録が完了しました';
                header('Location: /custom/app/Views/mypage/index.php');
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['message_error'] = '登録に失敗しました';
            header('Location: /custom/app/Views/mypage/index.php');
        }
    }
}