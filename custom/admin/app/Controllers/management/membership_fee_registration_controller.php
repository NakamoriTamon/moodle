<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/TekijukuCommemorationModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EmailSendSettingModel.php');

class MembershipFeeRegistrationController
{
    private $TekijukuCommemorationModel;
    private $EmailSendSettingModel;

    public function __construct()
    {
        $this->TekijukuCommemorationModel = new TekijukuCommemorationModel();
        $this->EmailSendSettingModel = new EmailSendSettingModel();
    }

    public function index()
    {
        $old_input = isset($_SESSION['old_input']) ? $_SESSION['old_input'] : [];
        $_SESSION['old_input'] = $_POST;
        $year = $_POST['year'] ?? null;
        if (is_null($year) && isset($old_input['select_year'])) {
            $year = $old_input['select_year'];
            $_SESSION['old_input']['year'] = $year;
        }
        $keyword = $_POST['keyword'] ?? null;
        if (is_null($keyword) && isset($old_input['select_keyword'])) {
            $keyword = $old_input['select_keyword'];
            $_SESSION['old_input']['keyword'] = $keyword;
        }
        $payment_status = $_POST['payment_status'] ?? null;
        if (is_null($keyword) && isset($old_input['select_payment_status'])) {
            $payment_status = $old_input['select_payment_status'];
            $_SESSION['old_input']['payment_status'] = $payment_status;
        }

        $page = $_POST['page'];
        if (!empty($page) && $page < 0) {
            $page = 1;
        }
        $email_send_setting = [];

        // ページネーション
        $per_page = 15;
        $current_page = $page;

        if (empty($current_page) && !empty($page)) {
            $current_page  = $page;
        }
        if (empty($current_page) && empty($page)) {
            $current_page  = 1;
        }

        if (empty($year)) {
            $data = [
                'tekijuku_commemoration_list' => [],
                'total_count' => 0,
                'per_page' => $per_page,
                'current_page' => $current_page,
                'page' => $current_page,
                'email_send_setting' => $email_send_setting
            ];

            return $data;
        }

        $filters = [];
        // 年度末までにアカウントが作成されたか確認
        $filters['year'] = $year;
        if (!empty($keyword)) {
            $filters['keyword'] = htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8');
        }
        if (!empty($payment_status)) {
            $filters['payment_status'] = $payment_status;
        }

        $tekijuku_commemoration_list = $this->TekijukuCommemorationModel->getTekijukuUser($filters, $current_page, $per_page);
        $tekijuku_commemoration_count = $this->TekijukuCommemorationModel->getTekijukuUser($filters, 1, 1000000);
        $total_count = 0;
        if (!empty($tekijuku_commemoration_count)) {
            $total_count = count($tekijuku_commemoration_count);
        }

        $filters = [];
        if (!empty($keyword)) {
            $filters['keyword'] = $keyword;
        }
        $filters['year'] = $year;
        $email_send_setting = $this->EmailSendSettingModel->getEmailSendSetting($filters); // メール送信器のは後ほど変える

        $data = [
            'tekijuku_commemoration_list' => $tekijuku_commemoration_list,
            'total_count' => $total_count,
            'per_page' => $per_page,
            'current_page' => $current_page,
            'page' => $current_page,
            'email_send_setting' => $email_send_setting
        ];
        return $data;
    }
}
