<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

// 接続情報取得
$baseModel = new BaseModel();
$pdo = $baseModel->getPdo();

// POSTデータの取得 (バリデーションは別途行う)
$id       = $_POST['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = 'CSRFトークンが不正です。';
        header('Location: /custom/admin/app/Views/master/target/index.php');
        exit;
    }
}

try {
    $params = [1, $id];
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE mdl_target SET is_delete = ? WHERE id = ?");
    $stmt->execute($params);
    $pdo->commit();
    $_SESSION['message_success'] = '削除が完了しました';
    header('Location: /custom/admin/app/Views/master/target/index.php');
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    var_dump($e->getMessage());
    $_SESSION['message_error'] = '削除に失敗しました: ' . $e->getMessage();
    header('Location: /custom/admin/app/Views/master/target/index.php');
    exit;
}
