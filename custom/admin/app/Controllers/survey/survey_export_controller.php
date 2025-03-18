<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/SurveyApplicationModel.php');
// 必要なモデルのrequire文

try {
    $transaction = $DB->start_delegated_transaction();

    // 検索条件の取得
    $filters = array_filter([
        'category_id' => $_POST['category_id'] ?? null,
        'event_status_id' => $_POST['event_status_id'] ?? null,
        'event_id' => $_POST['event_id'] ?? null,
    ]);

    // アンケートデータの取得
    $surveyApplicationModel = new SurveyApplicationModel();
    $survey_list = $surveyApplicationModel->getSurveyApplications($filters);

    // CSVヘッダー
    $csv_list[0] = [
        '回答時間',
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
        '本日のプログラムの開催時間(90分)についてあてはまるものを1つお選びください',
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
        // 参加経験
        $attend = $survey['attend'] == '1' ? 'はい' : 'いいえ';

        // プログラムを知った方法
        $found_method = match($survey['found_method']) {
            '1' => 'チラシ',
            '2' => 'ウェブサイト',
            '3' => '大阪大学公開講座「知の広場」からのメール',
            '4' => 'SNS（X, Instagram, Facebookなど）',
            '5' => '21世紀懐徳堂からのメールマガジン',
            '6' => '大阪大学卒業生メールマガジン',
            '7' => '大阪大学入試課からのメール',
            '8' => 'Peatixからのメール',
            '9' => '知人からの紹介',
            '10' => '講師・スタッフからの紹介',
            '11' => '自治体の広報・掲示',
            '12' => 'スマートニュース広告',
            default => ''
        };

        // 受講理由
        $reason = match($survey['reason']) {
            '1' => 'テーマに関心があったから',
            '2' => '本日のプログラム内容に関心があったから',
            '3' => '本日のゲストに関心があったから',
            '4' => '大阪大学のプログラムに参加したかったから',
            '5' => '教養を高めたいから',
            '6' => '仕事に役立つと思われたから',
            '7' => '日常生活に役立つと思われたから',
            '8' => '余暇を有効に利用したかったから',
            default => ''
        };

        // 満足度
        $satisfaction = match($survey['satisfaction']) {
            1 => '非常に満足',
            2 => '満足',
            3 => 'ふつう',
            4 => '不満',
            5 => '非常に不満',
            default => ''
        };

        // 理解度
        $understanding = match($survey['understanding']) {
            1 => 'よく理解できた',
            2 => '理解できた',
            3 => 'ふつう',
            4 => '理解できなかった',
            5 => '全く理解できなかった',
            default => ''
        };

        // 良かった点
        $good_point = match($survey['good_point']) {
            1 => 'テーマについて考えを深めることができた',
            2 => '最先端の研究について学べた',
            3 => '大学の研究者と対話ができた',
            4 => '大学の講義の雰囲気を味わえた',
            5 => '大阪大学について知ることができた',
            6 => '身の周りの社会課題に対する解決のヒントが得られた',
            default => ''
        };

        // 開催時間
        $time = match($survey['time']) {
            1 => '適当である',
            2 => '長すぎる',
            3 => '短すぎる',
            default => ''
        };

        // 開催環境
        $holding_environment = match($survey['holding_environment']) {
            1 => 'とても快適だった',
            2 => '快適だった',
            3 => 'ふつう',
            4 => 'あまり快適ではなかった',
            5 => '全く快適ではなかった',
            default => ''
        };

        // 職業
        $work = match($survey['work']) {
            '1' => '高校生以下',
            '2' => '学生（高校生、大学生、大学院生等）',
            '3' => '会社員',
            '4' => '自営業・フリーランス',
            '5' => '公務員',
            '6' => '教職員',
            '7' => 'パート・アルバイト',
            '8' => '主婦・主夫',
            '9' => '定年退職',
            '10' => 'その他',
            default => ''
        };

        // 性別
        $sex = match($survey['sex']) {
            1 => '男性',
            2 => '女性',
            3 => 'その他',
            default => ''
        };

        $csv_array = [
            $survey['created_at'],
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
    header('Content-Disposition: attachment; filename="survey_list_' . date('YmdHis') . '.csv"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($save_path));
    
    readfile($save_path);
    unlink($save_path); // ファイルを削除

    $transaction->allow_commit();
    // $_SESSION['message_success'] = 'CSVファイルのダウンロードが完了しました';
    header('Location: /custom/admin/app/Views/survey/index.php');
    exit;

} catch (Exception $e) {
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        // $_SESSION['message_error'] = 'CSVファイルの出力に失敗しました';
        redirect('/custom/admin/app/Views/survey/index.php');
        exit;
    }
} 