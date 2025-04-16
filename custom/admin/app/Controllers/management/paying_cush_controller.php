<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/UserModel.php');
require_once('/var/www/html/moodle/custom/app/Models/TekijukuCommemorationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/PaymentTypeModel.php');

class PayingCushController
{

    private $userModel;
    private $tekijukuCommemorationModel;
    private $paymentTypeModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->tekijukuCommemorationModel = new TekijukuCommemorationModel();
        $this->paymentTypeModel = new PaymentTypeModel();
    }

    public function index()
    {
        // 検索項目取得
        $keyword = $_POST['keyword'] ?? null;
        $fk_user_id = $_POST['fk_user_id'] ?? $_SESSION['old_input']['fk_user_id'] ?? null;
        

        $filters = [];
        if (!empty($keyword)) {
            $filters['keyword'] = $keyword;
            $filters['fk_user_id'] = $fk_user_id;
        }

        $user_list = $this->userModel->getShortNameUserList();

        $tekijuku = [];
        if(!empty($fk_user_id)) {
            $tekijuku = $this->tekijukuCommemorationModel->getTekijukuUserByPaid($fk_user_id);
            if($tekijuku === false) {
                $user = $this->userModel->getUserById($fk_user_id);
                $empty_result = [
                    'id' => '',
                    'number' => '',
                    'type_code' => '',
                    'name' => $user['name'],
                    'kana' => $user['name_kana'],
                    'post_code' => '',
                    'address' => '',
                    'tell_number' => $user['phone1'],
                    'email' => $user['email'],
                    'payment_method' => '',
                    'paid_date' => '',
                    'note' => '',
                    'is_published' => '',
                    'is_subscription' => '',
                    'is_delete' => '',
                    'department' => '',
                    'major' => '',
                    'official' => '',
                    'paid_status' => '',
                    'is_university_member' => '',
                    'price' => '',
                    'is_dummy_email' => '',
                    'is_deposit_2025' => '',
                    'is_deposit_2026' => '',
                    'is_deposit_2027' => '',
                    'is_deposit_2028' => '',
                    'is_deposit_2029' => '',
                    'is_deposit_2030' => '',
                ];
                    
            }

            $tekijuku = $tekijuku !== false ? $tekijuku : $empty_result;
        }

        $payment_type_list = $this->paymentTypeModel->getPaymentTypeAll();

        $data = [
            'user_list' => $user_list,
            'tekijuku' => $tekijuku,
            'keyword' => $keyword,
            'fk_user_id' => $fk_user_id,
            'payment_type_list' => $payment_type_list
        ];

        return $data;
    }
}