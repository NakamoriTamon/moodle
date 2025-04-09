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
        case 'update_payment_method':
            $controller->updatePaymentMethod();
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

class MypageUpdateController
{
    /**
     * マイページ情報更新のメソッド
     */
    public function updateUserInfo()
    {
        global $DB;
        global $USER;

        $user_id = $_SESSION['USER']->id;
        $name_size = 50;
        $name = htmlspecialchars(required_param('name', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
        $_SESSION['errors']['name'] = validate_text($name, 'お名前', $name_size, true);
        $name_kana = htmlspecialchars(required_param('name_kana', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
        if (!empty($name_kana)) {
            $name_kana = preg_replace('/[\x{3000}\s]/u', '', $name_kana);
        }
        $_SESSION['errors']['name_kana'] = validate_kana($name_kana, $name_size);
        $city = htmlspecialchars(required_param('city', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
        $_SESSION['errors']['city'] = validate_select($city, 'お住いの都道府県', true);

        $email = required_param('email', PARAM_TEXT);
        $_SESSION['errors']['email'] = validate_custom_email($email);

        $user_list = $DB->get_records_select(
            'user',
            'email = :email AND id != :user_id AND deleted = 0',
            ['email' => $email, 'user_id' => $user_id]
        );

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

        $child_name = htmlspecialchars(required_param('child_name', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
        $_SESSION['errors']['child_name'] = validate_text($child_name, 'お子様の氏名', $name_size, false);
        $phone = htmlspecialchars(required_param('phone', PARAM_TEXT), ENT_QUOTES, 'UTF-8');

        // ユーザー重複チェック(管理者含む)
        $timestamp_format = date("Y-m-d H:i:s", strtotime($birthday));
        $user_list = $DB->get_records('user', ['phone1' => $phone, 'birthday' => $timestamp_format, 'name_kana' => $name_kana, 'deleted' => 0]);
        if (!empty($user_list)) {
            foreach ($user_list as $user) {
                $general_user = $DB->get_record('role_assignments', ['userid' => $user->id, 'roleid' => 7]);
                if ($general_user && $USER->id != $user->id) {
                    $email_error = '既に使用されています。';
                    $_SESSION['errors']['email'] = $email_error;
                    break;
                }
            }
        }

        $password = htmlspecialchars(required_param('password', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
        if (!empty($password)) {
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
        $_SESSION['errors']['description'] = validate_textarea($description, '備考', false, 200);

        $current_date = new DateTime();
        $birthday_obj = new DateTime($birthday);
        $age = $current_date->diff($birthday_obj)->y;

        // 保護者情報
        $guardian_name = "";
        $guardian_email = "";
        $guardian_phone = "";
        if ($age < 13) {
            $guardian_name = htmlspecialchars(required_param('guardian_name', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
            $_SESSION['errors']['guardian_name'] = validate_text($guardian_name, '保護者の氏名', $name_size, true);
            $guardian_email = htmlspecialchars(required_param('guardian_email', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
            $_SESSION['errors']['guardian_email'] = validate_custom_email($guardian_email, '保護者の');
            $guardian_phone = htmlspecialchars(required_param('guardian_phone', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
            if (empty($guardian_phone)) {
                $_SESSION['errors']['guardian_phone'] = '保護者電話番号は必須です。';
            } else {
                if (strlen($guardian_phone) > 15) {
                    $_SESSION['errors']['guardian_phone'] = '無効な電話番号です。';
                }
                if (!preg_match('/^\d+$/', $guardian_phone)) {
                    $_SESSION['errors']['guardian_phone'] = '無効な電話番号です。';
                }
            }
        }

        // $notification_kbn = htmlspecialchars(optional_param('notification_kbn', 1, PARAM_TEXT));

        $result = false;
        // エラーがある場合
        if (
            $_SESSION['errors']['name']
            || $_SESSION['errors']['name_kana']
            || $_SESSION['errors']['city']
            || $_SESSION['errors']['email']
            || $_SESSION['errors']['password']
            || $_SESSION['errors']['phone']
            || $_SESSION['errors']['birthday']
            || $_SESSION['errors']['description']
            || $_SESSION['errors']['child_name']
        ) {
            $result = true;
        }
        if ($age < 13) {
            if (
                $_SESSION['errors']['guardian_name']
                || $_SESSION['errors']['guardian_email']
                || $_SESSION['errors']['guardian_phone']
            ) {
                $result = true;
            }
        }
        // バリデーションチェックの結果
        if ($result) {
            $_SESSION['old_input'] = $_POST; // 入力内容も保持

            header('Location: /custom/app/Views/mypage/index.php#user_form');
            return;
        }

        try {
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
                $data->guardian_phone = $guardian_phone;
                $data->child_name = $child_name;
                
                // 「お子様の氏名」が入力されているかどうかでguardian_kbnを設定
                $data->guardian_kbn = !empty($child_name) ? 1 : 0;

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
            error_log('マイページ情報更新エラー: ' . $e->getMessage());
            $_SESSION['message_error'] = '登録に失敗しました';
            header('Location: /custom/app/Views/mypage/index.php#user_form');
        }
    }

    /**
     * 適塾情報の更新メソッド
     */
    public function updateMembershipInfo()
    {
        global $DB;

        $user_id = $_SESSION['USER']->id;
        $name_size = 50;
        $size = 500;
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

                header('Location: /custom/app/Views/mypage/index.php#tekijuku_form');
                exit;
            }
        }

        try {
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
                $data->is_published = $is_published;
                $data->department = $department;
                $data->major = $major;
                $data->official = $official;
                $data->is_university_member = $is_university_member;

                $DB->update_record_raw('tekijuku_commemoration', $data);

                $pdo->commit();
                $_SESSION['tekijuku_success'] = '登録が完了しました';
                header('Location: /custom/app/Views/mypage/index.php#tekijuku_form');
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('適塾情報更新エラー: ' . $e->getMessage());
            $_SESSION['message_error'] = '登録に失敗しました';
            header('Location: /custom/app/Views/mypage/index.php#tekijuku_form');
        }
    }

    /**
     * 支払い方法変更メソッド
     */
    public function updatePaymentMethod()
    {
        global $DB;
        global $USER;
        global $CFG;

        $user_id = $_SESSION['USER']->id;
        $id = htmlspecialchars(required_param('tekijuku_commemoration_id', PARAM_INT), ENT_QUOTES, 'UTF-8');

        $payment_method = htmlspecialchars($_POST['payment_method']);
        if (empty($payment_method)) {
            $user = $DB->get_record('tekijuku_commemoration', ['fk_user_id' => $user_id, 'is_delete' => 0]);
            if (!empty($user->payment_method)) {
                $payment_method = $user->payment_method;
            } else {
                $_SESSION['errors']['payment_method'] = '支払方法は必須です。';
            }
        }

        $paid_status = $_POST['paid_status'];
        $is_subscription = htmlspecialchars(required_param('is_subscription', PARAM_INT), ENT_QUOTES, 'UTF-8');

        foreach ($_SESSION['errors'] as $error) {
            if (!empty($error)) {
                $_SESSION['old_input'] = $_POST;

                header('Location: /custom/app/Views/mypage/index.php#payment_form');
                exit;
            }
        }

        // 決済中に変更しようとした場合はエラー
        if (empty($paid_status) || $paid_status == PAID_STATUS['PROCESSING']) {
            $_SESSION['message_membership_error'] = '支払方法の更新に失敗しました';
            header('Location: /custom/app/Views/mypage/index.php#payment_form');
            exit;
        }

        $amount = $_POST['price'];
        $payment_mode = "customer_payment";
        $payment_status = PAID_STATUS['PROCESSING'];

        // 決済済の場合はここを通る
        if ($paid_status == PAID_STATUS['COMPLETED'] || $paid_status == PAID_STATUS['SUBSCRIPTION_PROCESSING']) {
            $amount = 0;
            $payment_mode = "customer";
            $payment_status = PAID_STATUS['SUBSCRIPTION_PROCESSING'];

            // 定額課金解除時はここを通る
            if ($is_subscription != IS_SUBSCRIPTION['SUBSCRIPTION_ENABLED']) {
                try {
                    if (isloggedin() && isset($_SESSION['USER'])) {
                        // 接続情報取得
                        $baseModel = new BaseModel();
                        $pdo = $baseModel->getPdo();
                        $pdo->beginTransaction();

                        $data = new stdClass();
                        $data->id = (int)$id;
                        $data->external_payment_reference = null;
                        $data->paid_status = PAID_STATUS['COMPLETED'];
                        $data->is_subscription = $is_subscription;

                        $DB->update_record_raw('tekijuku_commemoration', $data);
                        $pdo->commit();

                        $_SESSION['message_membership_success'] = '支払方法を更新しました';
                        header('Location: /custom/app/Views/mypage/index.php#payment_form');
                        exit;
                    }
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $_SESSION['message_membership_error'] = '支払方法の更新に失敗しました';
                    header('Location: /custom/app/Views/mypage/index.php#payment_form');
                    exit;
                }
            }
            // 定額課金時はここを通る
            if ($is_subscription == IS_SUBSCRIPTION['SUBSCRIPTION_ENABLED']) {
                try {
                    if (isloggedin() && isset($_SESSION['USER'])) {
                        // 接続情報取得
                        $baseModel = new BaseModel();
                        $pdo = $baseModel->getPdo();
                        $pdo->beginTransaction();

                        $data = new stdClass();
                        $data->id = (int)$id;
                        $data->payment_method = PAYMENT_CREDIT;
                        $data->paid_status = PAID_STATUS['SUBSCRIPTION_PROCESSING'];
                        $data->is_subscription = $is_subscription;

                        $DB->update_record_raw('tekijuku_commemoration', $data);

                        // サブスクリプションの場合はcustomer_paymentモードを使用
                        $data = [
                            'payment_types' => [PAYMENT_METHOD_LIST[$payment_method]], // 利用可能な決済手段
                            'amount' => $amount,
                            'currency' => 'JPY',
                            'external_order_num' => uniqid(),
                            'return_url' => $CFG->wwwroot . '/custom/app/Views/mypage/index.php', // 決済成功後のリダイレクトURL
                            'cancel_url' => $CFG->wwwroot . '/custom/app/Views/mypage/index.php', // キャンセル時のリダイレクトURL
                            'metadata' => [
                                'tekujuku_id' => (string)$id,
                                'payment_method_type' => (string)$payment_method,
                                'paid_status' => (string)$paid_status,
                            ],
                            'mode' => $payment_mode, // customerモードを指定
                            'email' => $USER->email,
                        ];

                        $_SESSION['payment_method_type'] = $payment_method;

                        // ヘッダーの設定
                        $headers = [
                            'Authorization: Basic ' . base64_encode(KOMOJU_API_KEY),
                            'Content-Type: application/json',
                        ];

                        // cURLオプションの設定
                        $ch = curl_init(KOMOJU_ENDPOINT);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // レスポンスを文字列で返す
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // ヘッダーを設定
                        curl_setopt($ch, CURLOPT_POST, true); // POSTメソッド
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // POSTデータ

                        // 結果を取得
                        $response = curl_exec($ch);

                        // ステータスコードの取得
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);

                        if ($response) {
                            $result = json_decode($response, true);
                        }

                        // セッションURLが取得できたらリダイレクト
                        if (isset($result['session_url'])) {
                            $_SESSION['message_membership_success'] = '支払方法を更新しました';
                            header("Location: " . $result['session_url']);
                            $pdo->commit();
                            unset($_SESSION['old_input']);
                            exit;
                        } else {
                            $_SESSION['message_error'] = "決済ページ取得に失敗しました";
                            header('Location: /custom/app/Views/mypage/index.php#payment_form');
                            exit;
                        }
                        $_SESSION['payment_success'] = '支払方法の更新が完了しました';
                        header('Location: /custom/app/Views/mypage/index.php#payment_form');
                    }
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    error_log('適塾情報更新エラー: ' . $e->getMessage());
                    $_SESSION['message_membership_error'] = '支払方法の更新に失敗しました';
                    header('Location: /custom/app/Views/mypage/index.php#payment_form');
                }
            }
        }

        // 未決済の場合下記を通る
        try {
            if (isloggedin() && isset($_SESSION['USER'])) {
                // 接続情報取得
                $baseModel = new BaseModel();
                $pdo = $baseModel->getPdo();
                $pdo->beginTransaction();

                $data = new stdClass();
                $data->id = (int)$id;
                $data->payment_method = $payment_method;
                $data->paid_status = $payment_status;
                $data->payment_start_date = date('Y-m-d H:i:s');
                $data->is_subscription = $is_subscription;

                $DB->update_record_raw('tekijuku_commemoration', $data);


                if ($is_subscription == IS_SUBSCRIPTION['SUBSCRIPTION_ENABLED']) {
                    // サブスクリプションの場合はcustomer_paymentモードを使用
                    $data = [
                        'payment_types' => [PAYMENT_METHOD_LIST[$payment_method]], // 利用可能な決済手段
                        'amount' => $amount,
                        'currency' => 'JPY',
                        'external_order_num' => uniqid(),
                        'return_url' => $CFG->wwwroot . '/custom/app/Views/mypage/index.php', // 決済成功後のリダイレクトURL
                        'cancel_url' => $CFG->wwwroot . '/custom/app/Views/mypage/index.php', // キャンセル時のリダイレクトURL
                        'metadata' => [
                            'tekujuku_id' => (string)$id,
                            'payment_method_type' => (string)$payment_method,
                            'paid_status' => (string)$paid_status,
                        ],
                        'mode' => $payment_mode, // customerモードを指定
                        'email' => $USER->email,
                    ];
                } else {
                    // 通常の支払いの場合は従来のpaymentモード
                    $data = [
                        'payment_types' => [PAYMENT_METHOD_LIST[$payment_method]], // 利用可能な決済手段
                        'amount' => $amount,
                        'currency' => 'JPY',
                        'external_order_num' => uniqid(),
                        'return_url' => $CFG->wwwroot . '/custom/app/Views/mypage/index.php', // 決済成功後のリダイレクトURL
                        'cancel_url' => $CFG->wwwroot . '/custom/app/Views/mypage/index.php', // キャンセル時のリダイレクトURL
                        'metadata' => [
                            'tekujuku_id' => (string)$id,
                            'payment_method_type' => (string)$payment_method,
                        ],
                    ];
                }

                $_SESSION['payment_method_type'] = $payment_method;

                // ヘッダーの設定
                $headers = [
                    'Authorization: Basic ' . base64_encode(KOMOJU_API_KEY),
                    'Content-Type: application/json',
                ];

                // cURLオプションの設定
                $ch = curl_init(KOMOJU_ENDPOINT);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // レスポンスを文字列で返す
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // ヘッダーを設定
                curl_setopt($ch, CURLOPT_POST, true); // POSTメソッド
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // POSTデータ

                // 結果を取得
                $response = curl_exec($ch);

                // ステータスコードの取得
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($response) {
                    $result = json_decode($response, true);
                }

                // セッションURLが取得できたらリダイレクト
                if (isset($result['session_url'])) {
                    $_SESSION['message_membership_success'] = '支払方法を更新しました';
                    header("Location: " . $result['session_url']);
                    $pdo->commit();
                    unset($_SESSION['old_input']);
                    exit;
                } else {
                    $_SESSION['message_error'] = "決済ページ取得に失敗しました";
                    header('Location: /custom/app/Views/mypage/index.php#payment_form');
                    exit;
                }
                $_SESSION['payment_success'] = '支払方法の更新が完了しました';
                header('Location: /custom/app/Views/mypage/index.php#payment_form');
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['message_membership_error'] = '支払方法の更新に失敗しました';
            header('Location: /custom/app/Views/mypage/index.php#payment_form');
        }
    }

    // お知らせメール設定API
    public function changeEmailNotifications()
    {
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
