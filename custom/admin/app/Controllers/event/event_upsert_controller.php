<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');

$event_kbns = require '/var/www/html/moodle/custom/path/to/event_kbn.php';
// フォームからのデータを受け取る
$id = $_POST['id'] ?? null;
$event_kbn = $_POST['event_kbn'] ?? null; // イベント区分
$_SESSION['errors']['event_kbn'] = validate_select($event_kbn, 'イベント区分', true); // バリデーションチェック
$name = $_POST['name'] ?? null; // イベントタイトル　必須
$_SESSION['errors']['name'] = validate_text($name, 'イベントタイトル', true); // バリデーションチェック
$description = $_POST['description'] ?? null; // 説明文　必須
$_SESSION['errors']['description'] = validate_textarea($description, '説明文', true); // バリデーションチェック
$selectedCategories = $_POST['category_id'] ?? []; // カテゴリー　必須
$_SESSION['errors']['category_id'] = validate_select_multiple($selectedCategories, 'カテゴリー', true); // バリデーションチェック
$thumbnail_img = $_FILES['thumbnail_img'] ?? null; // サムネール画像　必須
$_SESSION['errors']['thumbnail_img'] = validate_image_file($thumbnail_img, 'サムネール画像', true); // バリデーションチェック
$lecture_format_ids = $_POST['lecture_format_id'] ?? []; // 講義形式　必須
$_SESSION['errors']['lecture_format_id'] = validate_select_multiple($lecture_format_ids, '講義形式', true); // バリデーションチェック
$venue_name = $_POST['venue_name'] ?? null; // 会場名
$_SESSION['errors']['venue_name'] = validate_text_max225($venue_name, '会場名', false); // バリデーションチェック
$target = $_POST['target'] ?? null; // 対象
$_SESSION['errors']['target'] = validate_text_max225($target, '対象', false); // バリデーションチェック
$event_date = empty($_POST['event_date']) ? null : $_POST['event_date']; // 開催日
if ($event_kbn == 1) {
    $_SESSION['errors']['event_date'] = validate_date($event_date, '開催日', true);
} else {
    $_SESSION['errors']['event_date'] = null;
}
$start_hour = $_POST['start_hour'] ?? null; // 開始時間　必須
$_SESSION['errors']['start_hour'] = validate_time($start_hour, '開始時間', true);
$end_hour = $_POST['end_hour'] ?? null; // 終了時間　必須
$_SESSION['errors']['end_hour'] = validate_time($end_hour, '終了時間', true);
$access = $_POST['access'] ?? null; // 交通アクセス
$_SESSION['errors']['access'] = validate_text_max500($access, '交通アクセス', false); // バリデーションチェック
$google_map = $_POST['google_map'] ?? null; // Google Map
$is_top = $_POST['is_top'] == null ? 0 : 1; // トップに固定
$program = $_POST['program'] ?? null; // プログラム
$_SESSION['errors']['program'] = validate_text_max500($program, 'プログラム', false); // バリデーションチェック
$sponsor = $_POST['sponsor'] ?? null; // 主催
$_SESSION['errors']['sponsor'] = validate_text_max225($sponsor, '主催', false); // バリデーションチェック
$co_host = $_POST['co_host'] ?? null; // 共催
$_SESSION['errors']['co_host'] = validate_text_max225($co_host, '共催', false); // バリデーションチェック
$sponsorship = $_POST['sponsorship'] ?? null; // 後援
$_SESSION['errors']['sponsorship'] = validate_text_max225($sponsorship, '後援', false); // バリデーションチェック
$cooperation = $_POST['cooperation'] ?? null; // 協力
$_SESSION['errors']['cooperation'] = validate_text_max225($cooperation, '協力', false); // バリデーションチェック
$plan = $_POST['plan'] ?? null; // 企画
$_SESSION['errors']['plan'] = validate_text_max225($plan, '企画', false); // バリデーションチェック
$capacity = $_POST['capacity'] ?? null; // 定員
$_SESSION['errors']['capacity'] = validate_int($capacity, '定員', true); // バリデーションチェック
// 複数回シリーズのイベント　の場合
if($event_kbn == 2) {
    $participation_fee = $_POST['all_participation_fee'] ?? null; // 参加費
    $_SESSION['errors']['participation_fee'] = validate_int($participation_fee, '参加費( 全て受講 )', false); // バリデーションチェック
    $deadline = $_POST['all_deadline'] ?? null; // 申し込み締切日　必須
    $_SESSION['errors']['deadline'] = validate_date($deadline, '申し込み締切日( 全て受講 )', true);
} else {
    $participation_fee = $_POST['participation_fee'] ?? null; // 参加費
    $_SESSION['errors']['participation_fee'] = validate_int($participation_fee, '参加費', false); // バリデーションチェック
    $deadline = $_POST['deadline'] ?? null; // 申し込み締切日　必須
    $_SESSION['errors']['deadline'] = validate_date($deadline, '申し込み締切日', true);
}
$archive_streaming_period = empty($_POST['archive_streaming_period']) ? 0 : $_POST['archive_streaming_period']; // アーカイブ配信期間
$_SESSION['errors']['archive_streaming_period'] = validate_int($archive_streaming_period, 'アーカイブ配信期間', false); // バリデーションチェック
$is_double_speed = $_POST['is_double_speed'] == null ? 0 : 1; // 動画倍速機能
$is_apply_btn = $_POST['is_apply_btn'] ?? null; // 申込みボタンを表示する
$event_custom_id = $_POST['event_custom_id'] ?? null; // イベントカスタム区分
$_SESSION['errors']['event_custom_id'] = validate_select($event_custom_id, 'イベントカスタム区分', true); // バリデーションチェック
$survey_custom_id = $_POST['survey_custom_id'] ?? null; // アンケートカスタム区分
$_SESSION['errors']['survey_custom_id'] = validate_select($survey_custom_id, 'アンケートカスタム区分', false); // バリデーションチェック
$note = $_POST['note'] ?? null; // その他
$_SESSION['errors']['note'] = validate_textarea($note, 'その他', false); // バリデーションチェック


// 講師、講義名、講義概要のデータ構造
$lectures = [];
$error_flg = false;

if ($event_kbn == 1) {
    // イベント区分が 1 の場合: tutor_id_番号 の形式
    foreach ($_POST as $key => $value) {
        if (preg_match('/^tutor_id_(\d+)$/', $key, $matches)) {
            $lectureNumber = $matches[1]; // 講座番号

            $_SESSION['errors']["tutor_id_{$lectureNumber}"] = validate_select($value, '講師', true); // バリデーションチェック;
            $_SESSION['errors']["lecture_name_{$lectureNumber}"] = validate_text_max225($_POST["lecture_name_{$lectureNumber}"], '講義名', true); // バリデーションチェック;
            $_SESSION['errors']["program_{$lectureNumber}"] = validate_textarea($_POST["program_{$lectureNumber}"], '講義概要', true); // バリデーションチェック;

            if(!$error_flg 
                && ($_SESSION['errors']["tutor_id_{$lectureNumber}"]
                || $_SESSION['errors']["lecture_name_{$lectureNumber}"]
                || $_SESSION['errors']["program_{$lectureNumber}"])
            ) {
                $error_flg = true;
            }
            // データ収集
            $lectures[$lectureNumber] = [
                'tutor_id' => $value,
                'lecture_name' => $_POST["lecture_name_{$lectureNumber}"],
                'program' => $_POST["program_{$lectureNumber}"],
                'course_date' => $_POST["event_date"],
            ];
        }
    }
} elseif ($event_kbn == 2) {
    $required_flg = true;
    $count = 0;
    // イベント区分が 2 の場合: tutor_id_番号_番号 の形式
    foreach ($_POST as $key => $value) {
        if (preg_match('/^tutor_id_(\d+)_(\d+)$/', $key, $matches)) {
            $lectureNumber = $matches[1]; // 講座番号
            $itemNumber = $matches[2];   // 項目番号

            // 全て未入力の場合
            if(empty($_POST["course_date_{$lectureNumber}"])
            && empty($value)
            && empty($_POST["lecture_name_{$lectureNumber}_{$itemNumber}"])
            && empty($_POST["program_{$lectureNumber}_{$itemNumber}"])){
                // 追加せず次へ
                continue;
            }

            // 初期化
            if (!isset($lectures[$lectureNumber])) {
                $lectures[$lectureNumber] = [];
                if($lectureNumber > 2) {
                    $required_flg = false;
                }
            }

            $_SESSION['errors']["course_date_{$lectureNumber}"] = validate_select($_POST["course_date_{$lectureNumber}"], "開催日", $required_flg); // バリデーションチェック;
            $_SESSION['errors']["tutor_id_{$lectureNumber}_{$itemNumber}"] = validate_select($value, "講師", $required_flg); // バリデーションチェック;
            $_SESSION['errors']["lecture_name_{$lectureNumber}_{$itemNumber}"] = validate_text_max225($_POST["lecture_name_{$lectureNumber}_{$itemNumber}"], "講義名", $required_flg); // バリデーションチェック;
            $_SESSION['errors']["program_{$lectureNumber}_{$itemNumber}"] = validate_textarea($_POST["program_{$lectureNumber}_{$itemNumber}"], "講義概要", $required_flg); // バリデーションチェック;

            if(!$error_flg 
                && ($_SESSION['errors']["course_date_{$lectureNumber}"]
                || $_SESSION['errors']["tutor_id_{$lectureNumber}_{$itemNumber}"]
                || $_SESSION['errors']["lecture_name_{$lectureNumber}_{$itemNumber}"]
                || $_SESSION['errors']["program_{$lectureNumber}_{$itemNumber}"])
            ) {
                $error_flg = true;
            }

            // 各フィールドを収集
            $lectures[$lectureNumber] = [
                'course_date' => $_POST["course_date_{$lectureNumber}"],
            ];
            $lectures[$lectureNumber]["detail"][$count] = [
                'tutor_id' => $value,
                'lecture_name' => $_POST["lecture_name_{$lectureNumber}_{$itemNumber}"],
                'program' => $_POST["program_{$lectureNumber}_{$itemNumber}"],
            ];
        }
    }
}
// エラーがある場合
if($_SESSION['errors']['name']
    || $_SESSION['errors']['description']
    || $_SESSION['errors']['category_id']
    || $_SESSION['errors']['lecture_format_id']
    || $_SESSION['errors']['venue_name']
    || $_SESSION['errors']['target']
    || $_SESSION['errors']['event_date']
    || $_SESSION['errors']['start_hour']
    || $_SESSION['errors']['end_hour']
    || $_SESSION['errors']['access']
    || $_SESSION['errors']['program']
    || $_SESSION['errors']['sponsor']
    || $_SESSION['errors']['co_host']
    || $_SESSION['errors']['sponsorship']
    || $_SESSION['errors']['cooperation']
    || $_SESSION['errors']['plan']
    || $_SESSION['errors']['capacity']
    || $_SESSION['errors']['participation_fee']
    || $_SESSION['errors']['deadline']
    || $_SESSION['errors']['archive_streaming_period']
    || $_SESSION['errors']['event_custom_id']
    || $_SESSION['errors']['survey_custom_id']
    || $_SESSION['errors']['note']
    || $error_flg) {
    $_SESSION['old_input'] = $_POST; // 入力内容も保持
    header('Location: /custom/admin/app/Views/event/upsert.php');
    exit;
}

// 画像登録
// if ($thumbnail_img && $thumbnail_img['error'] === UPLOAD_ERR_OK) {
//     // アップロードされたファイルの情報を取得
//     $tmpName = $thumbnail_img['tmp_name']; // 一時ファイルのパス
//     $originalName = pathinfo($thumbnail_img['name'], PATHINFO_FILENAME); // 元のファイル名
//     $extension = pathinfo($thumbnail_img['name'], PATHINFO_EXTENSION);  // 拡張子

//     // 保存先ディレクトリ
//     $uploadDir = realpath(__DIR__ . '/../../../../uploads/thumbnails/');

//     // 保存先パスを生成
//     $timestamp = date('YmdHis');
//     $newFileName = "{$originalName}_{$timestamp}.{$extension}";
//     $destination = $uploadDir . DIRECTORY_SEPARATOR . $newFileName;

//     // 保存先ディレクトリが書き込み可能か確認
//     // if (!is_writable($uploadDir)) {
//     //     die('アップロード先ディレクトリが書き込み可能ではありません: ' . $uploadDir);
//     // }

//     // ファイルを移動
//     if (move_uploaded_file($tmpName, $destination)) {
//         echo "ファイルがアップロードされました: " . $destination;
//     } else {
//         echo "ファイルの保存中にエラーが発生しました。";
//     }
// } else {
//     echo "ファイルアップロードに失敗しました。エラーコード: " . $thumbnail_img['error'];
// }

// データの確認 (デバッグ用)
// print_r($lectures);

// 接続情報取得
$baseModel = new BaseModel();
$eventModel = new EventModel();
$pdo = $baseModel->getPdo();

$isTop = 1;
$isDoubleSpeed = 1;
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

    if(!empty($id)) {
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO mdl_event (
                name, description
                , event_date, start_hour, end_hour, target, venue_name, access
                , google_map, is_top, program, sponsor, co_host, sponsorship, cooperation, plan, capacity
                , participation_fee, deadline, archive_streaming_period, is_double_speed, note, thumbnail_img
                , created_at, updated_at
            ) 
            VALUES (
                :name, :description
                , :event_date, :start_hour, :end_hour, :target, :venue_name, :access
                , :google_map, :is_top, :program, :sponsor, :co_host, :sponsorship, :cooperation, :plan, :capacity
                , :participation_fee, :deadline, :archive_streaming_period, :is_double_speed, :note, :thumbnail_img
                , CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )
        ");
    
        $stmt->execute([
            ':name' => $name
            , ':description' => $description
            , ':event_date' => $event_date
            , ':start_hour' => $start_hour
            , ':end_hour' => $end_hour
            , ':target' => $target
            , ':venue_name' => $venue_name
            , ':access' => $access
            , ':google_map' => $google_map
            , ':is_top' => $is_top
            , ':program' => $program
            , ':sponsor' => $sponsor
            , ':co_host' => $co_host
            , ':sponsorship' => $sponsorship
            , ':cooperation' => $cooperation
            , ':plan' => $plan
            , ':capacity' => $capacity
            , ':participation_fee' => $participation_fee
            , ':deadline' => $deadline
            , ':archive_streaming_period' => $archive_streaming_period
            , ':is_double_speed' => $is_double_speed
            , ':note' => $note
            , ':thumbnail_img' => ""
        ]);
    
        // mdl_eventの挿入IDを取得
        $evenId = $pdo->lastInsertId();
    
        // カテゴリー登録処理
        foreach($selectedCategories as $key => $category_id) {
            // 2. mdl_event_categoryへのINSERT
            $stmt = $pdo->prepare("
                INSERT INTO mdl_event_category (
                    created_at, updated_at, event_id, category_id
                )
                VALUES (
                    CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :event_id, :category_id
                )
            ");
        
            $stmt->execute([
                ':event_id' => $evenId, // mdl_eventの挿入IDを使用
                ':category_id' => $category_id
            ]);
        }
        
        // 講義形式登録処理
        foreach($lecture_format_ids as $key => $lecture_format_id) {
            // 2. mdl_event_lecture_formatへのINSERT
            $stmt = $pdo->prepare("
                INSERT INTO mdl_event_lecture_format (
                    created_at, updated_at, event_id, lecture_format_id
                )
                VALUES (
                    CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :event_id, :lecture_format_id
                )
            ");
        
            $stmt->execute([
                ':event_id' => $evenId, // mdl_eventの挿入IDを使用
                ':lecture_format_id' => $lecture_format_id
            ]);
        }

        // 講座登録登録処理
        foreach($lectures as $key => $lecture) {
            // mdl_courseへのINSERT
            $stmt = $pdo->prepare("
                INSERT INTO mdl_course_info (
                    created_at, updated_at, no, course_date
                )
                VALUES (
                    CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :no, :course_date
                )
            ");
        
            $stmt->execute([
                ':no' => $key, // mdl_eventの挿入IDを使用
                ':course_date' => preg_replace('/[^\d]/', '', $lecture["course_date"])
            ]);
            $courseInfoId = $pdo->lastInsertId();

            // 講座詳細登録処理
            if($event_kbn == 2) {
                foreach($lecture["detail"] as $key => $detail) {
                    // mdl_course_detailへのINSERT
                    $stmt = $pdo->prepare("
                        INSERT INTO mdl_course_detail (
                            created_at, updated_at, course_info_id, tutor_id, name, program
                        )
                        VALUES (
                            CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :course_info_id, :tutor_id, :name, :program
                        )
                    ");
                
                    $stmt->execute([
                        ':course_info_id' => $courseInfoId,
                        ':tutor_id' => $detail["tutor_id"],
                        ':name' => $detail["lecture_name"],
                        ':program' => $detail["program"],
                    ]);
                }
            } else {
                // mdl_course_detailへのINSERT
                $stmt = $pdo->prepare("
                    INSERT INTO mdl_course_detail (
                        created_at, updated_at, course_info_id, tutor_id, name, program
                    )
                    VALUES (
                        CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :course_info_id, :tutor_id, :name, :program
                    )
                ");
            
                $stmt->execute([
                    ':course_info_id' => $courseInfoId,
                    ':tutor_id' => $lecture["tutor_id"],
                    ':name' => $lecture["lecture_name"],
                    ':program' => $lecture["program"],
                ]);
            }

            // mdl_courseへのINSERT
            $stmt = $pdo->prepare("
                INSERT INTO mdl_event_course_info (
                    created_at, updated_at, event_id, course_info_id
                )
                VALUES (
                    CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :event_id, :course_info_id
                )
            ");
        
            $stmt->execute([
                ':event_id' => $evenId, // mdl_eventの挿入IDを使用
                ':course_info_id' => $courseInfoId
            ]);
        }
    }

    $pdo->commit();
    $_SESSION['message_success'] = '登録が完了しました';
    header('Location: /custom/admin/app/Views/event/index.php');
} catch (PDOException $e) {
    $pdo->rollBack();
    var_dump($e->getMessage());
    $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
    header('Location: /custom/admin/app/Views/event/index.php');
}
