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

        // 検索項目取得
        $page = $_POST['page'] ?? null;
        $_SESSION['old_input'] = $_POST;

        // ページネーション
        $per_page = 15;
        $current_page = $_GET['page'];
        $page = 1;

        if (empty($current_page) && !empty($page)) {
            $current_page  = $page;
        }
        if (empty($current_page) && empty($page)) {
            $current_page  = 1;
        }

        $user_list = $this->userModel->getUser($current_page);

        $data_list = [];
        foreach ($user_list as $Key => $user) {
            $formatted_id = sprintf('%08d', $user['id']);
            $user_id = substr_replace($formatted_id, ' ', 4, 0);
            $date = new DateTime($user['birthday']);
            $birthday = $date->format('Y年n月j日');

            $payment_method = '';
            $is_tekijuku = '未入会';
            if (!empty($user['tekijuku'])) {
                $is_tekijuku = '入会済';
                $payment_method = PAYMENT_SELECT_LIST[$user['tekijuku']['payment_method']];
            }

            $data_list[$Key] = [
                'id' => $user['id'],
                'user_id' => $user_id,
                'name' => $user['name'],
                'kana' => $user['name_kana'],
                'birthday' => $birthday,
                'city' => $user['city'],
                'email' => $user['email'],
                'phone' => $user['phone1'],
                'gurdian_name' =>  $user['gurdian_name'],
                'gurdian_email' =>  $user['gurdian_email'],
                'gurdian_phone' =>  $user['gurdian_phone'],
                'is_tekijuku' => $is_tekijuku,
                'pay_method' => $payment_method,
                'is_apply' => $user['is_apply']
            ];
        }

        $total_count = count($data_list);
        $data = [
            'data_list' => $data_list,
            'total_count' => $total_count,
            'per_page' => $per_page,
            'current_page' => $current_page,
            'page' => $current_page,
        ];

        return $data;
    }
}
