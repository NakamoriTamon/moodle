<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/SurveyApplicationModel.php');
// 必要なモデルのrequire文

try {
    $transaction = $DB->start_delegated_transaction();

    $surveyApplicationModel = new SurveyApplicationModel();

    $course_info_id = $_POST['course_info_id'];
    $event_id = $_POST['event_id'];
    $_SESSION['old_input'] = $_POST;

    // アンケートデータの取得
    $survey_period = 0;
    $survey_list = [];
    $path_name = '';
    if (!empty($course_info_id) || !empty($event_id)) {
        $survey_list = $surveyApplicationModel->getSurveyApplications($course_info_id, $event_id, 1, 100000);
        if (!empty($course_info_id)) {
            $survey = reset($survey_list);
            $name = $survey['event']['name'];
            $no = $survey['course_info']['no'];
            $path_name = '第' . $no . '回_' . $name;
        } else {
            $path_name = $survey['event']['name'];
        }
    }

    // アンケート時間集計
    foreach ($survey_list as $survey) {
        $start = strtotime($survey['event']["start_hour"]);
        $end = strtotime($survey['event']["end_hour"]);
        $survey_period = ($end - $start) / 60;
    }

    // CSVヘッダー
    $csv_list[0] = [
        '回答時間',
        '回数',
        '本日の講義内容について、ご意見・ご感想をお書きください',
        '今までに大阪大学公開講座のプログラムに参加されたことはありますか',
        '本日のプログラムをどのようにしてお知りになりましたか',
        'その他',
        '本日のテーマを受講した理由は何ですか',
        'その他',
        '本日のプログラムの満足度について、あてはまるもの1つをお選びください',
        '本日のプログラムの理解度について、あてはまるもの1つをお選びください',
        '本日のプログラムで特に良かった点について教えてください。いかに当てはまるものがあれば、1つお選びください。あてはまるものがなければ「その他」の欄に記述してください',
        'その他',
        '本日のプログラムの開催時間(' . $survey_period . '分)についてあてはまるものを1つお選びください',
        '本日のプログラムの開催環境について、あてはまるものを１つお選びください。',
        '「あまり快適ではなかった」「全く快適ではなかった」と回答された方はその理由を教えてください。',
        '今後の大阪大学公開講座で、希望するジャンルやテーマ、話題があれば、ご提案ください',
        '話を聞いてみたい大阪大学の教員や研究者がいれば、具体的にご提案ください',
        'ご職業等を教えてください',
        '性別をご回答ください',
        'お住いの地域を教えてください（〇〇県△△市のようにご回答ください'
    ];

    // データの書き込み
    $count = 1;
    foreach ($survey_list as $survey) {
        $attend = DECISION_LIST[$survey['attend']]; // 参加経験
        $found_method = FOUND_METHOD_LIST[$survey['found_method']]; // プログラムを知った方法
        $reason = REASON_LIST[$survey['reason']]; // 受講理由
        $satisfaction = SATISFACTION_LIST[$survey['satisfaction']]; // 満足度
        $understanding = UNDERSTANDING_LIST[$survey['understanding']];  // 理解度
        $good_point = GOOD_POINT_LIST[$survey['good_point']];  // 良かった点
        $time = TIME_LIST[$survey['time']]; // 開催時間
        $holding_environment = HOLDING_ENVIRONMENT_LIST[$survey['holding_environment']]; // 開催環境
        $work = WORK_LIST[$survey['work']]; // 職業
        $sex = SEX_LIST[$survey['sex']];   // 性別

        $csv_array = [
            $survey['created_at'],
            $survey['course_info']['no'],
            $survey['thoughts'],
            $attend,
            $found_method,
            $survey['other_found_method'],
            $reason,
            $survey['other_reason'],
            $satisfaction,
            $understanding,
            $good_point,
            $survey['other_good_point'],
            $time,
            $holding_environment,
            $survey['no_good_enviroment_reason'],
            $survey['lecture_suggestions'],
            $survey['speaker_suggestions'],
            $work,
            $sex,
            $survey['prefectures'] . $survey['address']
        ];
        $csv_list[$count] = $csv_array;
        $count++;
    }

    // 保存先のファイルパス
    $temp_dir = make_temp_directory('survey_export');
    $save_path = $temp_dir . "/survey_output.csv";

    if (!is_writable(dirname($save_path))) {
        die("ディレクトリに書き込み権限がありません: " . dirname($save_path));
    }

    // ディレクトリがない場合は作成
    if (!is_dir(dirname($save_path))) {
        mkdir(dirname($save_path), 0777, true);
    }

    // ファイルを開く
    $fp = fopen($save_path, "w");
    if ($fp === false) {
        die("ファイルを開けませんでした");
    }

    // UTF-8 BOMを追加
    fwrite($fp, "\xEF\xBB\xBF");

    // データをCSVとして書き込み（カンマ区切り）
    foreach ($csv_list as $row) {
        $row = array_map(function ($val) {
            // 文字列がUTF-8でない場合にUTF-8に変換
            if (!mb_detect_encoding($val, "UTF-8", true)) {
                $val = mb_convert_encoding($val, "UTF-8");
            }
            return $val;
        }, $row);
        fputcsv($fp, $row);
    }

    fclose($fp);

    // ファイルのダウンロード
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $path_name . '_' . date('YmdHi') . '.csv"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($save_path));

    readfile($save_path);
    unlink($save_path); // ファイルを削除

    $transaction->allow_commit();
    header('Location: /custom/admin/app/Views/survey/index.php');
    exit;
} catch (Exception $e) {
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = 'CSVファイルの出力に失敗しました';
        redirect('/custom/admin/app/Views/survey/index.php');
        exit;
    }
}
