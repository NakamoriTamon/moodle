<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/CategoryModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventApplicationModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventApplicationCourseInfoModel.php');

try {
    global $USER;
    global $DB;

    $categoryModel = new CategoryModel();
    $eventModel = new EventModel();
    $eventApplicationCourseInfo = new EventApplicationCourseInfoModel();

    // 検索項目取得
    $keyword = $_POST['keyword'] ?? null;
    $category_id = $_POST['category_id'] ?? null;
    $event_status_id = $_POST['event_status_id'] ?? null;
    $event_id = $_POST['event_id'] ?? null;
    $course_no = $_POST['course_no'] ?? null;
    $_SESSION['old_input'] = $_POST;

    // イベント選択時かつ他の選択肢が選択された際に対象イベントが含まれていなければ消す
    $first_filters = array_filter([
        'category_id' => $category_id,
        'event_status' => $event_status_id,
    ]);
    $first_filters = array_filter($first_filters);
    $found = false;
    if (!empty($first_filters) && !empty($event_id)) {
        $first_event_list = $eventModel->getEvents($first_filters, 1, 100000);
        foreach ($first_event_list as $first_event) {
            if ($event_id == $first_event['id']) {
                $found = true;
            }
        }
        if (!$found) {
            $event_id = $found ? $event_id : null;
        }
    }

    $filters = array_filter([
        'category_id' => $category_id,
        'event_status' => $event_status_id,
        'event_id' => $event_id,
        'course_no' => $course_no
    ]);

    $role = $DB->get_record('role_assignments', ['userid' => $USER->id]);

    // null の要素を削除しイベント検索
    $filters = array_filter($filters);
    $event_list = $eventModel->getEvents($filters, 1, 100000);

    // 部門管理者ログイン時は自身が作成したイベントのみを取得する
    if ($role->roleid == ROLE['COURSECREATOR']) {
        foreach ($event_list as $key => $event) {
            if ($event['userid'] != $USER->id) {
                unset($event_list[$key]);
            }
        }
    }

    $is_single = false;
    $course_info_id = null;

    // イベント情報を特定する
    foreach ($event_list as $event) {
        if (!empty($event_id)) {
            // 単発イベントの場合
            if ($event['event_kbn'] == SINGLE_EVENT) {
                foreach ($event['course_infos'] as $course_info) {
                    $course_info_id = $course_info['id'];
                    $course_no = 1;
                    $_SESSION['old_input']['course_no'] = "1";
                    $is_single = true;
                }
            }
            // 複数回イベントの場合
            elseif ($event['event_kbn'] == PLURAL_EVENT && !empty($course_no)) {
                foreach ($event['course_infos'] as $course_info) {
                    if ($course_info['no'] == $course_no) {
                        $course_info_id = $course_info['id'];
                    }
                }
            } elseif ($event['event_kbn'] == EVERY_DAY_EVENT) {
                foreach ($event['course_infos'] as $course_info) {
                    $course_info_id = "";
                    $course_no = "";
                    $_SESSION['old_input']['course_no'] = "";
                    $is_single = true;
                }
            }
        }
    }

    // IDの0を落とす
    if (is_numeric($keyword)) {
        $keyword = ltrim($keyword, '0');
    }

    $application_course_info_list = [];
    // 講義回数まで絞り込んだ場合
    if (!empty($course_info_id)) {
        // ページネーションを無視して全データを取得するため、大きな値を指定
        $application_course_info_list = $eventApplicationCourseInfo->getByCourseInfoId($course_info_id, $keyword, 1, 100000);
    }
    // イベント単位まで絞り込んだ場合
    else if (!empty($event_id)) {
        // ページネーションを無視して全データを取得するため、大きな値を指定
        $application_course_info_list = $eventApplicationCourseInfo->getByEventEventId($event_id, $keyword, 1, 100000);
    }
    // それ以外の場合（カテゴリやステータスだけで絞り込み、または絞り込みなし）
    else {
        // 該当するすべてのイベントIDを集める
        $event_ids = [];
        foreach ($event_list as $event) {
            $event_ids[] = $event['id'];
        }

        if (!empty($event_ids)) {
            // 配列内の各イベントIDに対してデータを取得し、結合
            foreach ($event_ids as $eid) {
                $results = $eventApplicationCourseInfo->getByEventEventId($eid, $keyword, 1, 100000);
                $application_course_info_list = array_merge($application_course_info_list, $results);
            }
        }
    }

    // 講座回数でソートする
    usort($application_course_info_list, function ($a, $b) {
        return $a['course_info']['no'] <=> $b['course_info']['no'];
    });

    // カスタムフィールド情報取得
    $customfield_header_list = [];
    if ($event_id) {
        $curstom_list = $DB->get_record('event', ['id' => $event_id]);
        $custom_id = $curstom_list->event_customfield_category_id;
        if (!empty($custom_id) && $custom_id > 0) {
            $custom_field_list = $DB->get_records('event_customfield', ['event_customfield_category_id' => $custom_id, 'is_delete' => 0]);
            usort($custom_field_list, function ($a, $b) {
                return (int)$a->sort - (int)$b->sort;
            });
            foreach ($custom_field_list as $custom_field) {
                $customfield_header_list[$custom_field->id] = $custom_field->name;
            }
        }
    }

    if ($is_single) {
        // CSVヘッダー
        $csv_list[0] = [
            'ID',
            'イベント名',
            '会員番号',
            'ユーザー名',
            'メールアドレス',
            '年齢',
            'その他',
            '備考',
            '決済方法',
            '決済状況',
            '決済日',
            '申込日',
            '参加状態'
        ];
    } else {
        // CSVヘッダー
        $csv_list[0] = [
            'ID',
            'イベント名',
            '講座回数',
            '会員番号',
            'ユーザー名',
            'メールアドレス',
            '年齢',
            'その他',
            '備考',
            '決済方法',
            '決済状況',
            '決済日',
            '申込日',
            '参加状態'
        ];
    }

    $insert_index = array_search('備考', $csv_list[0]);
    $insert_index++;
    array_splice($csv_list[0], $insert_index, 0, $customfield_header_list);

    // データの書き込み
    $count = 1;
    $path_name = '';
    foreach ($application_course_info_list as $application_course_info) {
        $application = reset($application_course_info['application']);
        $event = $application['event'];

        if (empty($path_name)) {
            $path_name = $event['name'];
            if (!empty($event['name']) && !empty($course_no)) {
                $path_name .= '_第' . $course_no . '回';
            }
        }

        $application_date = new DateTime($application['application_date']);
        $application_date = $application_date->format("Y年n月j日");

        $name = '';
        $user_id = '';
        $is_paid = '';
        $payment_type = '';
        $payment_date = '';
        $note = '';
        $age = null;

        // お連れ様の場合はユーザー情報は取得しない
        if ($application['user']['email'] == $application_course_info['participant_mail']) {
            $name = $application['user']['name'];
            $formatted_id = sprintf('%08d', $application["user"]['id']);
            $user_id = substr_replace($formatted_id, ' ', 4, 0);

            if ($application['pay_method'] != FREE_EVENT) {
                $payment_type = PAYMENT_SELECT_LIST[$application['pay_method']];
                $is_paid = !empty($application['payment_date']) ? '決済済' : '未決済';
                if (!empty($application['payment_date'])) {
                    $payment_date = new DateTime($application['payment_date']);
                    $payment_date = $payment_date->format("Y年n月j日");
                }
            }

            if ($application['note']) {
                $note = str_replace(",", " | ", $application['note']);
            }

            $age = getAge($application['user']['birthday']);
        } elseif (!empty($keyword)) {
            // キーワード検索時はお連れ様の情報も取得する
            continue;
        }

        $application_congnition = $DB->get_record('event_application_cognition', [
            'event_application_id' => $application_course_info['event_application_id']
        ]);

        $congnition_note = "";
        if ($application_congnition->note) {
            $congnition_note = str_replace(",", " | ", $application_congnition->note);
        }

        if ($is_single) {
            $csv_array = [
                $application_course_info['id'],
                $event['name'],
                $user_id,
                $name,
                $application_course_info['participant_mail'],
                $age,
                $congnition_note,
                $note,
                $payment_type,
                $is_paid,
                $payment_date,
                $application_date,
                IS_PARTICIPATION_LIST[$application_course_info['participation_kbn']]
            ];
        } else {
            $csv_array = [
                $application_course_info['id'],
                $event['name'],
                '第' . $application_course_info['course_info']['no'] . '講座',
                $user_id,
                $name,
                $application_course_info['participant_mail'],
                $age,
                $congnition_note,
                $note,
                $payment_type,
                $is_paid,
                $payment_date,
                $application_date,
                IS_PARTICIPATION_LIST[$application_course_info['participation_kbn']]
            ];
        }

        // カスタムフィールド回答結果を収集
        $application_customfield_list = [];
        foreach (array_keys($customfield_header_list) as $index) {
            $event_customfield_list = $DB->get_record('event_application_customfield', [
                'event_application_id' => $application_course_info['event_application_id'],
                'event_customfield_id' => $index
            ]);
            $application_customfield_list[$application_course_info['event_application_id']][$index] = $event_customfield_list->input_data;
        }

        $flattened_values = [];
        foreach ($application_customfield_list as $item) {
            if (is_array($item)) {
                foreach ($item as $v) {
                    $flattened_values[] = $v === null ? '' : $v;
                }
            } else {
                $flattened_values[] = $item === null ? '' : $item;
            }
        }
        array_splice($csv_array, $insert_index, 0, $flattened_values);

        $csv_list[$count] = $csv_array;
        $count++;
    }

    // 保存先のファイルパス
    $temp_dir = make_temp_directory('event_registration_export');
    $save_path = $temp_dir . "/event_registration_output.csv";

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
    header('Content-Disposition: attachment; filename="イベント登録情報_' . (!empty($path_name) ? $path_name . '_' : '') . date('YmdHi') . '.csv"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($save_path));

    readfile($save_path);
    unlink($save_path); // ファイルを削除
} catch (Exception $e) {
    try {
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = 'CSVファイルの出力に失敗しました';
        redirect('/custom/admin/app/Views/management/event_registration.php');
        exit;
    }
}

/**
 *  現在の年齢を取得する
 */
function getAge(?string $birthday = null): ?int
{
    if (empty($birthday)) {
        return null;
    }

    $birthday = new DateTime(substr($birthday, 0, 10));
    $today = new DateTime();
    $age = $today->diff($birthday)->y;
    return $age;
}
