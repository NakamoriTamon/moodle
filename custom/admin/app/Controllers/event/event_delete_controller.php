<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventApplicationModel.php');
require_once($CFG->libdir . '/filelib.php');

$id = $_POST['del_event_id'] ?? null;

if (empty($id)) {
    $_SESSION['message_error'] = '削除に失敗しました';
    header('Location: /custom/admin/app/Views/event/index.php');
    exit;
}

$eventApplicationModel = new EventApplicationModel();
$ticket_count = $eventApplicationModel->getSumTicketCountByEventId($id);

if(!empty($ticket_count) && $ticket_count['sum_ticket_count'] > 0) {
    $_SESSION['message_error'] = 'すでに申し込みが存在するため削除できません';
    header('Location: /custom/admin/app/Views/event/index.php');
    exit;
}
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message_error'] = '削除に失敗しました';
            header('Location: /custom/admin/app/Views/event/index.php');
            exit;
        }
    }

    // 接続情報取得
    $baseModel = new BaseModel();
    $pdo = $baseModel->getPdo();
    
    $pdo->beginTransaction();

    // 講義中間テーブルからcourse_info_id：講義IDを取得
    $stmt = $pdo->prepare("
        SELECT course_info_id
        FROM mdl_event_course_info 
        WHERE event_id = :event_id
    ");
    $stmt->execute([':event_id' => $id]);
    $course_info_ids = $stmt->fetchAll(PDO::FETCH_COLUMN); // course_info_id のリストを取得

    $material_file_names = [];
    $movie_file_names = [];

    // course_info_id分ループ
    foreach($course_info_ids as $course_info_id) {
        // 講義詳細を削除
        $stmt = $pdo->prepare("
            DELETE FROM mdl_course_info_detail 
            WHERE course_info_id = :course_info_id
        ");
        $stmt->execute([':course_info_id' => $course_info_id]);

        // 講義を削除
        $stmt = $pdo->prepare("
            DELETE FROM mdl_course_info 
            WHERE id = :course_info_id
        ");
        $stmt->execute([':course_info_id' => $course_info_id]);

        // 削除するイベントの教材を取得
        $stmt = $pdo->prepare("
            SELECT file_name
            FROM mdl_course_material 
            WHERE course_info_id = :course_info_id
        ");
        $stmt->execute([':course_info_id' => $id]);
        $list = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if(!empty($list)) {
            $material_file_names[] = $list;
        }

        // 教材を削除
        $stmt = $pdo->prepare("
            DELETE FROM mdl_course_material 
            WHERE course_info_id = :course_info_id
        ");
        $stmt->execute([':course_info_id' => $course_info_id]);


        // 削除するイベントの教材を取得
        $stmt = $pdo->prepare("
            SELECT file_name
            FROM mdl_course_movie 
            WHERE course_info_id = :course_info_id
        ");
        $stmt->execute([':course_info_id' => $course_info_id]);
        $list = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if(!empty($list)) {
            $movie_file_names[] = $list;
        }

        // 動画を削除
        $stmt = $pdo->prepare("
            DELETE FROM mdl_course_movie 
            WHERE course_info_id = :course_info_id
        ");
        $stmt->execute([':course_info_id' => $course_info_id]);
    }

    // 講義中間テーブルから削除
    $stmt = $pdo->prepare("
        DELETE FROM mdl_event_course_info 
        WHERE event_id = :event_id
    ");
    $stmt->execute([':event_id' => $id]);

    // カテゴリー中間テーブルから削除
    $stmt = $pdo->prepare("
        DELETE FROM mdl_event_category 
        WHERE event_id = :event_id
    ");
    $stmt->execute([':event_id' => $id]);

    // 講義形式中間テーブルから削除
    $stmt = $pdo->prepare("
        DELETE FROM mdl_event_lecture_format 
        WHERE event_id = :event_id
    ");
    $stmt->execute([':event_id' => $id]);
    
    // イベントを削除
    $stmt = $pdo->prepare("
        DELETE FROM mdl_event 
        WHERE id = :event_id
    ");
    $stmt->execute([':event_id' => $id]);

    // 教材を削除
    $dirpath = '/var/www/html/moodle/uploads/material/';
    if (is_dir($dirpath)) {
        foreach($course_info_ids as $course_info_id) {
            if (is_dir($dirpath)) {
                foreach($material_file_names as $file_name) {
                    if (file_exists($dirpath . $file_name)) {
                        if (!unlink($dirpath . $file_name)) {
                            throw new Exception('教材の削除に失敗しました。');
                        }
                    }
                }
            }
        }
    }

    // 動画を削除
    $dirpath = '/var/www/html/moodle/uploads/movie/';
    if (is_dir($dirpath)) {
        foreach($course_info_ids as $course_info_id) {
            if (is_dir($dirpath)) {
                foreach($movie_file_names as $file_name) {
                    if (file_exists($dirpath . $file_name)) {
                        if (!unlink($dirpath . $file_name)) {
                            throw new Exception('動画の削除に失敗しました。');
                        }
                    }
                }
            }
        }
    }

    // サムネール画像を削除
    $dirpath = '/var/www/html/moodle/uploads/thumbnails/' . $id;
    if (is_dir($dirpath)) {
        if (!fulldelete($dirpath)) {
            throw new Exception('サムネール画像の削除に失敗しました。');
        }
    }

    $pdo->commit();
    $_SESSION['message_success'] = '削除が完了しました';
    header('Location: /custom/admin/app/Views/event/index.php');
    exit;
} catch (Exception $e) {
    // ロールバック中に例外が再スローする事を防ぐ
    try {
        $pdo->rollBack();
        $_SESSION['message_error'] = '削除に失敗しました';
        redirect('/custom/admin/app/Views/event/index.php');
        exit;
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = '削除に失敗しました';
        redirect('/custom/admin/app/Views/event/index.php');
        exit;
    }
}
