<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');
require_once($CFG->dirroot . '/custom/app/Models/SurveyApplicationModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventSurveyCustomFieldModel.php');
require_once($CFG->dirroot . '/custom/app/Models/SurveyApplicationCustomfieldModel.php');

try {
    $delimiter = "|";
    $surveyApplicationModel = new SurveyApplicationModel();
    $eventModel = new EventModel();
    $eventSurveyCustomFieldModel = new EventSurveyCustomFieldModel();
    $surveyApplicationCustomfieldModel = new SurveyApplicationCustomfieldModel();

    $course_info_id = $_POST['course_info_id'] ?? null;
    $event_id = $_POST['event_id'] ?? null;
    $_SESSION['old_input'] = $_POST;

    $survey_period = 0;
    $survey_list   = [];
    $path_name     = '';

    if (!empty($course_info_id) || !empty($event_id)) {
        $survey_list = $surveyApplicationModel->getSurveyApplications($course_info_id, $event_id, 1, 100000);
        if (!empty($survey_list)) {
            $survey = reset($survey_list);
            if (!empty($course_info_id)) {
                $name      = $survey['event']['name'];
                $no        = $survey['course_info']['no'];
                $path_name = '第' . $no . '回_' . $name;
            } else {
                $path_name = $survey['event']['name'];
            }
        }
    }

    $event = null;
    if (!empty($event_id)) {
        $event = $eventModel->getEventById($event_id);
    }

    $survey_field_list = [];
    if (!empty($event) && !empty($event['event_survey_customfield_category_id'])) {
        $survey_field_list = $eventSurveyCustomFieldModel->getEventSurveyCustomFieldById(
            $event['event_survey_customfield_category_id']
        );
    }

    $csv_list   = [];
    $csv_list[] = [
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

    foreach ($survey_field_list as $field) {
        $csv_list[0][] = $field['name'];
    }

    $count = 1;

    foreach ($survey_list as $survey) {
        $start         = strtotime($survey['event']["start_hour"]);
        $end           = strtotime($survey['event']["end_hour"]);
        $survey_period = ($end - $start) / 60;

        $attend       = !empty(DECISION_LIST[$survey['attend']]) ? DECISION_LIST[$survey['attend']] : '';
        $found_method = '';
        if (!empty($survey['found_method'])) {
            $found_num_list = array_map('trim', explode(",", $survey['found_method']));
            foreach ($found_num_list as $index => $row) {
                $found_method .= ($index > 0 ? $delimiter : '') . FOUND_METHOD_LIST[$row];
            }
        }
        $reason = '';
        if (!empty($survey['reason'])) {
            $reason_num_list = array_map('trim', explode(",", $survey['reason']));
            foreach ($reason_num_list as $index => $row) {
                $reason .= ($index > 0 ? $delimiter : '') . REASON_LIST[$row];
            }
        }

        $satisfaction           = !empty(SATISFACTION_LIST[$survey['satisfaction']]) ? SATISFACTION_LIST[$survey['satisfaction']] : '';
        $understanding          = !empty(UNDERSTANDING_LIST[$survey['understanding']]) ? UNDERSTANDING_LIST[$survey['understanding']] : '';
        $good_point             = !empty(GOOD_POINT_LIST[$survey['good_point']]) ? GOOD_POINT_LIST[$survey['good_point']] : '';
        $time                   = !empty(TIME_LIST[$survey['time']]) ? TIME_LIST[$survey['time']] : '';
        $holding_environment    = !empty(HOLDING_ENVIRONMENT_LIST[$survey['holding_environment']]) ? HOLDING_ENVIRONMENT_LIST[$survey['holding_environment']] : '';
        $work                   = !empty(WORK_LIST[$survey['work']]) ? WORK_LIST[$survey['work']] : '';
        $sex                    = !empty(SEX_LIST[$survey['sex']]) ? SEX_LIST[$survey['sex']] : '';
        $address_combined       = ($survey['prefectures'] ?? '') . ($survey['address'] ?? '');

        $list = $surveyApplicationCustomfieldModel->getESurveyApplicationCustomfieldBySurveyApplicationId($survey['id']);

        $customValueMap = [];
        if (!empty($list)) {
            foreach ($list as $cf) {
                $customValueMap[$cf['event_survey_customfield_id']] = [
                    'field_type' => $cf['field_type'],
                    'input_data' => $cf['input_data']
                ];
            }
        }

        $csv_array = [
            $survey['created_at'],
            '第' . $survey['course_info']['no'] . '回' ?? '',
            $survey['thoughts'] ?? '',
            $attend,
            $found_method,
            $survey['other_found_method'] ?? '',
            $reason,
            $survey['other_reason'] ?? '',
            $satisfaction,
            $understanding,
            $good_point,
            $survey['other_good_point'] ?? '',
            $time,
            $holding_environment,
            $survey['no_good_environment_reason'] ?? '',
            $survey['lecture_suggestions'] ?? '',
            $survey['speaker_suggestions'] ?? '',
            $work,
            $sex,
            $address_combined
        ];

        foreach ($survey_field_list as $field) {
            $fieldId   = $field['id'];
            $fieldData = '';

            if (isset($customValueMap[$fieldId])) {
                $fieldType = $customValueMap[$fieldId]['field_type'];
                $inputData = $customValueMap[$fieldId]['input_data'];

                if ($fieldType == 3) {
                    $fieldData = str_replace(',', '|', $inputData);
                } else {
                    $fieldData = $inputData;
                }
            }

            $csv_array[] = $fieldData;
        }

        $csv_list[$count] = $csv_array;
        $count++;
    }

    $temp_dir  = make_temp_directory('survey_export');
    $save_path = $temp_dir . "/survey_output.csv";

    if (!is_writable(dirname($save_path))) {
        die("ディレクトリに書き込み権限がありません: " . dirname($save_path));
    }
    if (!is_dir(dirname($save_path))) {
        mkdir(dirname($save_path), 0777, true);
    }

    $fp = fopen($save_path, "w");
    if ($fp === false) {
        die("ファイルを開けませんでした");
    }

    fwrite($fp, "\xEF\xBB\xBF");

    foreach ($csv_list as $row) {
        $row = array_map(function ($val) {
            if (!mb_detect_encoding($val, "UTF-8", true)) {
                $val = mb_convert_encoding($val, "UTF-8");
            }
            return $val;
        }, $row);
        fputcsv($fp, $row);
    }

    fclose($fp);

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $path_name . '_' . date('YmdHi') . '.csv"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($save_path));

    readfile($save_path);
    unlink($save_path);
} catch (Exception $e) {
    $_SESSION['message_error'] = 'CSVファイルの出力に失敗しました';
    redirect('/custom/admin/app/Views/survey/index.php');
    exit;
}
