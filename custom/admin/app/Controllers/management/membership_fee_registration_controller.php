<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/TekijukuCommemorationModel.php');

class MembershipFeeRegistrationController
{
    private $TekijukuCommemorationModel;

    public function __construct()
    {
        $this->TekijukuCommemorationModel = new TekijukuCommemorationModel();
    }

    public function index()
    {
        $year = $_POST['year'] ?? null;
        $keyword = $_POST['keyword'] ?? null;
        $page = $_POST['page'] ?? 1;
        $_SESSION['old_input'] = $_POST;
        $email_send_setting_id = "";

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
                'email_send_setting_id' => $email_send_setting_id
            ];

            return $data;
        }

        $filters = [];
        if (!empty($keyword)) {
            $filters['keyword'] = $keyword;
        }
        // 年度末までにアカウントが作成されたか確認
        $filters['deadline_date'] = $year + 1 . '-04-01 00:00:00';

        $tekijuku_commemoration_list = $this->TekijukuCommemorationModel->getTekijukuUser($filters, $current_page);
        $total_count = $this->TekijukuCommemorationModel->getTekijukuUserCount($filters, $current_page);

        // 決済状況を組み込む
        foreach ($tekijuku_commemoration_list as $key => $tekijuku_commemoration) {
            $target = 'is_deposit_' . $year;
            if (!empty($tekijuku_commemoration[$target]) && $tekijuku_commemoration[$target] == 1) {
                $tekijuku_commemoration_list[$key]['display_depo'] = '決済済';
                $tekijuku_commemoration_list[$key]['paid_date'] = $year . '-04-01 00:00:00';
            }
            if ($tekijuku_commemoration[$target] != 1 && !empty($tekijuku_commemoration['paid_date'])) {
                $start_date = new DateTime($year . '-04-01 00:00:00');
                $end_date = new DateTime($year + 1 . '-04-01 00:00:00');
                $paid_date = new DateTime($tekijuku_commemoration['paid_date']);
                if ($start_date <= $paid_date && $paid_date < $end_date) {
                    $tekijuku_commemoration_list[$key]['display_depo'] = '決済済';
                } else {
                    $tekijuku_commemoration_list[$key]['display_depo'] = '未決済';
                }
            }
            if (empty($tekijuku_commemoration_list[$key]['display_depo'])) {
                $tekijuku_commemoration_list[$key]['display_depo'] = '未決済';
            }
        }

        $data = [
            'tekijuku_commemoration_list' => $tekijuku_commemoration_list,
            'total_count' => $total_count['total'],
            'per_page' => $per_page,
            'current_page' => $current_page,
            'page' => $current_page,
            'email_send_setting_id' => $email_send_setting_id
        ];
        return $data;
    }
}
