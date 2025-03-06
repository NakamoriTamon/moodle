<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');

use core\context\system;

try {
    $transaction = $DB->start_delegated_transaction();

    // 将来的にはユニークにするので下記制約は不要となる(確認中)
    $max_number = $DB->get_record_sql("
        SELECT number FROM {tekijuku_commemoration} 
        ORDER BY number DESC 
        LIMIT 1
    ");
    $max_number = $max_number->number + 1;

    // CSVファイルに変換した会員情報を取得する
    if ($_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file']['tmp_name'];
        $count = 0;
        if (($handle = fopen($file, 'r')) !== false) {
            $columns = fgetcsv($handle);
            $columns = array_map(fn($val) => mb_convert_encoding($val, 'UTF-8', 'SJIS-win'), $columns);
            while (($data = fgetcsv($handle)) !== false) {
                $count = $count + 1;
                $data = array_map(fn($val) => mb_convert_encoding($val, 'UTF-8', 'SJIS-win'), $data);
                $params = array_combine($columns, $data);
                if ($params['会員番号'] == '') {
                    continue;
                }

                $required_keys = ['氏名', 'ｶﾅ'];
                if ($missing_keys = array_diff($required_keys, array_keys($params))) {
                    throw new Exception('登録に失敗しました: ' . implode(', ', $missing_keys) . ' が不足しています');
                }

                // 登録項目を取得
                $unique_id = bin2hex(random_bytes(4));
                $name = $params['氏名'];
                // カナは全角に置換
                $kana = mb_convert_kana($params['ｶﾅ'], 'KV');
                $password = 'password-' . $unique_id;
                $email = 'dummy_email_' . $unique_id . '@mail.com';
                $phone = "";
                if (!empty($params['電話番号'])) {
                    $phones = explode('、', $params['電話番号']);
                    $phone = str_replace('-', '', $phones[0]);
                }

                // 動画プラットフォーム側ユーザー登録
                $record = new stdClass();
                $record->username = $name . uniqid();
                $record->password = password_hash($password, PASSWORD_DEFAULT);
                $record->email = $email;
                $record->phone1 = rtrim($phone, " 　");
                $record->lang = 'ja';
                $record->timecreated = time();
                $record->timemodified = time();
                $record->name = $name;
                $record->name_kana = $kana;
                $record->confirmed = 1;

                $id = $DB->insert_record_raw('user', $record, true);

                // 管理者ロールを割り当てる
                $admin_role = $DB->get_record('role', ['shortname' => 'user']);
                $context = system::instance(); // システムコンテキスト
                role_assign($admin_role->id, $id, $context->id);

                $tekijuku_required_keys = ['会員種別コード', '郵便番号'];
                if ($missing_keys = array_diff($tekijuku_required_keys, array_keys($params))) {
                    throw new Exception('登録に失敗しました: ' . implode(', ', $missing_keys) . ' が不足しています');
                }

                // 登録項目を取得
                $address = $params['住所'];
                $type_code = $params['会員種別コード'];

                if ($type_code == 3) {
                    $type_code = 1;
                }
                if ($type_code == 4) {
                    $type_code = 2;
                }
                if ($type_code == 1) {
                    $price = 2000;
                }
                if ($type_code == 2) {
                    $price = 10000;
                }

                // 枚数(仮)
                $unit = 1;
                $post_code = !empty($params['郵便番号']) ?  str_replace('-', '', $params['郵便番号']) : '';
                $is_published = !empty($params['『適塾』氏名掲載不可 ']) ? true : false;
                $note = $params['備考'];
                $department = $params['部局名'] ?? '';
                $major = $params['学科・専攻名'] ?? '';
                $official = $params['職名'] ?? '';
                $old_number = $params['会員番号'] ?? '';
                $is_deposit_2024 = !empty($params['2024（R6)']) ? true : false;
                $is_deposit_2025 = !empty($params['2025（R7)']) ? true : false;
                $is_deposit_2026 = !empty($params['2026（R8)']) ? true : false;
                $is_deposit_2027 = !empty($params['2027（R9)']) ? true : false;
                $is_deposit_2028 = !empty($params['2028（R10)']) ? true : false;
                $is_deposit_2029 = !empty($params['2029（R11)']) ? true : false;
                $is_deposit_2030 = !empty($params['2030（R12)']) ? true : false;
                $max_number = $max_number + 1;

                // 適塾記念会会員情報登録
                $tekijuku_commemoration = new stdClass();
                $tekijuku_commemoration->created_at = date('Y-m-d H:i:s');
                $tekijuku_commemoration->updated_at = date('Y-m-d H:i:s');
                $tekijuku_commemoration->type_code = (int)$type_code;
                $tekijuku_commemoration->number = (int)$max_number;
                $tekijuku_commemoration->name = $name;
                $tekijuku_commemoration->kana = $kana;
                $tekijuku_commemoration->post_code = rtrim($post_code, " 　");
                $tekijuku_commemoration->address = $adress;
                $tekijuku_commemoration->tell_number = $phone;
                $tekijuku_commemoration->email = $email;
                $tekijuku_commemoration->is_published = $is_published;
                $tekijuku_commemoration->note = $note;
                $tekijuku_commemoration->is_deposit_2024  = $is_deposit_2024;
                $tekijuku_commemoration->is_deposit_2025  = $is_deposit_2025;
                $tekijuku_commemoration->is_deposit_2026  = $is_deposit_2026;
                $tekijuku_commemoration->is_deposit_2027  = $is_deposit_2027;
                $tekijuku_commemoration->is_deposit_2028  = $is_deposit_2028;
                $tekijuku_commemoration->is_deposit_2029  = $is_deposit_2029;
                $tekijuku_commemoration->is_deposit_2030  = $is_deposit_2030;
                $tekijuku_commemoration->fk_user_id = $id;
                $tekijuku_commemoration->department = $department;
                $tekijuku_commemoration->major = $major;
                $tekijuku_commemoration->official = $official;
                $tekijuku_commemoration->old_number = (int)$old_number;
                $tekijuku_commemoration->is_temporary = 1;
                $tekijuku_commemoration->price = $price;
                $tekijuku_commemoration->unit = $unit;
                $tekijuku_commemoration->sex = 0; // 消すそのうち

                $DB->insert_record_raw('tekijuku_commemoration', $tekijuku_commemoration);
            }
            fclose($handle);
        } else {
            echo "CSVファイルの読み込みに失敗しました。";
        }
    } else {
        echo "ファイルのアップロードに失敗しました。";
    }
    $transaction->allow_commit();
    $_SESSION['message_success'] = '登録が完了しました';
    header('Location: /custom/admin/app/Views/management/tekijuku_registration.php');
    exit;
} catch (Exception $e) {
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = '登録に失敗しました';
        redirect('/custom/admin/app/Views/management/tekijuku_registration.php');
        exit;
    }
}
