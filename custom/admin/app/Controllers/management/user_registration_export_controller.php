<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/UserModel.php');

try {
    $userModel = new UserModel();

    // POSTから検索キーワードを取得
    $keyword = $_POST['keyword'] ?? null;
    if (!empty($keyword) && preg_match('/^0.*0/', $keyword)) {
        $keyword = preg_replace('/^0.*0/', '', $keyword);
    }

    $_SESSION['old_input'] = $_POST;

    $filters = [];
    if (!empty($keyword)) {
        $filters['keyword'] = $keyword;
    }

    // ユーザーデータの全件取得（ページネーションを無視して全データを取得）
    $user_count_list = $userModel->getAllUsersForExport($filters);
    $user_list = [];

    foreach ($user_count_list as $key => $user) {
        $formatted_id = sprintf('%08d', $user['id']);
        $user_id = substr_replace($formatted_id, ' ', 4, 0);
        if (empty($user['birthday'])) {
            $birthday = '';
        } else {
            $date = new DateTime($user['birthday']);
            $birthday = $date->format('Y年n月j日');
        }

        // 年度が設定できるようになればここも動的に変えること
        $month = date('n');
        $year = date('Y');
        $fiscal_year = ($month >= 4) ? $year : $year - 1;
        $payment_method = '';
        $is_tekijuku = '未入会';
        if (!empty($user['tekijuku']) && ($user['tekijuku']['paid_status'] == PAID_STATUS['COMPLETED'] ||
            $user['tekijuku']['paid_status'] == PAID_STATUS['SUBSCRIPTION_PROCESSING'] || $user['tekijuku']['is_deposit_' . $fiscal_year]) == 1) {
            $is_tekijuku = '入会済';
            $payment_method = PAYMENT_SELECT_LIST[$user['tekijuku']['payment_method']];
        }

        $user_list[$key] = [
            'id' => $user['id'],
            'user_id' => $user_id,
            'name' => $user['name'],
            'kana' => $user['name_kana'],
            'birthday' => $birthday,
            'city' => $user['city'],
            'email' => $user['email'],
            'phone' => $user['phone1'],
            'gurdian_name' =>  $user['guardian_name'],
            'gurdian_email' =>  $user['guardian_email'],
            'gurdian_phone' =>  $user['guardian_phone'],
            'is_tekijuku' => $is_tekijuku,
            'pay_method' => $payment_method,
            'is_apply' => $user['is_apply']
        ];
    }

    // CSVヘッダー
    $csv_list[0] = [
        '会員番号',
        '氏名',
        'フリガナ',
        '生年月日',
        '住所',
        'メールアドレス',
        '電話番号',
        '保護者氏名',
        '保護者メールアドレス',
        '保護者電話番号',
        '適塾記念会入会状況',
        '支払方法',
        'アカウント承認設定'
    ];

    // データの書き込み
    $count = 1;
    foreach ($user_list as $user) {
        $is_apply = IS_APPLY_LIST[$user['is_apply']]; // アカウント承認設定

        // 電話番号などの先頭の0が消えないように
        if (!empty($user['phone'])) {
            $phone = "'" . $user['phone'];
        }
        if (!empty($user['gurdian_phone'])) {
            $gurdian_phone = "'" . $user['gurdian_phone'];
        }

        $csv_array = [
            $user['user_id'],
            $user['name'],
            $user['kana'],
            $user['birthday'],
            $user['city'],
            $user['email'],
            '="' . $user['phone'] . '"',
            $user['gurdian_name'],
            $user['gurdian_email'],
            '="' . $user['gurdian_phone'] . '"',
            $user['is_tekijuku'],
            $user['pay_method'],
            $is_apply
        ];
        $csv_list[$count] = $csv_array;
        $count++;
    }

    // 保存先のファイルパス
    $temp_dir = make_temp_directory('user_export');
    $save_path = $temp_dir . "/user_output.csv";

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
    header('Content-Disposition: attachment; filename="ユーザー情報一覧_' . date('YmdHi') . '.csv"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($save_path));

    readfile($save_path);
    unlink($save_path); // ファイルを削除
} catch (Exception $e) {
    try {
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = 'CSVファイルの出力に失敗しました';
        redirect('/custom/admin/app/Views/management/user_registration.php');
        exit;
    }
}
