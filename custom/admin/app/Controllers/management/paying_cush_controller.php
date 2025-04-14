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
        $user_id = $_POST['user_id'] ?? null;
        $_SESSION['old_input'] = $_POST;

        $filters = [];
        if (!empty($keyword)) {
            $filters['keyword'] = $keyword;
            $filters['user_id'] = $user_id;
        }

        $user_list = $this->userModel->getUsers($filters, 1, 1000000);

        $tekijuku = [];
        if(!empty($user_id)) {
            $tekijuku = $this->tekijukuCommemorationModel->getTekijukuUserByPaid($user_id);
        }

        $payment_type_list = $this->paymentTypeModel->getPaymentTypeAll();

        $data = [
            'user_list' => $user_list,
            'tekijuku' => $tekijuku,
            'keyword' => $keyword,
            'user_id' => $user_id,
            'payment_type_list' => $payment_type_list
        ];

        return $data;
    }
}