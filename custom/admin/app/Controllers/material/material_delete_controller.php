<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

session_start();

$baseModel = new BaseModel();
$pdo = $baseModel->getPdo();

// POST データの取得（バリデーションは別途実施）
$id = $_POST['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = 'CSRFトークンが不正です。';
        header('Location: /custom/admin/app/Views/event/material.php');
        exit;
    }
}

try {
    $params = [1, $id];
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE mdl_course_material SET is_delete = ? WHERE id = ?");
    $stmt->execute($params);
    $pdo->commit();
    $_SESSION['message_success'] = '削除が完了しました';
    header('Location: /custom/admin/app/Views/event/material.php');
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['message_error'] = '削除に失敗しました: ' . $e->getMessage();
    header('Location: /custom/admin/app/Views/event/material.php');
    exit;
}
