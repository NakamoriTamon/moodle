<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationCourseInfoModel.php');

global $DB;

$is_apply_list = $_POST['is_apply'];
$_SESSION['old_input'] = $_POST;

// 参加・未参加を更新する
try {
    $transaction = $DB->start_delegated_transaction();
    foreach ($is_apply_list as $key => $is_apply) {
        $user = new stdClass();
        $user->id = $key;
        $user->is_apply = $is_apply;
        $DB->update_record_raw('user', $user);
    }
    $transaction->allow_commit();
    $_SESSION['message_success'] = '登録が完了しました';
    header('Location: /custom/admin/app/Views/management/user_registration.php');
} catch (Exception $e) {
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = '登録に失敗しました';
        redirect('/custom/admin/app/Views/management/user_registration.php');
        exit;
    }
}
