<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');

$id = $_POST['id'] ?? null;

global $DB, $CFG;
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message_error'] = '削除に失敗しました';
            header('Location: /custom/admin/app/Views/event/custom_index');
            exit;
        }
    }

    $transaction = $DB->start_delegated_transaction();
    $DB->set_field('event_customfield_category', 'is_delete', true, ['id' => $id]);


    $transaction->allow_commit();
    $_SESSION['message_success'] = '削除が完了しました';
    header('Location: /custom/admin/app/Views/event/custom_index.php');
    exit;
} catch (Exception $e) {
    // ロールバック中に例外が再スローする事を防ぐ
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = '削除に失敗しました';
        redirect('/custom/admin/app/Views/event/custom_index.php');
        exit;
    }
}
