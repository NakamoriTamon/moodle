<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

$lastname = $_POST['lastname'] ?? null;
$firstname = $_POST['firstname'] ?? null;
$department = $_POST['department'] ?? null;
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;

// バリデーションチェック
$lastname_error = validate_last_name($lastname);
$firstname_error = validate_first_name($firstname);
$department_error = validate_text($department, "所属部局", 255, true);
$email_error = validate_custom_email($email);
$password_error = validate_password($password);

// 必要なバリデーションや処理を行う
if ($lastname_error || $firstname_error || $department_error || $email_error || $password_error) {
    // エラーメッセージをセッションに保存
    $_SESSION['errors'] = [
        'lastname' => $lastname_error,
        'firstname' => $firstname_error,
        'department' => $department_error,
        'email' => $email_error,
        'password' => $password_error,
    ];
    $_SESSION['old_input'] = $_POST; // 入力内容も保持
    header('Location: /custom/admin/app/Views/login/sign_up.php');
    exit;
} else {
    global $DB, $CFG;

    // 入力されたメールアドレスが存在するか確認
    $user = $DB->get_record('user', ['email' => $email]);

    if (!$user) {
        try {
            $baseModel = new BaseModel();
            $pdo = $baseModel->getPdo();
            $pdo->beginTransaction();
            
            // $itmt = $pdo->prepare("
            //     INSERT INTO mdl_user (
            //         username, auth, confirmed, lastname, firstname, name, name_kana,
            //         email, password, department, timecreated, timemodified, lang
            //     ) VALUES (
            //         :username , :auth , :confirmed , :lastname, :firstname, :name, :name_kana,
            //         :email, :password, :department, :timecreated, :timemodified, :lang
            //     )
            // ");
            
            
            // $itmt->execute([
            //     ':username' => strtolower($lastname . '.' . $firstname . time()) // 例: john.doe1672901234
            //     , ':auth' => 'manual' // 手動認証
            //     , ':confirmed' => 1
            //     , ':lastname' => $lastname
            //     , ':firstname' => $firstname
            //     , ':name' => $lastname . '.' . $firstname
            //     , ':name_kana' => ''
            //     , ':email' => $email
            //     , ':password' => password_hash($password, PASSWORD_DEFAULT)
            //     , ':department' => $department
            //     , ':timecreated' => time()
            //     , ':timemodified' => time()
            //     , ':lang' => LANG_DEFAULT
            // ]);

            // // IDを取得
            // $user_id = $pdo->lastInsertId();

            // ユーザーを作成
            $new_user = new stdClass();
            $new_user->username = strtolower($firstname . '.' . $lastname . time()); // 例: john.doe1672901234
            $new_user->auth = 'manual'; // 手動認証
            $new_user->confirmed = 1;
            $new_user->lastname = $lastname;
            $new_user->firstname = $firstname;
            $new_user->email = $email;
            $new_user->password = password_hash($password, PASSWORD_DEFAULT);
            $new_user->department = $department;
            $new_user->timecreated = time();
            $new_user->timemodified = time();
            $new_user->lang = LANG_DEFAULT;
            $new_user->name = $lastname . ' ' . $firstname; // 氏名（姓 名）
            $new_user->name_kana = ''; // 仮で入れる or フォーム入力で受け取る
            $user_id = $DB->insert_record('user', $new_user);

            // 管理者ロールを割り当てる
            $admin_role = $DB->get_record('role', ['shortname' => 'coursecreator']); // もしくは 'admin'
            $context = context_system::instance(); // システムコンテキスト
            role_assign($admin_role->id, $user_id, $context->id);

            $siteadmins = explode(',', get_config('moodle', 'siteadmins'));
            
            // 管理者IDがすでに存在しない場合のみ追加
            if (!in_array($user_id, $siteadmins)) {
                $siteadmins[] = $user_id;
                $value = implode(',', $siteadmins);
                set_config('siteadmins', $value);
            } else {
                throw new Exception("Error Processing Request", 1);
            }

            $_SESSION['result_message'] = '管理者として正常に登録されました。';
        } catch (Exception $e) {
            $_SESSION['result_message'] = 'エラーが発生しました。再登録してください。';
        }
    } else {
        $_SESSION['result_message'] = '入力したメールアドレスは登録済みです。';
    }
    header('Location: /custom/admin/app/Views/login/result.php');
    exit;
}
?>