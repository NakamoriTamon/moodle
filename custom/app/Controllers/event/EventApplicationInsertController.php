<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventCustomFieldModel.php');
require_once($CFG->libdir . '/filelib.php');

$action = required_param('action', PARAM_TEXT);
$eventId = htmlspecialchars(required_param('event_id', PARAM_INT), ENT_QUOTES, 'UTF-8');
$user_id = $_SESSION['USER']->id;
$name = htmlspecialchars(required_param('name', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$kana = htmlspecialchars(required_param('kana', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars(required_param('email', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$ticket = htmlspecialchars(required_param('ticket', PARAM_INT), ENT_QUOTES, 'UTF-8');
$price =  required_param('price', PARAM_INT);
$triggers = htmlspecialchars(required_param('triggers', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$trigger_other = htmlspecialchars(required_param('trigger_other', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$pay_method = htmlspecialchars(required_param('pay_method', PARAM_INT), ENT_QUOTES, 'UTF-8');
$request_mail_kbn = htmlspecialchars(required_param('request_mail_kbn', PARAM_INT), ENT_QUOTES, 'UTF-8');
$note = htmlspecialchars(required_param('note', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$companion_mails = optional_param('companion_mails', '', PARAM_TEXT);
$triggers = optional_param('triggers', '', PARAM_TEXT);
// カンマで分割して配列にする
$triggerArray = explode(',', $triggers);
$applicant_kbn = optional_param('applicant_kbn', '', PARAM_INT);
$event_customfield_id = optional_param('event_customfield_id', 0, PARAM_INT);
$contact_phone = optional_param('contact_phone', '', PARAM_TEXT);
$guardian_name = optional_param('guardian_name', '', PARAM_TEXT);
$guardian_kana = optional_param('guardian_kana', '', PARAM_TEXT);
$guardian_email = optional_param('guardian_email', '', PARAM_TEXT);
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

if ($action === 'register') {
    // 申込登録処理
    try {
        $baseModel = new BaseModel();
        $pdo = $baseModel->getPdo();
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO mdl_event_application (
                event_id, user_id, event_custom_field_id, field_value
                , name, name_kana, email, ticket_count, price, pay_method
                , request_mail_kbn, applicant_kbn, application_date
                , note, contact_phone, guardian_name, guardian_name_kana
                , guardian_email, companion_mails
            ) 
            VALUES (
                :event_id , :user_id , :event_custom_field_id , :field_value
                , :name , :name_kana , :email , :ticket_count , :price , :pay_method
                , :request_mail_kbn , :applicant_kbn , CURRENT_TIMESTAMP
                , :note , :contact_phone , :guardian_name , :guardian_name_kana
                , :guardian_email , :companion_mails
            )
        ");
        
        $stmt->execute([
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
            , ':request_mail_kbn' => $request_mail_kbn
            , ':applicant_kbn' => $applicant_kbn
            , ':note' => $note
            , ':contact_phone' => $contact_phone
            , ':guardian_name' => $guardian_name
            , ':guardian_name_kana' => $guardian_kana
            , ':guardian_email' => $guardian_email
            , ':companion_mails' => $companion_mails
        ]);

        
        // mdl_eventの挿入IDを取得
        $eventApplicationId = $pdo->lastInsertId();

        // 知った経由　mdl_event_application_cognition
        $stmt2 = $pdo->prepare("
            INSERT INTO mdl_event_application_cognition (created_at, updated_at, event_application_id, cognition_id, note) 
            VALUES (CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :event_application_id, :cognition_id, :note)
        ");
        foreach ($triggerArray as $cognition_id) {
            $stmt2->execute([
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
        
        $pdo->commit();

        // API
        $_SESSION['message_success'] = '登録が完了しました';
        header('Location: /custom/app/Views/front/event_application.php?id=' . $eventId);
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
        header('Location: /custom/app/Views/front/event_application.php?id=' . $eventId);
    }

} else {
    // 修正(申込登録画面に戻る)
    // 入力画面に戻る
    $SESSION->formdata = [
        'id' => $eventId
        , 'ticket' => $ticket
        , 'trigger_othier' => $trigger_other
        , 'pay_method' => $pay_method
        , 'request_mail_kbn' => $request_mail_kbn
        , 'triggers' => $triggers
        , 'note' => $note
        , 'guardian_name' => $guardian_name
        , 'guardian_name_kana' => $guardian_name_kana
        , 'guardian_email' => $guardian_email
        , 'companion_mails' => $companion_mails];
    redirect(new moodle_url('/custom/app/Views/front/event_application.php'));
}
