<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');

// セッションをクリア
unset($SESSION->formdata);

$id = $_POST['event_id'];
$name = $_POST['name'];
$_SESSION['errors']['name'] = validate_text($name, 'お名前', 225, true);
$email = $_POST['email'];
$_SESSION['errors']['email'] = validate_custom_email($email);
$email_confirm = $_POST['email_confirm'];
$_SESSION['errors']['email_confirm'] = validate_custom_email($email_confirm);
$_SESSION['errors']['email_confirm'] = confirm_validation($email_confirm, $email, 'メールアドレス', $_SESSION['errors']['email_confirm']);
$event_name = $_POST['event_name'];
$_SESSION['errors']['event_name'] = validate_select($event_name, 'お問い合わせの項目', true);
$inquiry_details =$_POST['inquiry_details'];
$_SESSION['errors']['inquiry_details'] = validate_textarea($inquiry_details, 'お問い合わせ内容', true);


if ($_SESSION['errors']['name']
    || $_SESSION['errors']['email']
    || $_SESSION['errors']['event_name']
    || $_SESSION['errors']['inquiry_details']
) {
    $_SESSION['old_input'] = $_POST; // 入力内容も保持
    header('Location: /custom/app/Views/contact/index.php?event_id=' . $id);
    exit;
} else {
    $SESSION->formdata = [
        'event_id' => $id,
        'name' => $name,
        'email' => $email,
        'email_confirm' => $email_confirm,
        'event_name' => $event_name,
        'inquiry_details' => $inquiry_details
    ];
    redirect(new moodle_url('/custom/app/Views/contact/confirm.php'));
    exit;
}