<?php
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/TekijukuCommemorationModel.php');

global $DB;
try {
    $category_id = $_POST['category_id'] ?? null;
    $year = $_POST['year'] ?? null;
    $keyword = $_POST['keyword'] ?? null;


    

    $transaction = $DB->start_delegated_transaction();
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