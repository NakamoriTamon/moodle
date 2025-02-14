<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventCustomFieldModel.php');
require_once('/var/www/html/moodle/custom/app/Models/PaymentTypeModel.php');
require_once($CFG->libdir . '/filelib.php');

// ログイン判定
if (isloggedin() && isset($_SESSION['USER'])) {
    $user_id = $_SESSION['USER']->id;
} else {
    $_SESSION['message_error'] = 'ユーザ情報が取得できませんでした。ログインしてください。';
    header('Location: /custom/app/Views/front/index.php');
    return;
}

$action = required_param('action', PARAM_TEXT);
$eventId = htmlspecialchars(required_param('event_id', PARAM_INT), ENT_QUOTES, 'UTF-8');
$eventModel = new eventModel();
$event = $eventModel->getEventById($eventId);
// イベント情報がなかった場合
if(is_null($event)) {
    header('Location: /custom/app/Views/front/index.php');
    return;
}
$name = htmlspecialchars(required_param('name', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$kana = htmlspecialchars(required_param('kana', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars(required_param('email', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
// 枚数
$ticket = htmlspecialchars(required_param('ticket', PARAM_INT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['ticket'] = validate_int($ticket, '枚数', true); // バリデーションチェック
$price =  htmlspecialchars(required_param('price', PARAM_INT), ENT_QUOTES, 'UTF-8');
if($price != $ticket * $event['participation_fee']) {
    $_SESSION['message_error'] = '支払い料金が変更されました。ご確認の上、再度お申し込みしてください。';
    header('Location: /custom/app/Views/front/event_application.php?id=' . $eventId);
    return;
}
$triggers = htmlspecialchars(required_param('triggers', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['triggers'] = validate_array($triggers, '', true) ? "どこで本イベントを知ったか選択してください。" : null;
if(is_null($_SESSION['errors']['triggers'])) {
    // カンマで分割して配列にする
    $triggerArray = explode(',', $triggers);
} else {
    $triggerArray = [];
}
$trigger_other = htmlspecialchars(required_param('trigger_other', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['trigger_other'] = validate_textarea($trigger_other, 'その他', false);
$pay_method = htmlspecialchars(required_param('pay_method', PARAM_INT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['pay_method'] = validate_select($pay_method, '支払方法', true); // バリデーションチェック
$note = htmlspecialchars(optional_param('note', '', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['note'] = validate_textarea($note, '備考欄', false);
$companion_mails = array_map('htmlspecialchars', optional_param_array('companion_mails', [], PARAM_RAW));
$companion_mails_string = implode(',', $companion_mails);
$_SESSION['errors']['companion_mails'] = null;
foreach($companion_mails as $companion_mail) {
    $_SESSION['errors']['companion_mails'] = validate_custom_email($email) ? "受講する人のメールアドレスを入力してください。" : null;
    break;
}
$event_customfield_id = htmlspecialchars(optional_param('event_customfield_id', 0, PARAM_INT), ENT_QUOTES, 'UTF-8');
$guardian_kbn = htmlspecialchars(optional_param('guardian_kbn', 0, PARAM_INT), ENT_QUOTES, 'UTF-8');
$contact_phone = $_SESSION['USER']->phone1;
$applicant_kbn = htmlspecialchars(optional_param('applicant_kbn', 0, PARAM_INT), ENT_QUOTES, 'UTF-8');
$guardian_name = htmlspecialchars(optional_param('guardian_name', '', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$guardian_kana = htmlspecialchars(optional_param('guardian_kana', '', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$guardian_email = htmlspecialchars(optional_param('guardian_email', '', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$notification_kbn = htmlspecialchars(optional_param('notification_kbn', 0, PARAM_INT), ENT_QUOTES, 'UTF-8');

if(empty($guardian_kbn)) {
    $_SESSION['errors']['guardian_name'] = validate_text($guardian_name, '保護者名', 225, true);
    $_SESSION['errors']['guardian_kana'] = validate_text($guardian_kana, '保護者名フリガナ', 225, true);
    $_SESSION['errors']['guardian_email'] = validate_custom_email($guardian_email, '保護者の');
} else {
    $_SESSION['errors']['guardian_name'] = null;
    $_SESSION['errors']['guardian_kana'] = null;
    $_SESSION['errors']['guardian_email'] = null;
}
$event_customfield_category_id =  htmlspecialchars(required_param('event_customfield_category_id', PARAM_INT), ENT_QUOTES, 'UTF-8');
$eventCustomFieldModel = new eventCustomFieldModel();
$fieldList = [];
$fieldInputDataList = [];
if(!empty($event_customfield_category_id)) {
    $fieldList = $eventCustomFieldModel->getCustomFieldById($event_customfield_category_id);
    foreach ($fieldList as $fields) {
        $input_value = null;
        $tag_name = $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'];
        if ($fields['field_type'] == 3) {
            $input_data = optional_param($tag_name, '', PARAM_TEXT);
            $input_data = explode(",", $input_data);
            $options = explode(",", $fields['selection']);
            
            foreach ($options as $i => $option) {
                if(in_array($i+1, $input_data)) {
                    if($i == 0) {
                        $input_value = $option;
                        continue;
                    }
                    $input_value .= ',' . $option;
                }
            }
        } elseif ($fields['field_type'] == 4) {
            $input_value = optional_param($tag_name, 0, PARAM_INT);
            $options = explode(",", $fields['selection']);
            foreach ($options as $i => $option) {
                if($i+1 == $input_value) {
                    $input_value = $option;
                    break;
                }
            }
        } else {
            $input_value = optional_param($tag_name, '', PARAM_TEXT);
        }

        if(!empty($input_value)) {
            $fieldInputDataList[] = ['event_customfield_id' => $fields['id'], 'field_type' => $fields['field_type'], 'input_data' => $input_value];
        }
    }
}

$result = false;
// エラーがある場合
if($_SESSION['errors']['ticket']
|| $_SESSION['errors']['pay_method']
|| $_SESSION['errors']['triggers']
|| $_SESSION['errors']['trigger_other']
|| $_SESSION['errors']['note']
|| $_SESSION['errors']['companion_mails']
|| $_SESSION['errors']['guardian_name']
|| $_SESSION['errors']['guardian_kana']
|| $_SESSION['errors']['guardian_email']) {
    $result = true;
}

if ($action === 'register' || $result) {
    // 申込登録処理
    try {
        $baseModel = new BaseModel();
        $pdo = $baseModel->getPdo();
        $pdo->beginTransaction();
        
        // 1. イベントのcapacityと現在のチケット枚数をロック
        $stmt = $pdo->prepare("
            SELECT e.capacity, COALESCE(SUM(a.ticket_count), 0) AS current_count
            FROM mdl_event e
            LEFT JOIN mdl_event_application a ON e.id = a.event_id
            WHERE e.id = :event_id
            FOR UPDATE
        ");
        $stmt->execute([':event_id' => $eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            throw new Exception("イベントが見つかりません。");
        }

        $capacity = (int)$event['capacity'];
        $currentCount = (int)$event['current_count'];
        // 2. 定員超過のチェック 受付済みのチケット枚数 + 注文しているチケット枚数
        if (($currentCount + $ticket) <= $capacity) {
            $itmt = $pdo->prepare("
                INSERT INTO mdl_event_application (
                    event_id, user_id, event_custom_field_id, field_value
                    , name, name_kana, email, ticket_count, price, pay_method
                    , request_mail_kbn, applicant_kbn, application_date
                    , note, contact_phone, guardian_name, guardian_name_kana
                    , guardian_email, companion_mails
                ) VALUES (
                    :event_id , :user_id , :event_custom_field_id , :field_value
                    , :name , :name_kana , :email , :ticket_count , :price , :pay_method
                    , :request_mail_kbn , :applicant_kbn , CURRENT_TIMESTAMP
                    , :note , :contact_phone , :guardian_name , :guardian_name_kana
                    , :guardian_email , :companion_mails
                )
            ");
            
            $itmt->execute([
                ':event_id' => $eventId
                , ':user_id' => $user_id
                , ':event_custom_field_id' => $event_customfield_category_id
                , ':field_value' => ''
                , ':name' => $name
                , ':name_kana' => $kana
                , ':email' => $email
                , ':ticket_count' => $ticket
                , ':price' => $price
                , ':pay_method' => $pay_method
                , ':request_mail_kbn' => $notification_kbn
                , ':applicant_kbn' => $applicant_kbn
                , ':note' => $note
                , ':contact_phone' => $contact_phone
                , ':guardian_name' => $guardian_name
                , ':guardian_name_kana' => $guardian_kana
                , ':guardian_email' => $guardian_email
                , ':companion_mails' => $companion_mails_string
            ]);
    
            
            // mdl_eventの挿入IDを取得
            $eventApplicationId = $pdo->lastInsertId();
    
            // 知った経由　mdl_event_application_cognition
            $itmt2 = $pdo->prepare("
                INSERT INTO mdl_event_application_cognition (created_at, updated_at, event_application_id, cognition_id, note) 
                VALUES (CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :event_application_id, :cognition_id, :note)
            ");
            foreach ($triggerArray as $cognition_id) {
                $itmt2->execute([
                    ':event_application_id' => $eventApplicationId,
                    ':cognition_id' => trim($cognition_id), // 空白を除去
                    ':note' => $trigger_other
                ]);
            }
            
            // カスタムフィールドがある場合
            if(!empty($event_customfield_category_id)) {
                foreach($fieldInputDataList as $fieldInputData) {
                    $stmt3 = $pdo->prepare("
                        INSERT INTO mdl_event_application_customfield (created_at, updated_at, event_application_id, event_customfield_id, field_type, input_data) 
                        VALUES (CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :event_application_id, :event_customfield_id, :field_type, :input_data)
                    ");
                    $stmt3->execute([
                        ':event_application_id' => $eventApplicationId,
                        ':event_customfield_id' => $fieldInputData['event_customfield_id'],
                        ':field_type' => $fieldInputData['field_type'],
                        ':input_data' => $fieldInputData['input_data']
                    ]);
                }
            }
            
            $stmt = $pdo->prepare("
                UPDATE mdl_user
                SET 
                    notification_kbn = :notification_kbn
                WHERE id = :id
            ");

            $stmt->execute([
                ':notification_kbn' => $notification_kbn,
                ':id' => $user_id // 一意の識別子をWHERE条件として設定
            ]);

            $paymentTypeModel = new PaymentTypeModel();
            $paymentType = $paymentTypeModel->getPaymentTypesById($pay_method);
            $type = $paymentType['payment_type'];
            
            // 決済データ（サンプル）
            $data = [
                'payment_types' => [$type], // 利用可能な決済手段
                'amount' => $price,
                'currency' => 'JPY',
                'external_order_num' => uniqid(),
                'return_url' => $CFG->wwwroot . '/custom/app/Views/front/event_application.php?id=' . $eventId, // 決済成功後のリダイレクトURL
                'cancel_url' => $CFG->wwwroot . '/custom/app/Views/front/event_application.php?id=' . $eventId, // キャンセル時のリダイレクトURL
                'metadata' => [
                    'event_application_id' => $eventApplicationId,
                ],
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
                $pdo->commit();
                // header("Location: " . $result['session_url']);
                $redirect_url = $result['session_url'];

                echo "<script>window.location.href='$redirect_url';</script>";
                exit;
            } else {
                throw new Exception("決済ページ取得に失敗しました");
            }
        } else {
            // 定員超過時はロールバック
            $pdo->rollBack();
            $_SESSION['message_error'] = '定員を超えているため、申込できません。';
            header('Location: /custom/app/Views/front/event_application.php?id=' . $eventId);
            exit;
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
        header('Location: /custom/app/Views/front/event_application.php?id=' . $eventId);
        exit;
    }

} else {
    $_SESSION['message_error'] = '登録に失敗しました。再度情報を入力してください。: ' . $e->getMessage();
    // 修正(申込登録画面に戻る)
    // 入力画面に戻る
    $SESSION->formdata = [
        'id' => $eventId
        , 'ticket' => $ticket
        , 'trigger_other' => $trigger_other
        , 'pay_method' => $pay_method
        , 'request_mail_kbn' => $notification_kbn
        , 'triggers' => $triggers
        , 'note' => $note
        , 'guardian_name' => $guardian_name
        , 'guardian_name_kana' => $guardian_name_kana
        , 'guardian_email' => $guardian_email
        , 'companion_mails' => $companion_mails];
    redirect(new moodle_url('/custom/app/Views/front/event_application.php?id=' . $eventId));
}
