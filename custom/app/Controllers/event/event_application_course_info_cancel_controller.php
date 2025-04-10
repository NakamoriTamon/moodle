<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationCourseInfoModel.php');

global $DB;

$cancel_event_application_course_info_id = $_POST['cancel_event_application_course_info_id'];

// 参加・未参加・キャンセルまたは参加前の状態に更新する
try {
    $transaction = $DB->start_delegated_transaction();
    $event_application_course_info = new stdClass();
    $event_application_course_info->id = $cancel_event_application_course_info_id;
    $event_application_course_info->participation_kbn = PARTICIPATION_KBN['CANCEL'];
    $DB->update_record_raw('event_application_course_info', $event_application_course_info);
    $transaction->allow_commit();
    $_SESSION['message_success'] = 'キャンセルが完了しました';
    header('Location: /custom/app/Views/mypage/index.php');
} catch (Exception $e) {
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = 'キャンセルに失敗しました';
        redirect('/custom/app/Views/mypage/index.php');
        exit;
    }
}
