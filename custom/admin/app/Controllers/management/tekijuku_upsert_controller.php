<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');

// CSV登録項目リスト
$column_list = [
    '会員番号' => 'number',
    '会員種別コード' => 'type_code',
    '氏名' => 'name',
    'フリガナ' => 'kana',
    '性別コード' => 'sex',
    '郵便番号' => 'post_code',
    '住所' => 'address',
    '電話番号' => 'tell_number',
    'メールアドレス' => 'email',
    '『適塾』氏名掲載不可' => 'is_published',
    '支払方法' => 'payment_method',
    '支払日' => 'paid_date',
    '備考' => 'note',
    '2024（R6)' => 'is_deposit_2024',
    '2025（R7)' => 'is_deposit_2025',
    '2026（R8)' => 'is_deposit_2026',
    '2027（R9)' => 'is_deposit_2027',
    '2028（R10)' => 'is_deposit_2028',
    '2029（R11)' => 'is_deposit_2029',
    '2030（R12)' => 'is_deposit_2030',
];
// ハイフンを取り除くリスト
$hyphen_columns = [
    'post_code',
    'tell_number',
];
// 文字列を数値として変換するリスト
$integer_columns = [
    'number',
    'type_code',
    'post_code',
    'sex',
    'tell_number',
    'is_published',
    'payment_method',
    'is_deposit_2024',
    'is_deposit_2025',
    'is_deposit_2026',
    'is_deposit_2027',
    'is_deposit_2028',
    'is_deposit_2029',
    'is_deposit_2030',
];
// 状態カラム
$state_columns = [
    'is_published',
    'is_deposit_2024',
    'is_deposit_2025',
    'is_deposit_2026',
    'is_deposit_2027',
    'is_deposit_2028',
    'is_deposit_2029',
    'is_deposit_2030',
];

try {
    $transaction = $DB->start_delegated_transaction();
    // CSVファイルに変換した会員情報を既存のDBに登録する
    if ($_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file']['tmp_name'];
        if (($handle = fopen($file, 'r')) !== false) {
            $columns = fgetcsv($handle);
            $columns = array_map(fn($val) => mb_convert_encoding($val, 'UTF-8', 'SJIS-win'), $columns);
            while (($data = fgetcsv($handle)) !== false) {
                $data = array_map(fn($val) => mb_convert_encoding($val, 'UTF-8', 'SJIS-win'), $data);
                $params = array_combine($columns, $data);
                if ($params['会員番号'] == '') {
                    continue;
                }
                $tekijuku_commemoration = new stdClass();
                $tekijuku_commemoration->created_at = date('Y-m-d H:i:s');
                $tekijuku_commemoration->updated_at = date('Y-m-d H:i:s');
                $tekijuku_commemoration->payment_method = NULL;
                $tekijuku_commemoration->paid_date = NULL;
                foreach ($column_list as $key => $column) {
                    if (isset($params[$key])) {
                        $params[$key] = empty($params[$key]) ? null : $params[$key];
                        // ハイフンを取り除く 
                        if (in_array($column, $hyphen_columns) && $params[$key]) {
                            $params[$key] = str_replace('-', '', $params[$key]);
                        }
                        if (in_array($column, $integer_columns) && $params[$key]) {
                            $params[$key] = (int)$params[$key];
                        } else if (in_array($column, $state_columns)) {
                            // 状態はnullの場合は0を登録
                            $params[$key] = 0;
                        }
                        // 性別は入力されていなければその他にする
                        if ($column == 'sex' && $params[$key] == null) {
                            $params[$key] = 3;
                        }
                        $tekijuku_commemoration->$column = $params[$key];
                    }
                }
                $id = $DB->insert_record('tekijuku_commemoration', $tekijuku_commemoration);
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
