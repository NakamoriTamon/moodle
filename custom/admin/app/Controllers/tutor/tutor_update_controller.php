<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

// セッション開始
session_start();

// 接続情報取得
$baseModel = new BaseModel();
$pdo = $baseModel->getPdo();

// POSTデータの取得 (バリデーションは別途行う)
$id         = $_POST['id'] ?? '';
$name       = trim($_POST['name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$imagefile  = $_FILES['imagefile'] ?? null;
$path       = $_POST['existing_image'] ?? '';
$overview   = trim($_POST['overview'] ?? '');
$createdAt  = date('Y-m-d H:i:s');
$updatedAt  = date('Y-m-d H:i:s');

// 必要なバリデーションや処理を行う
$tutor_name_error       = validate_tutor_name($name);
$overview_error         = validate_tutor_overview($overview);
$require_image          = empty($id) || (!empty($id) && isset($imagefile) && $imagefile['error'] !== UPLOAD_ERR_NO_FILE);
$image_error            = $require_image ? validate_image($imagefile) : null;
$email_validate_error   = validate_custom_email($email);
$email_duplicate_error  = is_email_duplicate($pdo, $email, $name, $id);
$email_error            = ($email_duplicate_error ?? '') . ' ' . ($email_validate_error ?? '');
$email_error            = trim($email_error) ?: null;

if ($email_error || $tutor_name_error || $image_error || $overview_error) {
    $_SESSION['errors'] = [
        'name'       => $tutor_name_error,
        'imagefile'  => $image_error,
        'overview'   => $overview_error,
        'email'      => $email_error,
    ];
    $_SESSION['old_input'] = $_POST;
    $_SESSION['message_error'] = '登録に失敗しました';

    if (!empty($id)) {
        header('Location: /custom/admin/app/Views/master/tutor/upsert.php?id=' . urlencode($id));
    } else {
        header('Location: /custom/admin/app/Views/master/tutor/upsert.php');
    }
    exit;
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message_error'] = 'トークンが不正です。';
            header('Location: /custom/admin/app/Views/master/tutor/index.php');
            exit;
        }
    }

    if (isset($_FILES['imagefile']) && $_FILES['imagefile']['error'] === UPLOAD_ERR_OK) {
        $tmp_name       = $_FILES['imagefile']['tmp_name'];
        $original_name  = $_FILES['imagefile']['name'];
        $ext            = pathinfo($original_name, PATHINFO_EXTENSION);

        $newfilename    = uniqid('tutor_') . '.' . $ext;

        $destination_dir = '/var/www/html/moodle/uploads/tutor';
        if (!file_exists($destination_dir)) {
            mkdir($destination_dir, 0777, true);
        }
        $destination = $destination_dir . '/' . $newfilename;

        if (move_uploaded_file($tmp_name, $destination)) {
            $path = $newfilename;
        } else {
            $_SESSION['message_error'] = '画像アップロードに失敗しました。';
            header('Location: /custom/admin/app/Views/master/tutor/index.php');
            exit;
        }
    }

    if (!empty($id)) {
        $sql = "UPDATE mdl_tutor
                SET
                  name       = ?,
                  email      = ?,
                  path       = ?,
                  overview   = ?,
                  created_at = ?,
                  updated_at = ?
                WHERE id = ?";
        $params = [$name, $email, $path, $overview, $createdAt, $updatedAt, $id];
    } else {
        $sql = "INSERT INTO mdl_tutor
                  (name, email, path, overview, created_at, updated_at)
                VALUES
                  (?, ?, ?, ?, ?, ?)";
        $params = [$name, $email, $path, $overview, $createdAt, $updatedAt];
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $pdo->commit();
        $_SESSION['message_success'] = '登録が完了しました';
        header('Location: /custom/admin/app/Views/master/tutor/index.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
        header('Location: /custom/admin/app/Views/master/tutor/index.php');
        exit;
    }
}

// メールアドレス重複チェック
function is_email_duplicate($pdo, $email, $new_name, $current_id = null)
{
    if (!empty($current_id)) {
        // 更新処理時
        $stmt = $pdo->prepare("SELECT name FROM mdl_tutor WHERE email = :email AND id != :id");
        $stmt->execute(['email' => $email, 'id' => $current_id]);
    } else {
        // 新規登録時
        $stmt = $pdo->prepare("SELECT name FROM mdl_tutor WHERE email = :email");
        $stmt->execute(['email' => $email]);
    }
    $existing_tutors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($existing_tutors) > 0) {
        foreach ($existing_tutors as $tutor) {
            if ($tutor['name'] === $new_name) {
                return false;
            }
        }
        return 'メールアドレスが重複しています。';
    }
    return false;
}
