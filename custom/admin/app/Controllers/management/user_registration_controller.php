<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/UserModel.php');

class UserRegistrationController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
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

        // ページネーション
        $per_page = 15;
        // 検索ボタンが押された場合のみ1ページ目を表示
        $is_search = isset($_POST['search']) && $_POST['search'] == 1;
        $current_page = $is_search ? 1 : ($_POST['page'] ?? $_GET['page'] ?? 1);
        $current_page = max(1, (int)$current_page);

        if ($current_page < 0) {
            $current_page = 1;
        }

        // ユーザーデータ取得 (UserModel 側で $filters, $current_page, $per_page を考慮)
        $user_list = $this->userModel->getUsers($filters, $current_page, $per_page);
        $user_count_list = $this->userModel->getFilterUserCount($filters);

        $data_list = [];
        foreach ($user_list as $key => $user) {
            $formatted_id = sprintf('%08d', $user['id']);
            $user_id = substr_replace($formatted_id, ' ', 4, 0);
            if (empty($user['birthday'])) {
                $birthday = '';
            } else {
                $date = new DateTime($user['birthday']);
                $birthday = $date->format('Y年n月j日');
            }

            // 年度計算（例）
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

            $data_list[$key] = [
                'id' => $user['id'],
                'user_id' => $user_id,
                'name' => $user['name'],
                'kana' => $user['name_kana'],
                'birthday' => $birthday,
                'city' => $user['city'],
                'email' => $user['email'],
                'phone' => $user['phone1'],
                'gurdian_name' => $user['guardian_name'],
                'gurdian_email' => $user['guardian_email'],
                'gurdian_phone' => $user['guardian_phone'],
                'is_tekijuku' => $is_tekijuku,
                'pay_method' => $payment_method,
                'is_apply' => $user['is_apply']
            ];
        }

        $total_count = count($user_count_list);
        $data = [
            'data_list'      => $data_list,
            'total_count'    => $total_count,
            'per_page'       => $per_page,
            'current_page'   => $current_page,
            'page'           => $current_page,
        ];

        return $data;
    }
}
