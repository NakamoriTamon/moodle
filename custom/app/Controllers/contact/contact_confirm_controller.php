<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');

// セッションをクリア
unset($SESSION->formdata);

$event_id = $_POST['event_id'];
$name = $_POST['name'];
$_SESSION['errors']['name'] = validate_text($name, 'お名前', 225, true);
$email = $_POST['email'];
$_SESSION['errors']['email'] = validate_custom_email($email);
$email_confirm = $_POST['email_confirm'];
$_SESSION['errors']['email_confirm'] = validate_custom_email($email_confirm);
$_SESSION['errors']['email_confirm'] = confirm_validation($email_confirm, $email, 'メールアドレス', $_SESSION['errors']['email_confirm']);
$event_id = $_POST['event_id'];
$_SESSION['errors']['event_id'] = validate_select($event_id, 'お問い合わせの項目', true);
$inquiry_details =$_POST['inquiry_details'];
$_SESSION['errors']['inquiry_details'] = validate_textarea($inquiry_details, 'お問い合わせ内容', true);

if ($_SESSION['errors']['name']
    || $_SESSION['errors']['email']
    || $_SESSION['errors']['event_id']
    || $_SESSION['errors']['inquiry_details']
) {
    $_SESSION['old_input'] = $_POST; // 入力内容も保持
    header('Location: /custom/app/Views/contact/index.php?event_id=' . $event_id);
    exit;
} else {
    $event_name = "";
    if(is_numeric($event_id)) {
        $eventModel = new EventModel();
        $event = $eventModel->getEventById($event_id);
        $event_name = '【' . $event['name'] . '】について';
    } else {
        $event_name = "その他「『阪大知の広場』に関しての一般的なお問い合わせ";
    }

    $SESSION->formdata = [
        'event_id' => $event_id,
        'name' => $name,
        'email' => $email,
        'email_confirm' => $email_confirm,
        'event_name' => $event_name,
        'inquiry_details' => $inquiry_details
    ];
    redirect(new moodle_url('/custom/app/Views/contact/confirm.php'));
    exit;
}