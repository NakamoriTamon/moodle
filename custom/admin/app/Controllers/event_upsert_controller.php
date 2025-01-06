<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');

// 接続情報取得
$baseModel = new BaseModel();
$eventModel = new EventModel();
$pdo = $baseModel->getPdo();

// 登録情報取得 TO DO バリデーション
$empty = '';
$userId = 1;
$name = $_POST['name'];
$description = $_POST['description'];
$categoryId = $_POST['category_id'];
$thumbnailImg = $_POST['thumbnail_img'];
$venueId = $_POST['venue_id'];
$venueName = $_POST['venue_name'];
$target = $_POST['target'];
$eventDate = $_POST['event_date'];
$startHour = $_POST['start_hour'];
$endHour = $_POST['end_hour'];
$access = $_POST['access'];
$googleMap = $_POST['google_map'];
$program = $_POST['program'];
$isTop = $_POST['is_top'];
$tutorID = $_POST['tutor_id'];
$lectureName = $_POST['lecture_name'];
$lectureOutline = $_POST['lecture_outline'];
$sponsor = $_POST['sponsor'];
$coHost = $_POST['co_host'];
$sponsorship = $_POST['sponsorship'];
$cooperation = $_POST['cooperation'];
$plan = $_POST['plan'];
$capacity = $_POST['capacity'];
$participationFee = $_POST['participation_fee'];
$deadline = $_POST['deadline'];
$archiveStreamingPeriod = $_POST['archive_streaming_period'];
$note = $_POST['note'];
$isDoubleSpeed = $_POST['is_double_speed'];

$isTop = 1;
$isDoubleSpeed = 1;
$thumbnailImg = $_POST['thumbnail_img_name'];
$createdAt = date('Y-m-d H:i:s');
$updatedAt = date('Y-m-d H:i:s');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
        header('Location: /custom/admin/app/Views/index.php');
    }
}
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO mdl_event (
            name, 
            description, 
            categoryid, 
            userid, 
            event_date,
            start_hour,
            end_hour,
            target,
            venue_id,
            venue_name,
            access,
            google_map,
            is_top,
            program,
            sponsor,
            co_host,
            sponsorship,
            cooperation,
            plan,
            capacity,
            participation_fee,
            deadline,
            archive_streaming_period,
            is_double_speed,
            note,
            thumbnail_img,
            created_at,
            updated_at
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $name,
        $description,
        $categoryId,
        $userId,
        $eventDate,
        $startHour,
        $endHour,
        $target,
        $venueId,
        $venueName,
        $access,
        $googleMap,
        $isTop,
        $program,
        $sponsor,
        $coHost,
        $sponsorship,
        $cooperation,
        $plan,
        $capacity,
        $participationFee,
        $deadline,
        $archiveStreamingPeriod,
        $isDoubleSpeed,
        $note,
        $thumbnailImg,
        $createdAt,
        $updatedAt,
    ]);
    // var_dump('ok');

    $pdo->commit();
    $_SESSION['message_success'] = '登録が完了しました';
    header('Location: /custom/admin/app/Views/index.php');
} catch (PDOException $e) {
    $pdo->rollBack();
    var_dump($e->getMessage());
    $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
    header('Location: /custom/admin/app/Views/index.php');
}
