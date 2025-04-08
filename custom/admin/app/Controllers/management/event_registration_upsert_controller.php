<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationCourseInfoModel.php');

global $DB;

$participation_kbn_list = $_POST['participation_kbn'];
$_SESSION['old_input'] = $_POST;

// 参加・未参加を更新する
try {
    $transaction = $DB->start_delegated_transaction();
    foreach ($participation_kbn_list as $key => $participation_kbn) {
        $event_application_course_info = new stdClass();
        $event_application_course_info->id = $key;
        $event_application_course_info->participation_kbn = $participation_kbn;
        $DB->update_record_raw('event_application_course_info', $event_application_course_info);
    }
    $transaction->allow_commit();
    $_SESSION['message_success'] = '登録が完了しました';
    header('Location: /custom/admin/app/Views/management/event_registration.php');
} catch (Exception $e) {
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = '登録に失敗しました';
        redirect('/custom/admin/app/Views/management/event_registration.php');
        exit;
    }
}
