<?php
header('Content-Type: application/json');
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/TargetModel.php');
require_once($CFG->libdir . '/filelib.php');

$preview_id = uniqid('preview_', true);

$targetModel = new TargetModel();
$targets = $targetModel->getTargets();

global $USER, $DB;
$userid = $USER->id;
$event_kbns = EVENT_KBN_LIST;

$_SESSION['message_error'] = 'プレビュー画面の生成に失敗しました。';

// フォームからのデータを受け取る
$id = $_POST['id'] ?? null;
$event_kbn = $_POST['event_kbn'] ?? null; // イベント区分
$_SESSION['errors']['event_kbn'] = validate_select($event_kbn, 'イベント区分', true); // バリデーションチェック
$name = $_POST['name'] ?? null; // イベントタイトル　必須
$_SESSION['errors']['name'] = validate_text($name, 'イベントタイトル', 225, true); // バリデーションチェック
$description = $_POST['description'] ?? null; // 説明文　必須
$_SESSION['errors']['description'] = validate_textarea($description, '説明文', false); // バリデーションチェック
$selectedCategories = $_POST['category_id'] ?? []; // カテゴリー　必須
$_SESSION['errors']['category_id'] = validate_select_multiple($selectedCategories, 'カテゴリー', true); // バリデーションチェック
$thumbnail_img = $_FILES['thumbnail_img'] ?? null; // サムネール画像　新規登録は必須
$_SESSION['errors']['thumbnail_img'] = validate_image_file($thumbnail_img, 'サムネール画像', false); // バリデーションチェック
$lecture_format_ids = $_POST['lecture_format_id'] ?? []; // 講義形式　必須
$_SESSION['errors']['lecture_format_id'] = validate_select_multiple($lecture_format_ids, '講義形式', true); // バリデーションチェック
$venue_name = $_POST['venue_name'] ?? null; // 会場名
$_SESSION['errors']['venue_name'] = validate_text($venue_name, '会場名', 225, false); // バリデーションチェック
$target = $_POST['target'] ?? null; // 対象
$_SESSION['errors']['target'] = validate_select($target, '対象', false);
$event_date = empty($_POST['event_date']) ? null : $_POST['event_date']; // 開催日
$start_event_date = null;
$end_event_date = null;

// 公開予約入力の取得
$scheduled_publish_at = null;
$scheduled_publish_date = $_POST['scheduled_publish_date'] ?? '';
$scheduled_publish_time = $_POST['scheduled_publish_time'] ?? '';
if (!empty($scheduled_publish_date) xor !empty($scheduled_publish_time)) {
    $_SESSION['errors']['scheduled_publish_at'] = '公開予約を設定する場合は、日付と時間の両方が必要です';
} elseif (!empty($scheduled_publish_date) && !empty($scheduled_publish_time)) {
    $scheduled_publish_at = sprintf('%s %02d:00:00', $scheduled_publish_date, (int)$scheduled_publish_time);
}

// 値のチェック
if (!empty($scheduled_publish_at) && strtotime($scheduled_publish_at) < time()) {
    $_SESSION['errors']['scheduled_publish_at'] = '公開予約は未来の日付を入力してください';
}

// 値のチェック
if ($event_kbn == SINGLE_EVENT) {
    $_SESSION['errors']['event_date'] = validate_date($event_date, '開催日', true);
    $_SESSION['errors']['start_event_date'] = null;
    $_SESSION['errors']['end_event_date'] = null;
} else {
    $_SESSION['errors']['event_date'] = null;
    if ($event_kbn == EVERY_DAY_EVENT) {
        $start_event_date = empty($_POST['start_event_date']) ? null : $_POST['start_event_date']; // 開催日
        $end_event_date = empty($_POST['end_event_date']) ? null : $_POST['end_event_date']; // 開催日
        if (!empty($id)) {
            $start_event_date = $start_event_date ? (new DateTime($start_event_date))->format('Y-m-d') : null;
            $end_event_date = $end_event_date ? (new DateTime($end_event_date))->format('Y-m-d') : null;
        }
        $_SESSION['errors']['start_event_date'] = validate_date($start_event_date, '開催日(開始日)', true);
        $_SESSION['errors']['end_event_date'] = validate_date($end_event_date, '開催日(終了日)', true);
        if (is_null($_SESSION['errors']['start_event_date']) && is_null($_SESSION['errors']['end_event_date'])) {
            $_SESSION['errors']['start_event_date'] = validate_date_comparison($start_event_date, $end_event_date, '開催日(開始日)', '開催日(終了日)');
        }
    } else {
        $_SESSION['errors']['start_event_date'] = null;
        $_SESSION['errors']['end_event_date'] = null;
    }
}
$start_hour = $_POST['start_hour'] ?? null; // 開始時間　必須
$_SESSION['errors']['start_hour'] = validate_time($start_hour, '開始時間', true);
$end_hour = $_POST['end_hour'] ?? null; // 終了時間　必須
$_SESSION['errors']['end_hour'] = validate_time($end_hour, '終了時間', true);

// 開始時間と終了時間が両方とも正しい形式の場合のみ比較
if ($_SESSION['errors']['start_hour'] === null && $_SESSION['errors']['end_hour'] === null) {
    // 開始時間を終了時間に変換
    $start_time = DateTime::createFromFormat('H:i', $start_hour);
    $end_time = DateTime::createFromFormat('H:i', $end_hour);

    // 開始時間が終了時間より後の場合、値を交換する
    if ($start_time > $end_time) {
        $temp = $start_hour;
        $start_hour = $end_hour;
        $end_hour = $temp;

        $_POST['start_hour'] = $start_hour;
        $_POST['end_hour'] = $end_hour;
    }
}

$access = $_POST['access'] ?? null; // 交通アクセス
$_SESSION['errors']['access'] = validate_text($access, '交通アクセス', 500, false); // バリデーションチェック
$google_map = $_POST['google_map'] ?? null; // Google Map
$_SESSION['errors']['google_map'] = validate_google_map($google_map, 'Google Map', false);
$is_top = !isset($_POST['is_top']) ? 0 : $_POST['is_top']; // トップに固定
$is_best = !isset($_POST['is_best']) ? 0 : $_POST['is_best']; // 推しイベント設定
if (!empty($is_best)) {
    $best_event_img = $_FILES['best_event_img'] ?? null; // 推しイベント画像　新規登録は必須
    $tag = $_POST['best_event_img_tag'] ?? null;
    $best_event_sp_img = $_FILES['best_event_sp_img'] ?? null; // 推しイベント画像　新規登録は必須
    $sp_tag = $_POST['best_event_sp_img_tag'] ?? null;
    if (empty($best_event_img['name']) && !empty($id) && !empty($tag)) {
        $_SESSION['errors']['best_event_img'] = null;
    } else {
        $_SESSION['errors']['best_event_img'] = validate_image_file($best_event_img, '推しイベント画像 パソコン表示用', true); // バリデーションチェック
    }
    if (empty($best_event_sp_img['name']) && !empty($id) && !empty($sp_tag)) {
        $_SESSION['errors']['best_event_sp_img'] = null;
    } else {
        $_SESSION['errors']['best_event_sp_img'] = validate_image_file($best_event_sp_img, '推しイベント画像 スマホ表示用', true); // バリデーションチェック
    }
} else {
    $best_event_img = null;
    $_SESSION['errors']['best_event_img'] = null;
    $_SESSION['errors']['best_event_sp_img'] = null;
}
$is_tekijuku_only = !isset($_POST['is_tekijuku_only']) ? 0 : $_POST['is_tekijuku_only']; // 適塾会員限定イベント
$program = ""; // プログラム
$sponsor = $_POST['sponsor'] ?? null; // 主催
$_SESSION['errors']['sponsor'] = validate_text($sponsor, '主催', 225, false); // バリデーションチェック
$co_host = $_POST['co_host'] ?? null; // 共催
$_SESSION['errors']['co_host'] = validate_text($co_host, '共催', 225, false); // バリデーションチェック
$sponsorship = $_POST['sponsorship'] ?? null; // 後援
$_SESSION['errors']['sponsorship'] = validate_text($sponsorship, '後援', 225, false); // バリデーションチェック
$cooperation = $_POST['cooperation'] ?? null; // 協力
$_SESSION['errors']['cooperation'] = validate_text($cooperation, '協力', 225, false); // バリデーションチェック
$plan = $_POST['plan'] ?? null; // 企画
$_SESSION['errors']['plan'] = validate_text($plan, '企画', 225, false); // バリデーションチェック
$inquiry_mail = $_POST['inquiry_mail'] ?? null; // お問い合わせ先メールアドレス
$_SESSION['errors']['inquiry_mail'] = validate_custom_email($inquiry_mail, 'お問い合わせ先'); // バリデーションチェック
$tekijuku_discount = empty($_POST['tekijuku_discount']) ? 0 : $_POST['tekijuku_discount']; // 適塾記念会会員割引額
$_SESSION['errors']['tekijuku_discount'] = validate_int_zero_ok($tekijuku_discount, '適塾記念会会員割引額', false); // バリデーションチェック

// 複数回シリーズのイベント　の場合
if ($event_kbn == PLURAL_EVENT) {
    $single_participation_fee = empty($_POST['single_participation_fee']) ? 0 : $_POST['single_participation_fee']; // 単体の参加費
    $_SESSION['errors']['single_participation_fee'] = validate_int_zero_ok($single_participation_fee, '参加費', false);
    $title = "参加費( 全て受講 )";
    $participation_fee = empty($_POST['participation_fee']) ? 0 : $_POST['participation_fee']; // 参加費
    $_SESSION['errors']['participation_fee'] = validate_int_zero_ok($participation_fee, $title, false); // バリデーションチェック
    $all_deadline = empty($_POST['all_deadline']) ? 0 : $_POST['all_deadline']; // 各回申し込み締切日　必須
    $_SESSION['errors']['all_deadline'] = validate_int_zero_ok($all_deadline, '各回申し込み締切日', false);

    // 適塾記念会会員割引額が単体の参加費、参加費より大きくないか確認
    if (
        is_null($_SESSION['errors']['single_participation_fee'])
        && is_null($_SESSION['errors']['participation_fee'])
        && is_null($_SESSION['errors']['tekijuku_discount'])
    ) {
        if ($tekijuku_discount > $single_participation_fee || $tekijuku_discount > $participation_fee) {
            $_SESSION['errors']['tekijuku_discount'] = "適塾記念会会員割引額は参加費( 全て受講 )、参加費より大きい金額を入力しないでください。";
        }
    }
} else {
    $title = "参加費";
    $participation_fee = empty($_POST['participation_fee']) ? 0 : $_POST['participation_fee']; // 参加費
    $_SESSION['errors']['participation_fee'] = validate_int_zero_ok($participation_fee, $title, false); // バリデーションチェック
    $all_deadline = 0;
    $single_participation_fee = $participation_fee;
    // 適塾記念会会員割引額が単体の参加費、参加費より大きくないか確認
    if (
        is_null($_SESSION['errors']['participation_fee'])
        && is_null($_SESSION['errors']['tekijuku_discount'])
    ) {
        if ($tekijuku_discount > $participation_fee) {
            $_SESSION['errors']['tekijuku_discount'] = "適塾記念会会員割引額は参加費より大きい金額を入力しないでください。";
        }
    }
}

$deadline = empty($_POST['deadline']) ?  null : $_POST['deadline']; // 申し込み締切日　必須
$_SESSION['errors']['deadline'] = validate_date($deadline, '申し込み締切日', false);
$capacity = empty($_POST['capacity']) ? 0 : $_POST['capacity']; // 定員
$_SESSION['errors']['capacity'] = validate_int_zero_ok($capacity, '定員', false); // バリデーションチェック
// イベント毎日開催の場合
if ($event_kbn == EVERY_DAY_EVENT) {
    // 開始日
    if (!empty($deadline) && is_null($_SESSION['errors']['start_event_date']) && is_null($_SESSION['errors']['deadline'])) {
        $_SESSION['errors']['deadline'] = validate_date_comparison($deadline, $start_event_date, '申し込み締切日', '開催日(開始日)');
    }

    if (!empty($deadline) && is_null($_SESSION['errors']['end_event_date']) && is_null($_SESSION['errors']['deadline'])) {
        $_SESSION['errors']['deadline'] = validate_date_comparison($deadline, $end_event_date, '申し込み締切日', '開催日(終了日)');
    }
}
// 23時59分59秒を付ける
if (!is_null($deadline)) {
    $deadline = $deadline . ' 23:59:59';
} elseif ($event_kbn != EVERY_DAY_EVENT && is_null($deadline) && !is_null($event_date)) {
    $date = new DateTime($event_date);
    $date->modify('-1days');
    $deadline = $date->format('Y-m-d 23:59:59');
}
$real_time_distribution_url = empty($_POST['real_time_distribution_url']) ? null : $_POST['real_time_distribution_url']; // リアルタイム配信URL
$_SESSION['errors']['real_time_distribution_url'] = validate_url($real_time_distribution_url, 'リアルタイム配信URL', false);
$archive_streaming_period = empty($_POST['archive_streaming_period']) ? null : $_POST['archive_streaming_period']; // アーカイブ配信期間
$_SESSION['errors']['archive_streaming_period'] = validate_int($archive_streaming_period, 'アーカイブ配信期間', false); // バリデーションチェック
$material_release_period = empty($_POST['material_release_period']) ? null : $_POST['material_release_period']; // 講義資料公開期間
$_SESSION['errors']['material_release_period'] = validate_int($material_release_period, '講義資料公開期間', false); // バリデーションチェック
$is_double_speed = isset($_POST['is_double_speed']) ? 1 : 0; // 動画倍速機能
$is_apply_btn = isset($_POST['is_apply_btn']) ? 1 : 0; // 申込みボタンを表示する
$event_customfield_category_id = empty($_POST['event_customfield_category_id']) ? 0 : $_POST['event_customfield_category_id']; // イベントカスタム区分
$_SESSION['errors']['event_customfield_category_id'] = validate_select($event_customfield_category_id, 'イベントカスタム区分', false); // バリデーションチェック
$event_survey_customfield_category_id = empty($_POST['event_survey_customfield_category_id']) ? 0 : $_POST['event_survey_customfield_category_id']; // アンケートカスタム区分
$_SESSION['errors']['event_survey_customfield_category_id'] = validate_select($event_survey_customfield_category_id, 'アンケートカスタム区分', false); // バリデーションチェック
$note = $_POST['note'] ?? null; // その他
$_SESSION['errors']['note'] = validate_textarea($note, 'その他', false); // バリデーションチェック
$is_all_apply_btn = isset($_POST['is_all_apply_btn']) ? 1 : 0; // 一括申込みボタンを表示する

// 講師、講義名、講義概要のデータ構造
$lectures = [];
$error_flg = false;

$count = 0;
$release_date_input_flg = false; // アーカイブ公開日が入力されたか判定
$material_release_date_input_flg = false; // 講義資料公開日が入力されたか判定
if ($event_kbn == SINGLE_EVENT) {
    if (!empty($deadline) && is_null($_SESSION['errors']['event_date']) && is_null($_SESSION['errors']['deadline'])) {
        $_SESSION['errors']['deadline'] = validate_date_comparison($deadline, $event_date, '申し込み締切日', '開催日');
    }
    // イベント区分が 1 の場合: tutor_id_番号 の形式
    foreach ($_POST as $key => $value) {
        if (preg_match('/^tutor_id_(\d+)$/', $key, $matches)) {
            $lectureNumber = $matches[1]; // 講座番号

            $_SESSION['errors']["tutor_id_{$lectureNumber}"] = validate_select($value, '講師', false); // バリデーションチェック;
            if (empty($value)) {
                $_SESSION['errors']["tutor_name_{$lectureNumber}"] = validate_text($_POST["tutor_name_{$lectureNumber}"], '講師名', 225, false); // バリデーションチェック;
            } else {
                $_SESSION['errors']["tutor_name_{$lectureNumber}"] = null;
            }
            $_SESSION['errors']["lecture_name_{$lectureNumber}"] = validate_text($_POST["lecture_name_{$lectureNumber}"], '講義名', 225, true); // バリデーションチェック;
            $_SESSION['errors']["program_{$lectureNumber}"] = validate_textarea($_POST["program_{$lectureNumber}"], '講義概要', true); // バリデーションチェック;
            $_SESSION['errors']["release_date"] = validate_date($_POST["release_date"], "アーカイブ公開日", false);
            $_SESSION['errors']["material_release_date"] = validate_date($_POST["material_release_date"], "講義資料公開日", false);

            if (
                !$error_flg
                && ($_SESSION['errors']["tutor_id_{$lectureNumber}"]
                    || $_SESSION['errors']["lecture_name_{$lectureNumber}"]
                    || $_SESSION['errors']["program_{$lectureNumber}"]
                    || $_SESSION['errors']["tutor_name_{$lectureNumber}"]
                    || $_SESSION['errors']["release_date"]
                    || $_SESSION['errors']["material_release_date"])
            ) {
                $error_flg = true;
                $_SESSION['old_input'] = $_POST;
                http_response_code(500);
                echo json_encode(['success' => false]);
                exit;
            }

            // アーカイブ公開日が入力されている場合
            if (!empty($_POST["release_date"])) {
                $release_date_input_flg = true; // 入力判定
            }
            if (!empty($_POST["material_release_date"])) {
                $material_release_date_input_flg = true; //入力判定
            }

            if (!$error_flg) {
                if (empty($deadline)) {
                    $deadline_date = $event_date . ' ' . $end_hour;
                } else {
                    $deadline_date = $deadline;
                }

                // データ収集
                if (empty($lectures[1])) {
                    $lectures[1] = [
                        'course_info_id' => $_POST["course_info_id"],
                        'course_date' => $event_date,
                        'release_date' => empty($_POST["release_date"]) ? null : $_POST["release_date"],
                        'deadline_date' => $deadline_date,
                        'material_release_date' => empty($_POST["material_release_date"]) ? null : $_POST["material_release_date"],
                    ];
                }

                $lectures[1]["detail"][$lectureNumber] = [];
                $lectures[1]["detail"][$lectureNumber] = [
                    'tutor_id' => empty($value) ? null : $value,
                    'lecture_name' => $_POST["lecture_name_{$lectureNumber}"],
                    'program' => $_POST["program_{$lectureNumber}"],
                    'tutor_name' => $_POST["tutor_name_{$lectureNumber}"]
                ];
            }
            $count++;
        }
    }
} elseif ($event_kbn == PLURAL_EVENT) {
    $required_flg = true;
    $deadline_date = null;
    // イベント区分が 2 の場合: tutor_id_番号_番号 の形式
    foreach ($_POST as $key => $value) {
        if (preg_match('/^tutor_id_(\d+)_(\d+)$/', $key, $matches)) {
            $lectureNumber = $matches[1]; // 講座番号
            $itemNumber = $matches[2];   // 項目番号

            // 全て未入力の場合
            if (
                $lectureNumber > 2
                && empty($_POST["course_date_{$lectureNumber}"])
                && empty($_POST["release_date_{$lectureNumber}"])
                && empty($_POST["material_release_date_{$lectureNumber}"])
                && empty($value)
                && empty($_POST["lecture_name_{$lectureNumber}_{$itemNumber}"])
                && empty($_POST["program_{$lectureNumber}_{$itemNumber}"])
                && empty($_POST["tutor_name_{$lectureNumber}_{$itemNumber}"])
            ) {
                // 追加せず次へ
                continue;
            }

            // 初期化
            if (empty($lectures[$lectureNumber])) {
                $lectures[$lectureNumber] = [];
                $event_date = $_POST["course_date_1"];
                $_SESSION['errors']["course_date_1"] = validate_date($_POST["course_date_1"], "開催日", $required_flg); // バリデーションチェック;
                if (is_null($_SESSION['errors']["course_date_1"])) {
                    if (is_null($deadline) && !is_null($event_date)) {
                        $date = new DateTime($event_date);
                        $date->modify('-1days');
                        $deadline = $date->format('Y-m-d 23:59:59');
                    }
                    if (!$error_flg && !empty($deadline)) {
                        $_SESSION['errors']["course_date_1"] = validate_date_comparison($deadline, $event_date, '開催日', '申し込み締切日');
                    }
                }
            }

            $_SESSION['errors']["course_date_{$lectureNumber}"] = validate_date($_POST["course_date_{$lectureNumber}"], "開催日", $required_flg); // バリデーションチェック;
            $_SESSION['errors']["release_date_{$lectureNumber}"] = validate_date($_POST["release_date_{$lectureNumber}"], "アーカイブ公開日", false);
            $_SESSION['errors']["material_release_date_{$lectureNumber}"] = validate_date($_POST["material_release_date_{$lectureNumber}"], "講義資料公開日", false);
            if (empty($value)) {
                $_SESSION['errors']["tutor_id_{$lectureNumber}_{$itemNumber}"] = validate_select($value, "講師", false); // バリデーションチェック;
                $_SESSION['errors']["tutor_name_{$lectureNumber}_{$itemNumber}"] = validate_text($_POST["tutor_name_{$lectureNumber}_{$itemNumber}"], '講師名', 225, false); // バリデーションチェック;
            } else {
                $_SESSION['errors']["tutor_id_{$lectureNumber}_{$itemNumber}"] = validate_select($value, "講師", $required_flg); // バリデーションチェック;
                $_SESSION['errors']["tutor_name_{$lectureNumber}_{$itemNumber}"] = null;
            }
            $_SESSION['errors']["lecture_name_{$lectureNumber}_{$itemNumber}"] = validate_text($_POST["lecture_name_{$lectureNumber}_{$itemNumber}"], "講義名", 225, $required_flg); // バリデーションチェック;
            $_SESSION['errors']["program_{$lectureNumber}_{$itemNumber}"] = validate_textarea($_POST["program_{$lectureNumber}_{$itemNumber}"], "講義概要", $required_flg); // バリデーションチェック;

            // 第2講座以降の場合
            if ($lectureNumber > 1) {
                // 現在講座の開催日が一つ前の講座より前の日付になっていないかチェック 
                $before_no = $lectureNumber - 1;
                if (is_null($_SESSION['errors']["course_date_{$lectureNumber}"]) && is_null($_SESSION['errors']["course_date_{$before_no}"])) {
                    $before_course_date = $_POST["course_date_{$before_no}"];
                    $course_date = $_POST["course_date_{$lectureNumber}"];
                    $_SESSION['errors']["course_date_{$lectureNumber}"] = validate_date_comparison_not_same_day($before_course_date, $course_date, "第{$lectureNumber}講座", "第{$before_no}講座");
                }
            }
            if (
                !$error_flg
                && ($_SESSION['errors']["course_date_{$lectureNumber}"]
                    || $_SESSION['errors']["tutor_id_{$lectureNumber}_{$itemNumber}"]
                    || $_SESSION['errors']["lecture_name_{$lectureNumber}_{$itemNumber}"]
                    || $_SESSION['errors']["program_{$lectureNumber}_{$itemNumber}"]
                    || $_SESSION['errors']['all_deadline']
                    || $_SESSION['errors']['single_participation_fee']
                    || $_SESSION['errors']["tutor_name_{$lectureNumber}_{$itemNumber}"]
                    || $_SESSION['errors']["course_date_1"])
            ) {
                $error_flg = true;
                $_SESSION['old_input'] = $_POST;
                http_response_code(500);
                echo json_encode(['success' => false]);
                exit;
            }

            // アーカイブ公開日が入力されている場合
            if (!empty($_POST["release_date_{$lectureNumber}"])) {
                $release_date_input_flg = true; // 入力判定
            }
            if (!empty($_POST["material_release_date_{$lectureNumber}"])) {
                $material_release_date_input_flg = true; //入力判定
            }

            if (!$error_flg) {
                // 各講義の申込締切日を算出
                $course_date = optional_param("course_date_{$lectureNumber}", '', PARAM_RAW);
                $date = new DateTime($course_date);
                if ($all_deadline > 0) {
                    $date->modify('-' . $all_deadline . 'days');
                    $deadline_date = $date->format('Y-m-d 23:59:59'); // YYYY-MM-DD形式に変換
                } else {
                    $deadline_date = $date->format('Y-m-d ' . $end_hour); // YYYY-MM-DD形式に変換
                }

                // 各フィールドを収集
                if (empty($lectures[$lectureNumber])) {
                    $lectures[$lectureNumber] = [
                        'course_info_id' => $_POST["course_info_id_{$lectureNumber}"],
                        'course_date' => $_POST["course_date_{$lectureNumber}"],
                        'release_date' => empty($_POST["release_date_{$lectureNumber}"]) ? null : $_POST["release_date_{$lectureNumber}"],
                        'material_release_date' => empty($_POST["material_release_date_{$lectureNumber}"]) ? null : $_POST["material_release_date_{$lectureNumber}"],
                        'deadline_date' => $deadline_date
                    ];
                }
                $test = $_POST["tutor_name_{$lectureNumber}_{$itemNumber}"];
                $lectures[$lectureNumber]["detail"][$count] = [];
                $lectures[$lectureNumber]["detail"][$count] = [
                    'tutor_id' => empty($value) ? null : $value,
                    'lecture_name' => $_POST["lecture_name_{$lectureNumber}_{$itemNumber}"],
                    'program' => $_POST["program_{$lectureNumber}_{$itemNumber}"],
                    'tutor_name' => $_POST["tutor_name_{$lectureNumber}_{$itemNumber}"]
                ];
            }
            $count++;
        }
    }
} elseif ($event_kbn == EVERY_DAY_EVENT) {
    if (
        $_SESSION['errors']['start_event_date'] == null
        && $_SESSION['errors']['end_event_date'] == null
        && $_SESSION['errors']['end_hour'] == null
    ) {

        $_SESSION['errors']["release_date"] = validate_date($_POST["release_date"], "アーカイブ公開日", false);
        $_SESSION['errors']["material_release_date"] = validate_date($_POST["material_release_date"], "講義資料公開日", false);
        $count = 1;
        $detail = [];
        foreach ($_POST as $key => $value) {
            if (preg_match('/^tutor_id_(\d+)$/', $key, $matches)) {
                $lectureNumber = $matches[1]; // 講座番号

                if (empty($value)) {
                    $_SESSION['errors']["tutor_id_{$lectureNumber}"] = validate_select($value, '講師', false); // バリデーションチェック;
                    $_SESSION['errors']["tutor_name_{$lectureNumber}"] = validate_text($_POST["tutor_name_{$lectureNumber}"], '講師名', 225, false); // バリデーションチェック;
                } else {
                    $_SESSION['errors']["tutor_id_{$lectureNumber}"] = validate_select($value, '講師', true); // バリデーションチェック;
                    $_SESSION['errors']["tutor_name_{$lectureNumber}"] = null;
                }
                $_SESSION['errors']["lecture_name_{$lectureNumber}"] = validate_text($_POST["lecture_name_{$lectureNumber}"], '講義名', 225, true); // バリデーションチェック;
                $_SESSION['errors']["program_{$lectureNumber}"] = validate_textarea($_POST["program_{$lectureNumber}"], '講義概要', true); // バリデーションチェック;

                if (
                    !$error_flg
                    && ($_SESSION['errors']["tutor_id_{$lectureNumber}"]
                        || $_SESSION['errors']["lecture_name_{$lectureNumber}"]
                        || $_SESSION['errors']["tutor_name_{$lectureNumber}"]
                        || $_SESSION['errors']["program_{$lectureNumber}"]
                        || $_SESSION['errors']["release_date"]
                        || $_SESSION['errors']["material_release_date"])
                ) {
                    $error_flg = true;
                    $_SESSION['old_input'] = $_POST;
                    http_response_code(500);
                    echo json_encode(['success' => false]);
                    exit;
                }

                // アーカイブ公開日が入力されている場合
                if (!empty($_POST["release_date"])) {
                    $release_date_input_flg = true; // 入力判定
                }
                if (!empty($_POST["material_release_date"])) {
                    $material_release_date_input_flg = true; //入力判定
                }

                $courseDates = [];
                if (!$error_flg) {
                    // `start_event_date` と `end_event_date` を取得
                    if (!is_null($start_event_date)) {
                        $startDate = new DateTime($start_event_date);
                    }
                    if (!is_null($end_event_date)) {
                        $endDate = new DateTime($end_event_date);
                    }
                    $endHour = (int) $end_hour;

                    // 日付範囲内の全日を `course_date` に設定
                    if (!is_null($start_event_date) && !is_null($end_event_date)) {
                        while ($startDate <= $endDate) {
                            $courseDates[] = $startDate->format('Y-m-d'); // `YYYY-MM-DD` 形式で保存
                            $startDate->modify('+1 day'); // 1日ずつ増やす
                        }
                    }
                    $detail[] = [
                        'tutor_id' => empty($value) ? null : $value,
                        'lecture_name' => $_POST["lecture_name_{$lectureNumber}"],
                        'program' => $_POST["program_{$lectureNumber}"],
                        'tutor_name' => $_POST["tutor_name_{$lectureNumber}"]
                    ];
                }
            }
        }

        if (!empty($id)) {
            $stmt = $pdo->prepare("
                SELECT course_info_id
                FROM mdl_event_course_info 
                WHERE event_id = :event_id
            ");
            $stmt->execute([':event_id' => $id]);
            $eventCourseInfos = $stmt->fetchAll(PDO::FETCH_COLUMN); // course_info_id のリストを取得
        } else {
            $eventCourseInfos = null;
        }

        // 各 `course_date` ごとに `deadline_date` を設定
        foreach ($courseDates as $key => $courseDate) {
            $deadlineDate = new DateTime($courseDate);
            if ($event_kbn == EVERY_DAY_EVENT && !empty($all_deadline)) {
                $deadlineDate->modify('-' . $all_deadline . 'days');
            }
            $deadlineDate->setTime($endHour, 0, 0); // `end_hour` をセット

            if (isset($eventCourseInfos[$key])) {
                $course_info_id = $eventCourseInfos[$key];
            } else {
                $course_info_id = null;
            }

            $lectures[$count] = [
                'course_info_id' => $course_info_id,
                'course_date' => $courseDate,
                'release_date' => empty($_POST["release_date"]) ? null : $_POST["release_date"],
                'material_release_date' => empty($_POST["material_release_date"]) ? null : $_POST["material_release_date"],
                'deadline_date' => $deadlineDate->format('Y-m-d H:i:s') // `YYYY-MM-DD HH:MM:SS` 形式
            ];
            $lectures[$count]["detail"] = $detail;
            $count++;
        }
    }
}

if ($release_date_input_flg && empty($archive_streaming_period)) {
    $_SESSION['errors']['archive_streaming_period'] = "アーカイブ公開日が指定されています。アーカイブ配信期間を入力してください。";
} else if (!$release_date_input_flg && !empty($archive_streaming_period)) {
    $_SESSION['errors']['archive_streaming_period'] = "アーカイブ公開日を指定するか、アーカイブ配信期間の入力を削除してください。";
}
if ($material_release_date_input_flg && empty($material_release_period)) {
    $_SESSION['errors']['material_release_period'] = "講義資料公開日が指定されています。講義資料公開期間を入力してください。";
} else if (!$material_release_date_input_flg && !empty($material_release_period)) {
    $_SESSION['errors']['material_release_period'] = "講義資料公開日を指定するか、講義資料公開期間の入力を削除してください。";
}

// エラーがある場合
if (
    $_SESSION['errors']['name']
    || $_SESSION['errors']['description']
    || $_SESSION['errors']['category_id']
    || $_SESSION['errors']['lecture_format_id']
    || $_SESSION['errors']['venue_name']
    || $_SESSION['errors']['target']
    || $_SESSION['errors']['event_date']
    || $_SESSION['errors']['start_hour']
    || $_SESSION['errors']['end_hour']
    || $_SESSION['errors']['access']
    || $_SESSION['errors']['google_map']
    || $_SESSION['errors']['sponsor']
    || $_SESSION['errors']['co_host']
    || $_SESSION['errors']['sponsorship']
    || $_SESSION['errors']['cooperation']
    || $_SESSION['errors']['plan']
    || $_SESSION['errors']['capacity']
    || $_SESSION['errors']['participation_fee']
    || $_SESSION['errors']['tekijuku_discount']
    || $_SESSION['errors']['deadline']
    || $_SESSION['errors']['real_time_distribution_url']
    || $_SESSION['errors']['archive_streaming_period']
    || $_SESSION['errors']['event_customfield_category_id']
    || $_SESSION['errors']['event_survey_customfield_category_id']
    || $_SESSION['errors']['note']
    || $_SESSION['errors']['start_event_date']
    || $_SESSION['errors']['end_event_date']
    || $_SESSION['errors']['material_release_period']
    || $_SESSION['errors']['inquiry_mail']
    || $_SESSION['errors']['thumbnail_img']
    || $_SESSION['errors']['best_event_img']
    || $_SESSION['errors']['best_event_sp_img']
    || $_SESSION['errors']['scheduled_publish_at']
    || $error_flg
) {
    $_SESSION['old_input'] = $_POST;
    http_response_code(500);
    echo json_encode(['success' => false,]);
    exit;
}

// 画像を一時フォルダに保存する
if (!empty($thumbnail_img['name'])) {
    if ($thumbnail_img && $thumbnail_img['error'] === UPLOAD_ERR_OK) {
        // 一時ファイルと元のファイル情報を取得
        $tmpName = $thumbnail_img['tmp_name']; // 一時ファイルパス
        $originalName = pathinfo($thumbnail_img['name'], PATHINFO_FILENAME); // 元のファイル名
        $extension = pathinfo($thumbnail_img['name'], PATHINFO_EXTENSION);  // 拡張子

        // 保存先ディレクトリの設定
        $moodleDir = realpath(__DIR__ . '/../../../../../'); // Moodleのルートディレクトリ
        $uploadsDir = $moodleDir . '/uploads';
        $thumbnailsDir = $uploadsDir . '/tmp/thumbnails';
        $eventDir = $thumbnailsDir . '/' . $eventId;

        // 必要なディレクトリを順番に作成
        if (!file_exists($uploadsDir) && !is_dir($uploadsDir)) {
            $result = mkdir($uploadsDir, 0755, true);
            if (!$result) {
                $_SESSION['message_error'] = 'uploadsディレクトリの作成に失敗しました';
                $_SESSION['old_input'] = $_POST; // 入力内容も保持
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'システムエラー']);
                exit;
            }
        }

        if (!file_exists($thumbnailsDir) && !is_dir($thumbnailsDir)) {
            $result = mkdir($thumbnailsDir, 0755, true);
            if (!$result) {
                $_SESSION['message_error'] = 'thumbnailsディレクトリの作成に失敗しました';
                $_SESSION['old_input'] = $_POST; // 入力内容も保持
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'システムエラー']);
                exit;
            }
        }

        if (!file_exists($eventDir) && !is_dir($eventDir)) {
            $result = mkdir($eventDir, 0755, true);
            if (!$result) {
                $_SESSION['message_error'] = "イベント用ディレクトリの作成に失敗しました: $eventDir";
                $_SESSION['old_input'] = $_POST; // 入力内容も保持
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'システムエラー']);
                exit;
            }
        }

        // 1. 保存先ディレクトリの全ファイルを取得
        $allFiles = scandir($eventDir);

        // 保存先ファイルパスを生成
        $timestamp = date('YmdHis');
        $newFileName = "thumbnail_{$timestamp}.{$extension}";
        $destination = $eventDir . '/' . $newFileName;

        // ファイルを保存
        if (move_uploaded_file($tmpName, $destination)) {

            // ファイルURLを取得
            $relativePath = '/uploads/tmp/thumbnails/' . $eventId . '/' . $newFileName;
            $fileUrl = new moodle_url($relativePath);

            foreach ($allFiles as $file) {
                if ($file === '.' || $file === '..') {
                    continue; // カレントディレクトリと親ディレクトリをスキップ
                }
                if ($eventDir . $file != $relativePath) {
                    unlink($eventDir .  '/' . $file); // ファイルを削除
                }
            }
        }
    }
}

/* ▼ プレビュー用に表示するデータを整形し集計する ▼ */

// 開催回数と開催日を取得する
$select_course_list = [];
foreach ($lectures as $key => $lecture) {
    $select_course_list[$key] = ['no' => $key, 'course_date' => $lecture['course_date']];
}

// カテゴリーを取得する
$category_list = [];
foreach ($selectedCategories as $category_id) {
    $category_list[] = ['category_id' => $category_id];
}

// 開催ステータスを指定する
$event_status = null;
$first_event_date = null;
$now = new DateTime();
$current_time = new DateTime($now->format('H:i'));
foreach ($select_course_list as $no => $select_course) {
    $course_date = DateTime::createFromFormat('Y-m-d', $select_course['course_date']);
    if ($course_date === false) {
        continue;
    }
    if ($course_date->format('Y-m-d') === $now->format('Y-m-d')) {
        // 今日かつ時間内なら「開催中」
        if ($current_time >= new DateTime($start_hour) && $current_time <= new DateTime($end_hour)) {
            $event_status = $ongoing_event;
            break;
        }
    } elseif ($course_date > $now) {
        // 開催日が未来なら「開催前」
        $first_event_date = DateTime::createFromFormat('Y-m-d', $select_course['course_date']);
        $event_status = $upcoming_event;
    }
}
switch ($event_kbn) {
    case $single_event:
        $_SESSION['preview'][$preview_id] = [
            'name' => $name,
            'event_kbn' => $single_event,
            'is_top' => (int)$is_top,
            'categorys' => $category_list,
            'select_course' => $select_course_list,
            'event_status' => $event_status,
            'deadline_status_max' => 1, // 現状固定
            'thumbnail_img' => $relativePath,
            'first_event_date' => $first_event_date,
            'prev_event_id' => $id,
        ];
        break;
    case $plural_event:
        echo "公開済みです";
        break;
    case $every_day_event:
        echo "アーカイブ済みです";
        break;
    default:
        http_response_code(500);
        echo json_encode(['success' => false]);
        exit;
}

session_write_close();
echo json_encode([
    'success' => true,
    'preview_id' => $preview_id
]);
exit;
