<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/TekijukuCommemorationModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EmailSendSettingModel.php');

global $DB;
try {
    $id = $_POST['email_send_setting_id'] ?? null;
    $category_id = $_POST['select_category_id'] ?? null;
    $year = $_POST['select_year'] ?? null;
    $keyword = $_POST['select_keyword'] ?? null;
    $subject_ids = $_POST['subject_ids'] ?? null;
    $requert_month = $_POST['requert_month'] ?? null;
    $_SESSION['errors']["requert_month"] = validate_text($requert_month, '請求メール送信日時の月', 2, true);
    $requert_day = $_POST['requert_day'] ?? null;
    $_SESSION['errors']["requert_day"] = validate_text($requert_day, '請求メール送信日時の日', 2, true);
    $first_reminder_month = $_POST['first_reminder_month'] ?? null;
    $_SESSION['errors']["first_reminder_month"] = validate_text($first_reminder_month, '督促メール送信日時( 1回目 )の月', 2, true);
    $first_reminder_day = $_POST['first_reminder_day'] ?? null;
    $_SESSION['errors']["first_reminder_day"] = validate_text($first_reminder_day, '督促メール送信日時( 1回目 )の日', 2, true);
    $second_reminder_month = $_POST['second_reminder_month'] ?? null;
    $_SESSION['errors']["second_reminder_month"] = validate_text($second_reminder_month, '督促メール送信日時( 2回目 )の月', 2, true);
    $second_reminder_day = $_POST['second_reminder_day'] ?? null;
    $_SESSION['errors']["second_reminder_day"] = validate_text($second_reminder_day, '督促メール送信日時( 2回目 )の日', 2, true);
    $expulsion_month = $_POST['expulsion_month'] ?? null;
    $_SESSION['errors']["expulsion_month"] = validate_text($expulsion_month, '除名期日の月', 2, true);
    $expulsion_day = $_POST['expulsion_day'] ?? null;
    $_SESSION['errors']["expulsion_day"] = validate_text($expulsion_day, '除名期日の日', 2, true);
    $_SESSION['old_input'] = $_POST;
    
// エラーがある場合
if (
    $_SESSION['errors']['requert_month']
    || $_SESSION['errors']['requert_day']
    || $_SESSION['errors']['first_reminder_month']
    || $_SESSION['errors']['first_reminder_day']
    || $_SESSION['errors']['second_reminder_month']
    || $_SESSION['errors']['second_reminder_day']
    || $_SESSION['errors']['expulsion_month']
    || $_SESSION['errors']['expulsion_day']
) {
    header('Location: /custom/admin/app/Views/management/membership_fee_registration.php');
    exit;
}

    $emailSendSettingModel = new EmailSendSettingModel();
    $email_send_setting = $emailSendSettingModel->getEmailSendSettingById($id);

    $transaction = $DB->start_delegated_transaction();

    $row = new stdClass();
    if (!$id) {
        $row->created_at = date('Y-m-d H:i:s');
        $row->updated_at = date('Y-m-d H:i:s');
        $row->subject_ids = $subject_ids;
        $row->category_id = $category_id;
        $row->keyword = $keyword;
        $row->year = $year;
        $row->requert_month = $requert_month;
        $row->requert_day = $requert_day;
        $row->first_reminder_month = $first_reminder_month;
        $row->first_reminder_day = $first_reminder_day;
        $row->second_reminder_month = $second_reminder_month;
        $row->second_reminder_day = $second_reminder_day;
        $row->expulsion_month = $expulsion_month;
        $row->expulsion_day = $expulsion_day;
        $test = $DB->insert_record('email_send_setting', $row);
    } else {
        $row->id = $id;
        $row->updated_at = date('Y-m-d H:i:s');
        $row->subject_ids = $subject_ids;
        $row->requert_month = $requert_month;
        $row->requert_day = $requert_day;
        $row->first_reminder_month = $first_reminder_month;
        $row->first_reminder_day = $first_reminder_day;
        $row->second_reminder_month = $second_reminder_month;
        $row->second_reminder_day = $second_reminder_day;
        $row->expulsion_month = $expulsion_month;
        $row->expulsion_day = $expulsion_day;
        $DB->update_record('email_send_setting', $row);
    }

    $transaction->allow_commit();
    $_SESSION['message_success'] = '登録が完了しました';
    header('Location: /custom/admin/app/Views/management/membership_fee_registration.php');
} catch (Exception $e) {
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = '登録に失敗しました';
        redirect('/custom/admin/app/Views/management/membership_fee_registration.php');
        exit;
    }
}