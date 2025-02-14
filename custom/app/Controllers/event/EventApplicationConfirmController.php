<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventCustomFieldModel.php');

// セッションをクリア
unset($SESSION->formdata);

// ログイン判定
if (isloggedin() && isset($_SESSION['USER'])) {
    $user_id = $_SESSION['USER']->id;
} else {
    $_SESSION['message_error'] = 'ユーザ情報が取得できませんでした。ログインしてください。';
    header('Location: /custom/app/Views/front/index.php');
    return;
}

$eventId = $_POST['event_id'];
$eventModel = new eventModel();
$event = $eventModel->getEventById($eventId);
// イベント情報がなかった場合
if(is_null($event)) {
    header('Location: /custom/app/Views/front/index.php');
    return;
}
$eventName = $_POST['event_name'];
$name = $_POST['name'];
$kana = $_POST['kana'];
$email = $_POST['email'];
// 枚数
$ticket = $_POST['ticket'];
$_SESSION['errors']['ticket'] = validate_int($ticket, '枚数', true); // バリデーションチェック
$price =  str_replace(',', '', $_POST['price']);
$participation_fee = $event['participation_fee'];
if($price != $ticket * $participation_fee) {
    $_SESSION['errors']['message_error'] = '支払い料金が変更されました。ご確認の上、再度お申し込みしてください。';
    header('Location: /custom/app/Views/front/event_application.php?id=' . $eventId);
    return;
}
$triggers = optional_param_array('trigger', [], PARAM_INT);
$_SESSION['errors']['trigger'] = validate_array($triggers, '', true) ? "どこで本イベントを知ったか選択してください。" : null;
if(is_null($_SESSION['errors']['trigger'])) {
    // カンマで分割して配列にする
    $triggersString = implode(',', $triggers);
} else {
    $triggerArray = [];
}
$triggerOther = $_POST['trigger_other'];
$_SESSION['errors']['trigger_other'] = validate_textarea($triggerOther, 'その他', false);
$payMethod = optional_param('pay_method', null, PARAM_INT);
$_SESSION['errors']['pay_method'] = validate_select($payMethod, '支払方法', true); // バリデーションチェック
$note = $_POST['note'];
$_SESSION['errors']['note'] = validate_textarea($note, '備考欄', false);
$companionMails = array_map('htmlspecialchars', optional_param_array('companion_mails', [], PARAM_RAW));
$companionMailsString = implode(',', $companionMails);
$_SESSION['errors']['companion_mails'] = null;
foreach($companionMails as $companion_mail) {
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
$notification_kbn = htmlspecialchars(optional_param('notification_kbn', 1, PARAM_INT), ENT_QUOTES, 'UTF-8');

if(empty($guardian_kbn)) {
    $_SESSION['errors']['applicant_kbn'] = null;
    $_SESSION['errors']['guardian_name'] = null;
    $_SESSION['errors']['guardian_kana'] = null;
    $_SESSION['errors']['guardian_email'] = null;
} else {
    $_SESSION['errors']['applicant_kbn'] = validate_int($applicant_kbn, '保護者の許可', true);
    $_SESSION['errors']['guardian_name'] = validate_text($guardian_name, '保護者名', 225, true);
    $_SESSION['errors']['guardian_kana'] = validate_text($guardian_kana, '保護者名フリガナ', 225, true);
    $_SESSION['errors']['guardian_email'] = validate_custom_email($guardian_email, '保護者の');
}
$event_customfield_category_id =  $_POST['event_customfield_category_id'];
$eventCustomFieldModel = new eventCustomFieldModel();
$fieldList = [];
$fieldInputDataList = [];
if(!empty($event_customfield_category_id)) {
    $fieldList = $eventCustomFieldModel->getCustomFieldById($event_customfield_category_id);
    foreach ($fieldList as $fields) {
        $input_value = null;
        $tag_name = $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'];
        if ($fields['field_type'] == 3) {
            $input_data = optional_param_array($tag_name, [], PARAM_TEXT);
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
            $_SESSION['errors']['passage'][$tag_name] = validate_array($input_value, $fields['field_name'], true);
        } elseif ($fields['field_type'] == 4) {
            $input_value = optional_param($tag_name, 0, PARAM_INT);
            $options = explode(",", $fields['selection']);
            foreach ($options as $i => $option) {
                if($i+1 == $input_value) {
                    $input_value = $option;
                    break;
                }
            }
            $_SESSION['errors']['passage'][$tag_name] = validate_int($input_value, $fields['field_name'], true);
        } elseif ($fields['field_type'] == 1) {
            $input_value = optional_param($tag_name, '', PARAM_TEXT);
            $_SESSION['errors']['passage'][$tag_name] = validate_text($input_value, $fields['field_name'], 255, true);
        } elseif ($fields['field_type'] == 2) {
            $input_value = optional_param($tag_name, '', PARAM_TEXT);
            $_SESSION['errors']['passage'][$tag_name] = validate_textarea($input_value, $fields['field_name'], true);
        } elseif ($fields['field_type'] == 5) {
            $input_value = optional_param($tag_name, '', PARAM_TEXT);
            $_SESSION['errors']['passage'][$tag_name] = validate_date($input_value, $fields['field_name'], true);
        }
    }
}

// エラーがある場合
if($_SESSION['errors']['ticket']
|| $_SESSION['errors']['pay_method']
|| $_SESSION['errors']['trigger']
|| $_SESSION['errors']['trigger_other']
|| $_SESSION['errors']['note']
|| $_SESSION['errors']['companion_mails']
|| $_SESSION['errors']['guardian_name']
|| $_SESSION['errors']['guardian_kana']
|| $_SESSION['errors']['guardian_email']) {
    $_SESSION['old_input'] = $_POST; // 入力内容も保持
    header('Location: /custom/app/Views/front/event_application.php?id=' . $eventId);
    exit;
} else {
    $passages = '';
    $hiddens = '';
    if(!empty($event_customfield_category_id)) {
        $eventCustomFieldList = $eventCustomFieldModel->getCustomFieldById($event_customfield_category_id);
        foreach ($eventCustomFieldList as $eventCustomField) {
            $tag_name = $customfield_type_list[$eventCustomField['field_type']] . '_' . $eventCustomField['id'] . '_' . $eventCustomField['field_type'];
            
            if ($eventCustomField['field_type'] == 3) {
                $passages .= '<p><strong>' . $eventCustomField['field_name'] . '</strong>';
                $input_value = optional_param_array($tag_name, [], PARAM_INT);
                
                $options = explode(",", $eventCustomField['selection']);
                foreach ($options as $i => $option) {
                    if(in_array($i+1, $input_value)) {
                        $passages .= '<br>' . $option;
                    }
                }
                $passages .= '</p>';
                $inputValueString = implode(',', $input_value);
                $hiddens .= '<input type="hidden" name="' . $tag_name . '" value="' . $inputValueString . '">';
            } elseif ($eventCustomField['field_type'] == 4) {
                $passages .= '<p><strong>' . $eventCustomField['field_name'] . '</strong>';
                $input_value = optional_param($tag_name, 0, PARAM_INT);
                $options = explode(",", $eventCustomField['selection']);
                foreach ($options as $i => $option) {
                    if($i+1 == $input_value) {
                        $passages .= '<br>' . $option;
                    }
                }
                $passages .= '</p>';
                $hiddens .= '<input type="hidden" name="' . $tag_name . '" value="' . $input_value . '">';
            } elseif ($eventCustomField['field_type'] == 5) {
                $input_value = optional_param($tag_name, '', PARAM_TEXT);
                $value = str_replace("-", "/", $input_value);
                $passages .= '<p><strong>' . $eventCustomField['field_name'] . '</strong><br>' . $value . '</p>';
                $hiddens .= '<input type="hidden" name="' . $tag_name . '" value="' . $input_value . '">';
            } else {
                $input_value = optional_param($tag_name, '', PARAM_TEXT);
                $passages .= '<p><strong>' . $eventCustomField['field_name'] . '</strong><br>' . $input_value . '</p>';
                $hiddens .= '<input type="hidden" name="' . $tag_name . '" value="' . $input_value . '">';
            }
        }
    }
}
?>