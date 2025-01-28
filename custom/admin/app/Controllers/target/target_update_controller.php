<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

session_start();

// 接続情報取得
$baseModel = new BaseModel();
$pdo = $baseModel->getPdo();

// POSTデータの取得 (バリデーションは別途行う)
$id         = $_POST['id'] ?? '';
$name       = trim($_POST['name'] ?? '');
$createdAt  = date('Y-m-d H:i:s');
$updatedAt  = date('Y-m-d H:i:s');

// 必要なバリデーションや処理を行う
$target_name_error       = validate_target_name($name);

if ($target_name_error) {
    $_SESSION['errors'] = [
        'name'       => $target_name_error,
    ];
    $_SESSION['old_input'] = $_POST;
    $_SESSION['message_error'] = '登録に失敗しました';

    if (!empty($id)) {
        header('Location: /custom/admin/app/Views/master/target/upsert.php?id=' . urlencode($id));
    } else {
        header('Location: /custom/admin/app/Views/master/target/upsert.php');
    }
    exit;
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message_error'] = 'トークンが不正です。';
            header('Location: /custom/admin/app/Views/master/target/index.php');
            exit;
        }
    }

    try {
        if (!empty($id)) {
            $sql = "SELECT COUNT(*) FROM mdl_target WHERE name = ? AND is_delete = 0 AND id <> ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $id]);
        } else {
            $sql = "SELECT COUNT(*) FROM mdl_target WHERE name = ? AND is_delete = 0";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name]);
        }
        $count = (int) $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['message_error'] = '同じ名前のデータが既に存在しています。';
            $_SESSION['errors'] = ['name' => '同じ名前のカテゴリが存在します'];
            $_SESSION['old_input'] = $_POST;
            if (!empty($id)) {
                header('Location: /custom/admin/app/Views/master/target/upsert.php?id=' . urlencode($id));
            } else {
                header('Location: /custom/admin/app/Views/master/target/upsert.php');
            }
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['message_error'] = 'DBエラーが発生しました: ' . $e->getMessage();
        header('Location: /custom/admin/app/Views/master/target/index.php');
        exit;
    }

    if (!empty($id)) {
        $sql = "UPDATE mdl_target
                SET
                  name       = ?,
                  created_at = ?,
                  updated_at = ?
                WHERE id = ?";
        $params = [$name, $createdAt, $updatedAt, $id];
    } else {
        $sql = "INSERT INTO mdl_target
                  (name, created_at, updated_at)
                VALUES
                  (?, ?, ?)";
        $params = [$name, $createdAt, $updatedAt];
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $pdo->commit();
        $_SESSION['message_success'] = '登録が完了しました';
        header('Location: /custom/admin/app/Views/master/target/index.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
        header('Location: /custom/admin/app/Views/master/target/index.php');
        exit;
    }
}
