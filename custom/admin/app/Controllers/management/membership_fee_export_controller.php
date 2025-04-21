<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/TekijukuCommemorationModel.php');

try {
    $TekijukuCommemorationModel = new TekijukuCommemorationModel();

    // POSTパラメータの取得
    $year = $_POST['year'] ?? null;
    $keyword = $_POST['keyword'] ?? null;
    $_SESSION['old_input'] = $_POST;

    // フィルター条件の設定
    $filters = [];
    if (!empty($keyword)) {
        $filters['keyword'] = $keyword;
    }
    if (!empty($payment_status)) {
        $filters['payment_status'] = $payment_status;
    }

    if (!empty($year)) {
        // 年度末までにアカウントが作成されたか確認
        $filters['year'] = $year;
    } else {
        $_SESSION['message_error'] = '年度を選択してください';
        redirect('/custom/admin/app/Views/management/membership_fee_registration.php');
        exit;
    }

    // データの取得
    $tekijuku_commemoration_list = $TekijukuCommemorationModel->getTekijukuUser($filters, 1, 10000);
    $total_count = $TekijukuCommemorationModel->getTekijukuUserCount($filters);

    // CSVヘッダー
    $csv_list[0] = [
        '会員番号',
        'ユーザー名',
        'メールアドレス',
        '郵便番号',
        '住所',
        'メニュー',
        '口数',
        '所属部局',
        '部課・専攻名',
        '職名・学年',
        '決済状況',
        '決済方法',
        '支払日',
        '申込日',
        '旧会員番号'
    ];

    // データの書き込み
    $count = 1;
    foreach ($tekijuku_commemoration_list as $result) {
        $number = str_pad($result['number'], 8, '0', STR_PAD_LEFT);
        $menu = $result['type_code'] === 1 ? '普通会員' : '賛助会員';
        $created_date = new DateTime($result['created_at']);
        $paid_date = '';
        if (!empty($result['paid_date_history'])) {
            $paid_date = new DateTime($result['paid_date_history']);
            $paid_date = $paid_date->format("Y年n月j日");
        }

        $name = $result['name'];
        if($result['is_delete']) {
            $name .= '(退会済)';
        }
        $csv_array = [
            substr_replace($number, ' ', 4, 0),
            $name,
            $result['email'],
            $result['post_code'],
            $result['address'],
            $menu,
            $result['unit'],
            $result['department'],
            $result['major'],
            $result['official'],
            $result['payment_status'],
            $payment_select_list[$result['payment_method']] ?? '',
            $paid_date,
            $created_date->format("Y年n月j日"),
            $result['old_number'] ?? ''
        ];
        $csv_list[$count] = $csv_array;
        $count++;
    }

    // 保存先のファイルパス
    $temp_dir = make_temp_directory('membership_fee_export');
    $save_path = $temp_dir . "/membership_fee_output.csv";

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
    header('Content-Disposition: attachment; filename="適塾記念会会費_' . $year . '年度_' . date('YmdHi') . '.csv"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($save_path));

    readfile($save_path);
    unlink($save_path); // ファイルを削除
} catch (Exception $e) {
    try {
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = 'CSVファイルの出力に失敗しました';
        redirect('/custom/admin/app/Views/management/membership_fee_registration.php');
        exit;
    }
} 