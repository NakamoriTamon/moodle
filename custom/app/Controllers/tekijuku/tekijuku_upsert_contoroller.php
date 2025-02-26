<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');

// 登録内容
$values = $_SESSION['old_input'];
$type_code = (int)$values['type_code'];
$name = $values['name'];
$kana = $values['kana'];
$sex = (int)$values['sex'];
$post_code = (int)$values['post_code'];
$address = $values['address'];
$tell_number = $values['combine_tell_number'];
$email = $values['email'];
$payment_method = (int)$values['payment_method'];
$note = $values['note'];
$is_published = (int)$values['is_published'];
$is_subscription = (int)$values['is_subscription'];
try {
    // 将来的にはユニークにするので下記制約は不要となる(確認中)
    $max_number = $DB->get_record_sql("
        SELECT number FROM {tekijuku_commemoration} 
        ORDER BY number DESC 
        LIMIT 1
    ");

    $max_number = $max_number->number + 1;
    $fk_user_id = (int)$_SESSION['USER']->id;
    // ゼロ埋め　sprintf('%08d', $max_number)

    $transaction = $DB->start_delegated_transaction();
    $tekijuku_commemoration = new stdClass();
    $tekijuku_commemoration->created_at = date('Y-m-d H:i:s');
    $tekijuku_commemoration->updated_at = date('Y-m-d H:i:s');
    $tekijuku_commemoration->number = $max_number;
    $tekijuku_commemoration->type_code = $type_code;
    $tekijuku_commemoration->name = $name;
    $tekijuku_commemoration->kana = $kana;
    $tekijuku_commemoration->sex = $sex;
    $tekijuku_commemoration->post_code = $post_code;
    $tekijuku_commemoration->address = $address;
    $tekijuku_commemoration->tell_number = $tell_number;
    $tekijuku_commemoration->email = $email;
    $tekijuku_commemoration->payment_method = $payment_method;
    $tekijuku_commemoration->note = $note;
    $tekijuku_commemoration->is_published = $is_published;
    $tekijuku_commemoration->is_subscription = $is_subscription;
    $tekijuku_commemoration->paid_date = date('Y-m-d H:i:s');
    $tekijuku_commemoration->fk_user_id = $fk_user_id;

    $id = $DB->insert_record('tekijuku_commemoration', $tekijuku_commemoration);
    $amount = $type_code === 1 ? 2000 : 10000;
    // 決済データ（サンプル）
    $data = [
        'payment_types' => [$payment_method_list[$payment_method]], // 利用可能な決済手段
        'amount' => $amount,
        'currency' => 'JPY',
        'external_order_num' => uniqid(),
        'return_url' => $CFG->wwwroot . '/custom/app/Views/mypage/index.php', // 決済成功後のリダイレクトURL
        'cancel_url' => $CFG->wwwroot . '/custom/app/Views/tekijuku/registrate.php', // キャンセル時のリダイレクトURL
    ];

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

            // cURLオプションの設定
//     $ch2 = curl_init('https://komoju.com/api/v1/sessions/' . $result['id'] . '/pay');
//     $data = [
//         'payment_types' => [$payment_method_list[$payment_method]], // 利用可能な決済手段
//         'payment_details' => [
//             'type' => 'credit_card', // 支払い方法のタイプ
//             'number' => '4111111111111111', // カード番号
//             'name' => 'TEST CARD', // カード所有者の名前（任意）
//             'month' => '12', // 有効期限の月
//             'year' => '25', // 有効期限の年（下2桁）
//             'verification_value' => '123', // CVV（任意）
//         ]
//     ];
//     curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true); // レスポンスを文字列で返す
//     curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers); // ヘッダーを設定
//     curl_setopt($ch2, CURLOPT_POST, true); // POSTメソッド
//     curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($data)); // POSTデータ

//     $response2 = curl_exec($ch2);
// var_dump($response2);
        $transaction->allow_commit();
        unset($_SESSION['old_input']);
        // header("Location: " . $result['session_url']);
        exit;
    } else {
        throw new Exception("決済ページ取得に失敗しました");
    }
    exit;
} catch (Exception $e) {
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = '登録に失敗しました';
        redirect('/custom/app/Views/tekijuku/registrate.php');
        exit;
    }
}
