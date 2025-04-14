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

        $tekijuku_list = $this->tekijukuCommemorationModel->getTekijukuUserAll();

        $tekijuku = [];
        if(!empty($fk_user_id)) {
            $tekijuku = $this->tekijukuCommemorationModel->getTekijukuUserByPaid($fk_user_id);
        }

        $payment_type_list = $this->paymentTypeModel->getPaymentTypeAll();

        $data = [
            'tekijuku_list' => $tekijuku_list,
            'tekijuku' => $tekijuku,
            'keyword' => $keyword,
            'fk_user_id' => $fk_user_id,
            'payment_type_list' => $payment_type_list
        ];

        return $data;
    }
}