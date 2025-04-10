<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

// 接続情報取得
global $DB;

// POSTデータの取得 (バリデーションは別途行う)
$id = $_POST['id'] ?? '';
$best_event_img = $_POST['best_event_img'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // 元の画面に戻す
        return json_encode(['success' => false, 'message' => '削除に失敗しました']);
    }
}

try {
    // 接続情報取得
    $baseModel = new BaseModel();
    $pdo = $baseModel->getPdo();
    $pdo->beginTransaction();

    if(!empty($id) && !empty($best_event_img)) {
        // データベースに保存する場合
        $stmt = $pdo->prepare("
            UPDATE mdl_event
            SET 
                best_event_img = :best_event_img,
                is_best = 0,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $stmt->execute([
            ':best_event_img' => null, // ファイルURLを保存
            ':id' => $id // イベントID
        ]);

        // 画像ファイルを削除
        $file_path = $_SERVER['DOCUMENT_ROOT'] . parse_url($best_event_img, PHP_URL_PATH);
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        $pdo->commit();
    }

    // 元の画面に戻す
    header('Location: /custom/admin/app/Views/event/upsert.php?id=' . $id);
    return;
} catch (Exception $e) {
    $pdo->rollBack();
    // 元の画面に戻す
    header('Location: /custom/admin/app/Views/event/upsert.php?id=' . $id);
    return;
}
