<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/TargetModel.php');
require_once($CFG->libdir . '/filelib.php');

$targetModel = new TargetModel();
$targets = $targetModel->getTargets();

global $USER, $DB;
$userid = $USER->id;
$event_kbns = EVENT_KBN_LIST;
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
$access = $_POST['access'] ?? null; // 交通アクセス
$_SESSION['errors']['access'] = validate_text($access, '交通アクセス', 500, false); // バリデーションチェック
$google_map = $_POST['google_map'] ?? null; // Google Map
$_SESSION['errors']['google_map'] = validate_google_map($google_map, 'Google Map', false);
$is_top = !isset($_POST['is_top']) ? 0 : $_POST['is_top']; // トップに固定
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
$event_survey_custom_id = empty($_POST['event_survey_custom_id']) ? 0 : $_POST['event_survey_custom_id']; // アンケートカスタム区分
$_SESSION['errors']['event_survey_custom_id'] = validate_select($event_survey_custom_id, 'アンケートカスタム区分', false); // バリデーションチェック
$note = $_POST['note'] ?? null; // その他
$_SESSION['errors']['note'] = validate_textarea($note, 'その他', false); // バリデーションチェック


// 講師、講義名、講義概要のデータ構造
$lectures = [];
$error_flg = false;

// 接続情報取得
$baseModel = new BaseModel();
$eventModel = new EventModel();
$pdo = $baseModel->getPdo();

$pdo->beginTransaction();

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
            }

            // アーカイブ公開日が入力されている場合
            if(!empty($_POST["release_date"])) {
                $release_date_input_flg = true; // 入力判定
            }
            if(!empty($_POST["material_release_date"])) {
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
            }

            // アーカイブ公開日が入力されている場合
            if(!empty($_POST["release_date_{$lectureNumber}"])) {
                $release_date_input_flg = true; // 入力判定
            }
            if(!empty($_POST["material_release_date_{$lectureNumber}"])) {
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
                    $_SESSION['errors']["tutor_name_{$lectureNumber}"] = validate_text($_POST["tutor_name_{$lectureNumber}"], '講師名', 225, true); // バリデーションチェック;
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
                        || $_SESSION['errors']["program_{$lectureNumber}"]
                        || $_SESSION['errors']["release_date"]
                        || $_SESSION['errors']["material_release_date"])
                ) {
                    $error_flg = true;
                }
                    
                // アーカイブ公開日が入力されている場合
                if(!empty($_POST["release_date"])) {
                    $release_date_input_flg = true; // 入力判定
                }
                if(!empty($_POST["material_release_date"])) {
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

if($release_date_input_flg && empty($archive_streaming_period)) {
    $_SESSION['errors']['archive_streaming_period'] = "アーカイブ公開日が指定されています。アーカイブ配信期間を入力してください。";
} else if(!$release_date_input_flg && !empty($archive_streaming_period)) {
    $_SESSION['errors']['archive_streaming_period'] = "アーカイブ公開日を指定するか、アーカイブ配信期間の入力を削除してください。";
}
if($material_release_date_input_flg && empty($material_release_period)) {
    $_SESSION['errors']['material_release_period'] = "講義資料公開日が指定されています。講義資料公開期間を入力してください。";
} else if(!$material_release_date_input_flg && !empty($material_release_period)) {
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
    || $_SESSION['errors']['event_survey_custom_id']
    || $_SESSION['errors']['note']
    || $_SESSION['errors']['start_event_date']
    || $_SESSION['errors']['end_event_date']
    || $_SESSION['errors']['material_release_period']
    || $_SESSION['errors']['inquiry_mail']
    || $error_flg
) {
    $_SESSION['old_input'] = $_POST; // 入力内容も保持
    if ($id) {
        header('Location: /custom/admin/app/Views/event/upsert.php?id=' . $id);
    } else {
        header('Location: /custom/admin/app/Views/event/upsert.php');
    }
    exit;
}

// データの確認 (デバッグ用)
// print_r($lectures);

$isTop = 1;
$isDoubleSpeed = 1;
$createdAt = date('Y-m-d H:i:s');
$updatedAt = date('Y-m-d H:i:s');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
        header('Location: /custom/admin/app/Views/event/index.php');
        return;
    }
}

try {

    if (!empty($id)) {
        $stmt = $pdo->prepare("
            UPDATE mdl_event
            SET 
                name = :name,
                description = :description,
                event_date = :event_date,
                start_hour = :start_hour,
                end_hour = :end_hour,
                target = :target,
                venue_name = :venue_name,
                access = :access,
                google_map = :google_map,
                is_top = :is_top,
                program = :program,
                sponsor = :sponsor,
                co_host = :co_host,
                sponsorship = :sponsorship,
                cooperation = :cooperation,
                plan = :plan,
                capacity = :capacity,
                participation_fee = :participation_fee,
                single_participation_fee = :single_participation_fee,
                deadline = :deadline,
                all_deadline = :all_deadline,
                archive_streaming_period = :archive_streaming_period,
                is_double_speed = :is_double_speed,
                note = :note,
                event_kbn = :event_kbn,
                event_customfield_category_id = :event_customfield_category_id,
                event_survey_custom_id = :event_survey_custom_id,
                is_apply_btn = :is_apply_btn,
                start_event_date = :start_event_date,
                end_event_date = :end_event_date,
                tekijuku_discount = :tekijuku_discount,
                real_time_distribution_url = :real_time_distribution_url,
                material_release_period = :material_release_period,
                inquiry_mail = :inquiry_mail,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':event_date' => $event_date,
            ':start_hour' => $start_hour,
            ':end_hour' => $end_hour,
            ':target' => $target,
            ':venue_name' => $venue_name,
            ':access' => $access,
            ':google_map' => $google_map,
            ':is_top' => $is_top,
            ':program' => $program,
            ':sponsor' => $sponsor,
            ':co_host' => $co_host,
            ':sponsorship' => $sponsorship,
            ':cooperation' => $cooperation,
            ':plan' => $plan,
            ':capacity' => $capacity,
            ':participation_fee' => $participation_fee,
            ':single_participation_fee' => $single_participation_fee,
            ':deadline' => $deadline,
            ':all_deadline' => $all_deadline,
            ':archive_streaming_period' => $archive_streaming_period,
            ':is_double_speed' => $is_double_speed,
            ':note' => $note,
            ':event_kbn' => $event_kbn,
            ':event_customfield_category_id' => $event_customfield_category_id,
            ':event_survey_custom_id' => $event_survey_custom_id,
            ':is_apply_btn' => $is_apply_btn,
            ':start_event_date' => $start_event_date,
            ':end_event_date' => $end_event_date,
            ':tekijuku_discount' => $tekijuku_discount,
            ':real_time_distribution_url' => $real_time_distribution_url,
            ':material_release_period' => $material_release_period,
            ':inquiry_mail' => $inquiry_mail,
            ':id' => $id // 一意の識別子をWHERE条件として設定
        ]);

        $eventId = $id;
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO mdl_event (
                userid, name, description
                , event_date, start_hour, end_hour, target, venue_name, access
                , google_map, is_top, program, sponsor, co_host, sponsorship, cooperation, plan, capacity
                , participation_fee, single_participation_fee, deadline, all_deadline, archive_streaming_period, is_double_speed, note, thumbnail_img
                , created_at, updated_at, event_kbn, event_customfield_category_id, event_survey_custom_id, is_apply_btn, start_event_date, end_event_date
                , tekijuku_discount, real_time_distribution_url, material_release_period, inquiry_mail
            ) 
            VALUES (
                :userid, :name, :description
                , :event_date, :start_hour, :end_hour, :target, :venue_name, :access
                , :google_map, :is_top, :program, :sponsor, :co_host, :sponsorship, :cooperation, :plan, :capacity
                , :participation_fee, :single_participation_fee, :deadline, :all_deadline, :archive_streaming_period, :is_double_speed, :note, :thumbnail_img
                , CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :event_kbn, :event_customfield_category_id, :event_survey_custom_id, :is_apply_btn, :start_event_date, :end_event_date
                , :tekijuku_discount, :real_time_distribution_url, :material_release_period, :inquiry_mail
            )
        ");

        $stmt->execute([
            'userid' => $userid,
            ':name' => $name,
            ':description' => $description,
            ':event_date' => $event_date,
            ':start_hour' => $start_hour,
            ':end_hour' => $end_hour,
            ':target' => $target,
            ':venue_name' => $venue_name,
            ':access' => $access,
            ':google_map' => $google_map,
            ':is_top' => $is_top,
            ':program' => $program,
            ':sponsor' => $sponsor,
            ':co_host' => $co_host,
            ':sponsorship' => $sponsorship,
            ':cooperation' => $cooperation,
            ':plan' => $plan,
            ':capacity' => $capacity,
            ':participation_fee' => $participation_fee,
            ':single_participation_fee' => $single_participation_fee,
            ':deadline' => $deadline,
            ':all_deadline' => $all_deadline,
            ':archive_streaming_period' => $archive_streaming_period,
            ':is_double_speed' => $is_double_speed,
            ':note' => $note,
            ':thumbnail_img' => "",
            ':event_kbn' => $event_kbn,
            ':event_customfield_category_id' => $event_customfield_category_id,
            ':event_survey_custom_id' => $event_survey_custom_id,
            ':is_apply_btn' => $is_apply_btn,
            ':start_event_date' => $start_event_date,
            ':end_event_date' => $end_event_date,
            ':tekijuku_discount' => $tekijuku_discount,
            ':real_time_distribution_url' => $real_time_distribution_url,
            ':material_release_period' => $material_release_period,
            ':inquiry_mail' => $inquiry_mail
        ]);

        // mdl_eventの挿入IDを取得
        $eventId = $pdo->lastInsertId();
    }
    if (empty($eventId) || (!empty($eventId) && !empty($thumbnail_img['name']))) {
        if ($thumbnail_img && $thumbnail_img['error'] === UPLOAD_ERR_OK) {
            // 一時ファイルと元のファイル情報を取得
            $tmpName = $thumbnail_img['tmp_name']; // 一時ファイルパス
            $originalName = pathinfo($thumbnail_img['name'], PATHINFO_FILENAME); // 元のファイル名
            $extension = pathinfo($thumbnail_img['name'], PATHINFO_EXTENSION);  // 拡張子

            // 保存先ディレクトリの設定
            $moodleDir = realpath(__DIR__ . '/../../../../../'); // Moodleのルートディレクトリ
            $uploadsDir = $moodleDir . '/uploads';
            $thumbnailsDir = $uploadsDir . '/thumbnails';
            $eventDir = $thumbnailsDir . '/' . $eventId;

            // 必要なディレクトリを順番に作成
            if (!file_exists($uploadsDir) && !is_dir($uploadsDir)) {
                $result = mkdir($uploadsDir, 0755, true);
                if (!$result) {
                    $_SESSION['message_error'] = 'uploadsディレクトリの作成に失敗しました';
                    $_SESSION['old_input'] = $_POST; // 入力内容も保持
                    header('Location: /custom/admin/app/Views/event/upsert.php?id=' . $eventId);
                    return;
                }
            }

            if (!file_exists($thumbnailsDir) && !is_dir($thumbnailsDir)) {
                $result = mkdir($thumbnailsDir, 0755, true);
                if (!$result) {
                    $_SESSION['message_error'] = 'thumbnailsディレクトリの作成に失敗しました';
                    $_SESSION['old_input'] = $_POST; // 入力内容も保持
                    header('Location: /custom/admin/app/Views/event/upsert.php?id=' . $eventId);
                    return;
                }
            }

            if (!file_exists($eventDir) && !is_dir($eventDir)) {
                $result = mkdir($eventDir, 0755, true);
                if (!$result) {
                    $_SESSION['message_error'] = "イベント用ディレクトリの作成に失敗しました: $eventDir";
                    $_SESSION['old_input'] = $_POST; // 入力内容も保持
                    header('Location: /custom/admin/app/Views/event/upsert.php?id=' . $eventId);
                    return;
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
                $relativePath = '/uploads/thumbnails/' . $eventId . '/' . $newFileName;
                $fileUrl = new moodle_url($relativePath);

                foreach ($allFiles as $file) {
                    if ($file === '.' || $file === '..') {
                        continue; // カレントディレクトリと親ディレクトリをスキップ
                    }
                    if ($eventDir . $file != $relativePath) {
                        unlink($eventDir .  '/' . $file); // ファイルを削除
                    }
                }

                // データベースに保存する場合
                $stmt = $pdo->prepare("
                    UPDATE mdl_event
                    SET 
                        thumbnail_img = :thumbnail_img,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id
                ");

                $stmt->execute([
                    ':thumbnail_img' => $fileUrl, // ファイルURLを保存
                    ':id' => $eventId // イベントID
                ]);
            } else {
                $_SESSION['message_error'] = "ファイルの保存に失敗しました: $destination";
                $_SESSION['old_input'] = $_POST; // 入力内容も保持
                header('Location: /custom/admin/app/Views/event/upsert.php');
                return;
            }
        } else {
            $_SESSION['message_error'] = "アップロードに失敗しました。エラーコード: " . $thumbnail_img['error'];
            $_SESSION['old_input'] = $_POST; // 入力内容も保持
            header('Location: /custom/admin/app/Views/event/upsert.php');
            return;
        }
    }

    // $eventIdに紐づくデータを削除
    $stmt = $pdo->prepare("DELETE FROM mdl_event_category WHERE event_id = :event_id");
    $stmt->execute([':event_id' => $eventId]); // 削除対象のevent_id


    // カテゴリー登録処理
    foreach ($selectedCategories as $key => $category_id) {
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
            ':event_id' => $eventId, // mdl_eventの挿入IDを使用
            ':category_id' => $category_id
        ]);
    }

    // $eventIdに紐づくデータを削除
    $stmt = $pdo->prepare("DELETE FROM mdl_event_lecture_format WHERE event_id = :event_id");
    $stmt->execute([':event_id' => $eventId]); // 削除対象のevent_id

    // 講義形式登録処理
    foreach ($lecture_format_ids as $key => $lecture_format_id) {
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
            ':event_id' => $eventId, // mdl_eventの挿入IDを使用
            ':lecture_format_id' => $lecture_format_id
        ]);
    }

    // 講座登録登録処理
    foreach ($lectures as $key => $lecture) {
        if (!empty($lecture['course_info_id'])) {
            $courseInfoId = $lecture['course_info_id'];

            if ($event_kbn != EVERY_DAY_EVENT) {
                $stmt = $pdo->prepare("
                    UPDATE mdl_course_info
                    SET 
                        course_date = :course_date,
                        release_date = :release_date,
                        deadline_date = :deadline_date,
                        material_release_date = :material_release_date,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id
                ");

                $stmt->execute([
                    ':course_date' => $lecture["course_date"],
                    ':release_date' => $lecture["release_date"],
                    ':deadline_date' => $lecture["deadline_date"],
                    ':material_release_date' => $lecture["material_release_date"],
                    ':id' => $courseInfoId
                ]);
            }

            // **mdl_course_info_detail の削除**
            $stmt = $pdo->prepare("
                DELETE FROM mdl_course_info_detail 
                WHERE course_info_id = :course_info_id
            ");
            $stmt->execute([':course_info_id' => $courseInfoId]);
        } else {
            // mdl_courseへのINSERT
            $stmt = $pdo->prepare("
                INSERT INTO mdl_course_info (
                    created_at, updated_at, no, course_date, release_date, deadline_date, material_release_date
                )
                VALUES (
                    CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :no, :course_date, :release_date, :deadline_date, :material_release_date
                )
            ");

            $stmt->execute([
                ':no' => $key,
                ':course_date' => $lecture["course_date"],
                ':release_date' => $lecture["release_date"],
                ':deadline_date' => $lecture["deadline_date"],
                ':material_release_date' => $lecture["material_release_date"],
            ]);
            $courseInfoId = $pdo->lastInsertId();
        }

        // 講座詳細登録処理
        foreach ($lecture["detail"] as $key => $detail) {
            // mdl_course_info_detailへのINSERT
            $stmt = $pdo->prepare("
                INSERT INTO mdl_course_info_detail (
                    created_at, updated_at, course_info_id, tutor_id, name, program, tutor_name
                )
                VALUES (
                    CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :course_info_id, :tutor_id, :name, :program, :tutor_name
                )
            ");

            $tutor_id = empty($detail["tutor_id"]) ? null : $detail["tutor_id"];
            $tutor_name = null;
            if (empty($tutor_id)) {
                $tutor_name = empty($detail["tutor_name"]) ? "" : $detail["tutor_name"];
            }
            $stmt->execute([
                ':course_info_id' => $courseInfoId,
                ':tutor_id' => $tutor_id,
                ':name' => $detail["lecture_name"],
                ':program' => $detail["program"],
                ':tutor_name' => $tutor_name,
            ]);
        }

        // 講義IDがない場合
        if (empty($lecture['course_info_id'])) {
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
                ':event_id' => $eventId, // mdl_eventの挿入IDを使用
                ':course_info_id' => $courseInfoId
            ]);
        }
    }

    $pdo->commit();

    if (empty($id)) { // 新規登録の場合のみメールマガジン送信
        exec("php /var/www/html/moodle/custom/app/scripts/send_event_notification.php --eventid={$eventId} > /dev/null 2>&1 &");
    }

    $_SESSION['message_success'] = '登録が完了しました';
    header('Location: /custom/admin/app/Views/event/index.php');
} catch (PDOException $e) {
    try {
        $pdo->rollBack();
        $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
        header('Location: /custom/admin/app/Views/event/index.php');
        exit;
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
        redirect('/custom/admin/app/Views/event/index.php');
        exit;
    }
}
