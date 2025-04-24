<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationCourseInfoModel.php');

global $DB;

$cancel_event_application_id = $_POST['cancel_event_application_id'];

// 参加・未参加・キャンセルまたは参加前の状態に更新する
try {
    // 接続情報取得
    $baseModel = new BaseModel();
    $pdo = $baseModel->getPdo();
    $pdo->beginTransaction();
    // データベースに保存する場合
    $stmt = $pdo->prepare("
        UPDATE mdl_event_application_course_info
        SET 
            participation_kbn = :participation_kbn,
            updated_at = CURRENT_TIMESTAMP
        WHERE event_application_id = :event_application_id
    ");

    $stmt->execute([
        ':participation_kbn' => PARTICIPATION_KBN['CANCEL'], // 3：キャンセル
        ':event_application_id' => $cancel_event_application_id // キャンセルする申込ID
    ]);
    $pdo->commit();
    $_SESSION['event_application_message_success'] = 'キャンセルが完了しました';
    header('Location: /custom/app/Views/mypage/index.php#event_application');
} catch (Exception $e) {
    try {
        $pdo->rollBack();
    } catch (Exception $rollbackException) {
        $_SESSION['event_application_error'] = 'キャンセルに失敗しました';
        redirect('/custom/app/Views/mypage/index.php#event_application');
        exit;
    }
}
