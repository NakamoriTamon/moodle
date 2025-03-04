<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventCustomFieldModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CognitionModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/LectureFormatModel.php');
require_once('/var/www/html/moodle/custom/app/Models/PaymentTypeModel.php');

// セッションをクリア
unset($SESSION->formdata);

// ログイン判定
if (isloggedin() && isset($_SESSION['USER'])) {
    $user_id = $_SESSION['USER']->id;
} else {
    $_SESSION['message_error'] = 'ユーザ情報が取得できませんでした。ログインしてください。';
    header('Location: /custom/app/Views/index.php');
    return;
}

$eventId = htmlspecialchars(optional_param('event_id', 0, PARAM_INT));
$courseInfoId = htmlspecialchars(optional_param('course_info_id', 0, PARAM_INT));
$courseInfoId = $courseInfoId == 0 ? null : $courseInfoId;
$eventModel = new eventModel();
if(!is_null($courseInfoId)) {
    $event = $eventModel->getEventByIdAndCourseInfoId($eventId, $courseInfoId);
} else {
    $event = $eventModel->getEventById($eventId);
}
// イベント情報がなかった場合
if(empty($event)) {
    header('Location: /custom/app/Views/event/index.php');
    return;
}
        
$categoryModel = new CategoryModel();
$lectureFormatModel = new LectureFormatModel();
$categorys = $categoryModel->getCategories();
$lectureFormats = $lectureFormatModel->getLectureFormats();

$select_lecture_formats = [];
$select_categorys = [];
$select_courses = [];
if(!empty($event)) {
    
    foreach($event['lecture_formats'] as $lecture_format) {
        $lecture_format_id = $lecture_format['lecture_format_id'];
    
        foreach ($lectureFormats as $lectureFormat) {
            if ($lectureFormat['id'] == $lecture_format_id) {
                $select_lecture_formats[] = $lectureFormat;
                break;
            }
        }
    }

    foreach($event['categorys'] as $select_category) {
        $category_id = $select_category['category_id'];
    
        foreach ($categorys as $category) {
            if ($category['id'] == $category_id) {
                $select_categorys[] = $category;
                break;
            }
        }
    }

    foreach($event['course_infos'] as $select_course) {
        $select_courses[$select_course['no']] = $select_course;
    }
}

$eventName = $event['name'];
$name = htmlspecialchars(optional_param('name', '' , PARAM_TEXT));
$kana = htmlspecialchars(optional_param('kana', '' , PARAM_TEXT));
$email = htmlspecialchars(optional_param('email', '' , PARAM_TEXT));
// 枚数
$ticket = htmlspecialchars(optional_param('ticket', '' , PARAM_TEXT));
$_SESSION['errors']['ticket'] = validate_int($ticket, '枚数', true); // バリデーションチェック
$price =  htmlspecialchars(optional_param('price', '' , PARAM_TEXT));
$price =  str_replace(',', '', $price);
$participation_fee = $event['participation_fee'];
if($price != $ticket * ($participation_fee * count($select_courses))) {
    $_SESSION['errors']['message_error'] = '支払い料金が変更されました。ご確認の上、再度お申し込みしてください。';
    if(!is_null($courseInfoId)) {
        header('Location: /custom/app/Views/event/apply.php?id=' . $eventId . '&course_info_id=' . $courseInfoId);
        $event = $eventModel->getEventByIdAndCourseInfoId($eventId, $courseInfoId);
    } else {
        header('Location: /custom/app/Views/event/apply.php?id=' . $eventId);
    }
    return;
}
$triggers = optional_param_array('trigger', [], PARAM_RAW);
$_SESSION['errors']['trigger'] = validate_array($triggers, '', true) ? "どこで本イベントを知ったか選択してください。" : null;
if(is_null($_SESSION['errors']['trigger'])) {
    // カンマで分割して配列にする
    $triggersString = implode(',', $triggers);
} else {
    $triggerArray = [];
}
$triggerOther = htmlspecialchars(optional_param('trigger_other', '' , PARAM_TEXT));
$_SESSION['errors']['trigger_other'] = validate_textarea($triggerOther, 'その他', false);
$payMethod = htmlspecialchars(optional_param('pay_method', '', PARAM_INT));
$_SESSION['errors']['pay_method'] = validate_select($payMethod, '支払方法', true); // バリデーションチェック
$notificationKbn = htmlspecialchars(optional_param('notification_kbn', '', PARAM_TEXT));
$_SESSION['errors']['notification_kbn'] = validate_select($notificationKbn, 'お知らせメールの希望', true); // バリデーションチェック
$note = htmlspecialchars(optional_param('note', '' , PARAM_TEXT));
$_SESSION['errors']['note'] = validate_textarea($note, '備考欄', false);
$companionMails = array_map('htmlspecialchars', optional_param_array('companion_mails', [], PARAM_RAW));
$companionMailsString = implode(',', $companionMails);
$_SESSION['errors']['companion_mails'] = null;
foreach($companionMails as $companion_mail) {
    $_SESSION['errors']['companion_mails'] = validate_custom_email($companion_mail) ? "受講する人のメールアドレスを入力してください。" : null;
    if(!is_null($_SESSION['errors']['companion_mails'])) {
        break;
    }
}
$guardian_kbn = htmlspecialchars(optional_param('guardian_kbn', 0, PARAM_INT), ENT_QUOTES, 'UTF-8');
$contact_phone = $_SESSION['USER']->phone1;
$applicant_kbn = htmlspecialchars(optional_param('applicant_kbn', 0, PARAM_INT), ENT_QUOTES, 'UTF-8');
$guardian_name = htmlspecialchars(optional_param('guardian_name', '', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$guardian_email = htmlspecialchars(optional_param('guardian_email', '', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$notification_kbn = htmlspecialchars(optional_param('notification_kbn', 1, PARAM_INT), ENT_QUOTES, 'UTF-8');

if(empty($guardian_kbn)) {
    $_SESSION['errors']['applicant_kbn'] = null;
    $_SESSION['errors']['guardian_name'] = null;
    $_SESSION['errors']['guardian_email'] = null;
} else {
    $_SESSION['errors']['applicant_kbn'] = validate_int($applicant_kbn, '保護者の許可', true);
    $_SESSION['errors']['guardian_name'] = validate_text($guardian_name, '保護者名', 225, true);
    $_SESSION['errors']['guardian_email'] = validate_custom_email($guardian_email, '保護者の');
}
$event_customfield_category_id =  optional_param('event_customfield_category_id', '' , PARAM_INT);
$eventCustomFieldModel = new eventCustomFieldModel();

$cognitionModel = new cognitionModel();
if(!empty($triggers)) {
    $cognitions = $cognitionModel->getCognitionByIds($triggers);
} else {
    $cognitions = null;
}
$fieldList = [];
$fieldInputDataList = [];
$params = [];
if(!empty($event_customfield_category_id)) {
    $fieldList = $eventCustomFieldModel->getCustomFieldById($event_customfield_category_id);
    foreach ($fieldList as $fields) {
        $input_value = null;
        $tag_name = $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'];
        if ($fields['field_type'] == 3) {
            $input_data = optional_param_array($tag_name, [], PARAM_TEXT);
            $params[$tag_name] = $input_data;
            $options = explode(",", $fields['selection']);
            
            foreach ($options as $i => $option) {
                if(in_array($option, $input_data)) {
                    if($i == 0) {
                        $input_value = $option;
                        continue;
                    }
                    $input_value .= ',' . $option;
                }
            }
            $_SESSION['errors']['passage'][$tag_name] = validate_array($input_value, $fields['field_name'], false);
        } elseif ($fields['field_type'] == 4) {
            $input_value = optional_param($tag_name, '', PARAM_TEXT);
            $params[$tag_name] = $input_value;
            $options = explode(",", $fields['selection']);
            foreach ($options as $i => $option) {
                if($option == $input_value) {
                    $input_value = $option;
                    break;
                }
            }
            $_SESSION['errors']['passage'][$tag_name] = validate_int($input_value, $fields['field_name'], false);
        } elseif ($fields['field_type'] == 1) {
            $input_value = optional_param($tag_name, '', PARAM_TEXT);
            $params[$tag_name] = $input_value;
            $_SESSION['errors']['passage'][$tag_name] = validate_text($input_value, $fields['field_name'], 255, false);
        } elseif ($fields['field_type'] == 2) {
            $input_value = optional_param($tag_name, '', PARAM_TEXT);
            $params[$tag_name] = $input_value;
            $_SESSION['errors']['passage'][$tag_name] = validate_textarea($input_value, $fields['field_name'], false);
        } elseif ($fields['field_type'] == 5) {
            $input_value = optional_param($tag_name, '', PARAM_TEXT);
            $params[$tag_name] = $input_value;
            $_SESSION['errors']['passage'][$tag_name] = validate_date($input_value, $fields['field_name'], false);
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
|| $_SESSION['errors']['applicant_kbn']
|| $_SESSION['errors']['guardian_name']
|| $_SESSION['errors']['guardian_email']
|| (!empty($event_customfield_category_id) && empty($_SESSION['errors']['passage']))) {
    $_SESSION['old_input'] = $_POST; // 入力内容も保持
    if(isset($params)) {
        $SESSION->formdata = ['params' => $params];
    }

    if(!is_null($courseInfoId)) {
        header('Location: /custom/app/Views/event/apply.php?id=' . $eventId . '&course_info_id=' . $courseInfoId);
        $event = $eventModel->getEventByIdAndCourseInfoId($eventId, $courseInfoId);
    } else {
        header('Location: /custom/app/Views/event/apply.php?id=' . $eventId);
    }

    exit;
} else {
    $paymentTypeModel = new PaymentTypeModel();
    $paymentType = $paymentTypeModel->getPaymentTypesById($payMethod);

    $passages = '';
    $hiddens = '';
    if(!empty($event_customfield_category_id)) {
        $eventCustomFieldList = $eventCustomFieldModel->getCustomFieldById($event_customfield_category_id);
        foreach ($eventCustomFieldList as $eventCustomField) {
            $tag_name = $customfield_type_list[$eventCustomField['field_type']] . '_' . $eventCustomField['id'] . '_' . $eventCustomField['field_type'];
            
            if ($eventCustomField['field_type'] == 3) {
                $passages .= '<li class="long_item"><p class="list_label">' . $eventCustomField['name'] . '</p>';
                $input_value = optional_param_array($tag_name, [], PARAM_RAW);
                
                $options = explode(",", $eventCustomField['selection']);
                $passages .= '<div class="list_field f_txt list_col">';
                foreach ($options as $i => $option) {
                    if(in_array($option, $input_value)) {
                        $passages .= '<p class="list_field">' . $option . '</p><br />';
                    }
                }
                $passages .= '</div></li>';
                $inputValueString = implode(',', $input_value);
                $hiddens .= '<input type="hidden" name="' . $tag_name . '" value="' . $inputValueString . '">';
            } elseif ($eventCustomField['field_type'] == 4) {
                $passages .= '<li class="long_item"><p class="list_label">' . $eventCustomField['name'] . '</p>';
                $input_value = optional_param($tag_name, "", PARAM_TEXT);

                $options = explode(",", $eventCustomField['selection']);
                foreach ($options as $i => $option) {
                    if($option == $input_value) {
                        $passages .= '<p class="list_field">' . $option . '</p>';
                    }
                }
                $passages .= '</li>';
                $hiddens .= '<input type="hidden" name="' . $tag_name . '" value="' . $input_value . '">';
            } elseif ($eventCustomField['field_type'] == 5) {
                $input_value = optional_param($tag_name, '', PARAM_TEXT);
                $value = str_replace("-", "/", $input_value);
                $passages .= '<li><p class="list_label">' . $eventCustomField['name'] . '</p><p class="list_field">' . $value . '</p></li>';
                $hiddens .= '<input type="hidden" name="' . $tag_name . '" value="' . $input_value . '">';
            } else {
                $input_value = optional_param($tag_name, '', PARAM_TEXT);
                $passages .= '<li><p class="list_label">' . $eventCustomField['name'] . '</p><p class="list_field">' . nl2br($input_value) . '</p></li>';
                $hiddens .= '<input type="hidden" name="' . $tag_name . '" value="' . $input_value . '">';
            }
        }
    }

    $SESSION->formdata = [
        'id' => $eventId
        , 'course_info_id' => $courseInfoId
        , 'name' => $name
        , 'kana' => $kana
        , 'event_name' => $eventName
        , 'email' => $email
        , 'price' => $price
        , 'ticket' => $ticket
        , 'trigger_other' => $triggerOther
        , 'pay_method' => $payMethod
        , 'notification_kbn' => $notificationKbn
        , 'triggers' => $triggers
        , 'triggersString' => $triggersString
        , 'note' => $note
        , 'companion_mails' => $companionMails
        , 'companionMailsString' => $companionMailsString
        , 'applicant_kbn' => $applicant_kbn
        , 'guardian_kbn' => $guardian_kbn
        , 'guardian_name' => $guardian_name
        , 'guardian_email' => $guardian_email
        , 'event_customfield_category_id' => $event_customfield_category_id
        , 'cognitions' => $cognitions
        , 'select_lecture_formats' => $select_lecture_formats
        , 'select_categorys' => $select_categorys
        , 'select_courses' => $select_courses
        , 'paymentType' => $paymentType
        , 'passages' => $passages
        , 'hiddens' => $hiddens
        , 'params' => $params
    ];
    redirect(new moodle_url('/custom/app/Views/event/confirm.php'));
    exit;
}
?>