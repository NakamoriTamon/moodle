<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');
require_once($CFG->dirroot . '/custom/app/Models/TekijukuCommemorationModel.php');

// 登録内容
$values = $_SESSION['old_input'];
$type_code = (int)$values['type_code'];
$name = $values['name'];
$kana = $values['kana'];
$post_code = (int)$values['post_code'];
$address = $values['address'];
$tell_number = $values['combine_tell_number'];
$email = $values['email'];
$unit = (int)$values['unit'];
$price = (int)$values['price'];
$payment_method = (int)$values['payment_method'];
$note = $values['note'];
$department = $values['department'];
$is_university_member = (int)$values['is_university_member'];
$major = $values['major'];
$official = $values['official'];
$is_published = (int)$values['is_published'];
$is_subscription = (int)$values['is_subscription'];
try {
    // 既にアカウントを作成しているか確認
    $fk_user_id = (int)$_SESSION['USER']->id;
    $exist_tekijuku_account = $DB->get_record('tekijuku_commemoration', ['number' => $fk_user_id]);

    if (!empty($exist_tekijuku_account)) {
        $tekijuku_commemoration_model = new TekijukuCommemorationModel();
        $max_number = $tekijuku_commemoration_model->get_tekijuku_max_number();
        $number = !empty($max_number['max_number']) ? $max_number['max_number'] + 1 :  TEKIJUKU_RENUMBERING_NUM;
    } else {
        $number = $fk_user_id;
    }

    $transaction = $DB->start_delegated_transaction();
    $tekijuku_commemoration = new stdClass();
    $tekijuku_commemoration->created_at = date('Y-m-d H:i:s');
    $tekijuku_commemoration->updated_at = date('Y-m-d H:i:s');
    $tekijuku_commemoration->number = $number;
    $tekijuku_commemoration->type_code = $type_code;
    $tekijuku_commemoration->name = $name;
    $tekijuku_commemoration->kana = $kana;
    $tekijuku_commemoration->post_code = $post_code;
    $tekijuku_commemoration->address = $address;
    $tekijuku_commemoration->tell_number = $tell_number;
    $tekijuku_commemoration->email = $email;
    $tekijuku_commemoration->payment_method = $payment_method;
    $tekijuku_commemoration->note = $note;
    $tekijuku_commemoration->is_published = $is_published;
    $tekijuku_commemoration->is_subscription = $is_subscription;
    $tekijuku_commemoration->fk_user_id = $fk_user_id;

    $tekijuku_commemoration->department = $department;
    $tekijuku_commemoration->major = $major;
    $tekijuku_commemoration->official = $official;
    $tekijuku_commemoration->unit = $unit;
    $tekijuku_commemoration->price = $price;
    $tekijuku_commemoration->is_university_member = $is_university_member;
    $tekijuku_commemoration->paid_status = PAID_STATUS['PROCESSING']; // 決済中
    $tekijuku_commemoration->payment_start_date = date('Y-m-d H:i:s'); // 決済開始時刻

    $id = $DB->insert_record_raw('tekijuku_commemoration', $tekijuku_commemoration, true);
    $amount = $type_code === 1 ? 2000 : 10000;
    // 決済データ（サンプル）
    if ($is_subscription == IS_SUBSCRIPTION['SUBSCRIPTION_ENABLED']) {
        // サブスクリプションの場合はcustomer_paymentモードを使用
        $data = [
            'payment_types' => [$payment_method_list[$payment_method]], // 利用可能な決済手段
            'amount' => $amount,
            'currency' => 'JPY',
            'external_order_num' => uniqid(),
            'return_url' => $CFG->wwwroot . '/custom/app/Views/tekijuku/complete.php', // 決済成功後のリダイレクトURL
            'cancel_url' => $CFG->wwwroot . '/custom/app/Views/tekijuku/registrate.php', // キャンセル時のリダイレクトURL
            'metadata' => [
                'tekujuku_id' => (string)$id,
                'payment_method_type' => (string)$payment_method,
            ],
            'mode' => 'customer_payment', // customerモードを指定
            'email' => $email,
        ];
    } else {
        // 通常の支払いの場合は従来のpaymentモード
        $data = [
            'payment_types' => [$payment_method_list[$payment_method]], // 利用可能な決済手段
            'amount' => $amount,
            'currency' => 'JPY',
            'external_order_num' => uniqid(),
            'return_url' => $CFG->wwwroot . '/custom/app/Views/tekijuku/complete.php', // 決済成功後のリダイレクトURL
            'cancel_url' => $CFG->wwwroot . '/custom/app/Views/tekijuku/registrate.php', // キャンセル時のリダイレクトURL
            'metadata' => [
                'tekujuku_id' => (string)$id,
                'payment_method_type' => (string)$payment_method,
            ],
        ];
    }

    $_SESSION['payment_method_type'] = $payment_method;

    // ヘッダーの設定
    $headers = [
        'Authorization: Basic ' . base64_encode($komoju_api_key),
        'Content-Type: application/json',
    ];

    // cURLオプションの設定
    $ch = curl_init($komoju_endpoint);
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
        header("Location: " . $result['session_url']);
        $transaction->allow_commit();
        unset($_SESSION['old_input']);
        exit;
    } else {
        throw new Exception("決済ページ取得に失敗しました");
    }
    exit;
} catch (Exception $e) {
    var_dump($e);
    die();
    error_log('適塾登録エラー: ' . $e->getMessage());
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        error_log('適塾登録ロールバックエラー: ' . $rollbackException->getMessage());
        $_SESSION['message_error'] = '登録に失敗しました';
        redirect('/custom/app/Views/tekijuku/registrate.php');
        exit;
    }
}
