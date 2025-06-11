<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');

$id = $_POST['del_event_id'] ?? null;

try {
    if (empty($id)) {
        $_SESSION['message_error'] = '削除に失敗しました';
        header('Location: /custom/admin/app/Views/management/information.php');
        exit;
    }
    // 接続情報取得
    $baseModel = new BaseModel();
    $pdo = $baseModel->getPdo();
    $pdo->beginTransaction();
    $delete_stmt = $pdo->prepare("
        DELETE FROM mdl_information
        WHERE id = :information_id
    ");
    $delete_stmt->execute([':information_id' => $id]);
    $pdo->commit();
    $_SESSION['message_success'] = '削除が完了しました';
    header('Location: /custom/admin/app/Views/management/information.php');
    exit;
} catch (Exception $e) {
    // ロールバック中に例外が再スローする事を防ぐ
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        $_SESSION['old_input'] = $_POST;
        $_SESSION['message_error'] = '削除に失敗しました';
        redirect('/custom/admin/app/Views/management/information.php');
        exit;
    }
}
