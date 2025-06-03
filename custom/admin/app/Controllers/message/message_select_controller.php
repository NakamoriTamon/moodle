<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/UserModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/TekijukuCommemorationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationCourseInfoModel.php');
require_once('/var/www/html/moodle/custom/app/Models/RoleAssignmentsModel.php');

class MessageSelectController
{
    private $userModel;
    private $eventModel;
    private $categoryModel;
    private $TekijukuCommemorationModel;
    private $eventApplicationCourseInfo;
    private $roleAssignmentsModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->eventModel = new EventModel();
        $this->categoryModel = new CategoryModel();
        $this->TekijukuCommemorationModel = new TekijukuCommemorationModel();
        $this->eventApplicationCourseInfo = new EventApplicationCourseInfoModel();
        $this->roleAssignmentsModel = new RoleAssignmentsModel();
    }

    public function index()
    {

        global $USER;
        global $DB;

        $data = [];

        $role = $this->roleAssignmentsModel->getShortname($USER->id);
        $shortname = $role['shortname'];

        // システム管理者以外は自身のイベントのみ表示する
        $kbn_id_list = KBN_ID_LIST;
        if ($shortname !== ROLE_ADMIN && $USER->id != MEMBERSHIP_ACCESS_ACOUNT) {
            unset($kbn_id_list[2], $kbn_id_list[3]);
        }

        $kbn_id = $_POST['kbn_id'] ?? 0;
        $page = $_POST['page'] ?? 1;

        // 対象区分によって検索処理を変更
        switch ($kbn_id) {
            case DM_SEND_KBN_EVENT:
                $data = $this->getEvent($USER, $DB, $page);
                break;
            case DM_SEND_KBN_TEKIJUKU:
                $data = $this->getTekijuku($USER, $DB, $page);
                // 顧客作成の処理
                break;
            case DM_SEND_KBN_ALL:
                // 顧客情報更新の処理
                $data = $this->getUser($USER, $DB, $page);
                break;

            default:
        }

        $dataList = ['kbn_id_list' => $kbn_id_list, 'data' => $data];

        return $dataList;
    }


    // イベント情報に参加しているユーザー情報を取得
    private function getEvent($USER, $DB, $get_page)
    {

        // 検索項目取得
        $keyword = $_POST['keyword'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        $event_status_id = $_POST['event_status_id'] ?? null;
        $event_id = $_POST['event_id'] ?? null;
        $course_no = $_POST['course_no'] ?? null;
        $keyword = $_POST['keyword'] ?? null;
        $_SESSION['old_input'] = $_POST;
        $header_list = [];
        $course_list = [];

        // ページネーション
        $per_page = 15;
        $current_page = $get_page;

        if (empty($current_page)) {
            $current_page  = 1;
        }

        // イベント選択時かつ他の選択肢が選択された際に対象イベントが含まれていなければ消す
        $first_filters = array_filter([
            'category_id' => $category_id,
            'event_status' => $event_status_id,
        ]);
        $first_filters = array_filter($first_filters);
        $found = false;
        if (!empty($first_filters) && !empty($event_id)) {
            $first_event_list = $this->eventModel->getEvents($first_filters, 1, 100000);
            foreach ($first_event_list as $first_event) {
                if ($event_id == $first_event['id']) {
                    $found = true;
                }
            }
            if (!$found) {
                $event_id = $found ? $event_id : null;
            }
        }

        $role = $this->roleAssignmentsModel->getShortname($USER->id);
        $shortname = $role['shortname'];

        $filters = array_filter([
            'category_id' => $category_id,
            'event_status' => $event_status_id,
            'event_id' => $event_id,
            'course_no' => $course_no,
            'userid' => $USER->id,
            'shortname' => $shortname
        ]);

        // null の要素を削除しイベント検索
        $filters = array_filter($filters);
        $event_list = $this->eventModel->getEvents($filters, 1, 100000);
        $select_event_list = $this->eventModel->getEvents([
            'userid' => $USER->id,
            'shortname' => $shortname
        ], 1, 100000); // イベント名選択用

        $is_display = false;
        $is_single = false;
        $course_info_id = null;

        // イベント情報を特定する
        foreach ($event_list as $event) {
            if (!empty($event_id)) {
                if ($event['event_kbn'] == SINGLE_EVENT) {
                    foreach ($event['course_infos'] as $course_info) {
                        $course_info_id = $course_info['id'];
                        $course_list = [];
                        $course_no = 1;
                        $is_display = true;
                        $is_single = true;
                    }
                } elseif ($event['event_kbn'] == PLURAL_EVENT) {
                    if (!empty($course_no)) {
                        foreach ($event['course_infos'] as $course_info) {
                            if ($course_info['no'] == $course_no) {
                                $course_info_id = $course_info['id'];
                                $is_display = true;
                            }
                        }
                    }
                    $course_list = $event['course_infos'];
                } elseif ($event['event_kbn'] == EVERY_DAY_EVENT) {
                    foreach ($event['course_infos'] as $course_info) {
                        $course_info_id = $course_info['id'];
                        $course_list = [];
                        $course_no = 1;
                        $is_single = true;
                        $is_display = true;
                    }
                }
            }
        }

        // IDの0を落とす
        if (is_numeric($keyword)) {
            $keyword = ltrim($keyword, '0');
        }
        $application_course_info_list = [];
        // 講義回数まで絞り込んだ場合
        if (!empty($course_info_id)) {
            $application_course_info_list = $this->eventApplicationCourseInfo->getByCourseInfoId($course_info_id, $keyword, $current_page, $per_page);
            $application_course_info_list_count = $this->eventApplicationCourseInfo->getByCourseInfoId($course_info_id, $keyword, 1, 100000);
        }
        // イベント単位まで絞り込んだ場合
        if (empty($course_info_id) && !empty($event_id)) {
            $application_course_info_list = $this->eventApplicationCourseInfo->getByEventEventId($event_id, $keyword, $current_page, $per_page);
            $application_course_info_list_count = $this->eventApplicationCourseInfo->getByEventEventId($event_id, $keyword, 1, 1000000);
        }

        $total_count = 0;
        $mail_to_list = [];
        if (!empty($application_course_info_list_count)) {
            foreach ($application_course_info_list_count as $value) {
                // キーワード検索ではお連れ様は検索から省く
                if ($value['ticket_type'] != TICKET_TYPE['SELF'] && !empty($keyword)) {
                    continue;
                }
                $total_count = $total_count + 1;
                $mail_to_list[] = $value['participant_mail'];
            }
        }

        // 講座回数でソートする
        usort($application_course_info_list, function ($a, $b) {
            return $a['course_info']['no'] <=> $b['course_info']['no'];
        });

        // 表示データを取得・整形する
        $application_list = [];
        foreach ($application_course_info_list as $key => $application_course_info) {
            if ($application_course_info['participation_kbn'] == IS_PARTICIPATION_CANCEL) {
                unset($application_course_info_list[$key]);
                continue;
            }
            $application = reset($application_course_info['application']);
            $event = $application['event'];
            $application_date = new DateTime($application['application_date']);
            $application_date = $application_date->format("Y年n月j日");

            $name = '';
            $user_id = '';
            $is_paid = '';
            $payment_type = '';
            $payment_date = '';

            // お連れ様の場合はユーザー情報は取得しない
            if ($application_course_info['ticket_type'] == TICKET_TYPE['SELF']) {
                $name = $application['user']['name'];
                $formatted_id =  str_pad($application["user"]['id'], 8, "0", STR_PAD_LEFT);
                $user_id  = substr_replace($formatted_id, ' ', 4, 0);
                if ($application['pay_method'] != FREE_EVENT) {
                    $payment_type = PAYMENT_SELECT_LIST[$application['pay_method']];
                    $is_paid = !empty($application['payment_date']) ? '決済済' : '未決済';
                    if (!empty($application['payment_date'])) {
                        $payment_date = new DateTime($application['payment_date']);
                        $payment_date = $payment_date->format("Y年n月j日");
                    }
                }
            } elseif (!empty($keyword)) {
                // キーワード未検索時はお連れ様の情報も取得する
                continue;
            }

            $application_list[$key] = [
                'id' => $application_course_info['id'],
                'event_name' => $event['name'],
                'no' => '第' . $application_course_info['course_info']['no'] . '講座',
                'user_id' => $user_id,
                'name' => $name,
                'email' => $application_course_info['participant_mail'],
                'payment_type' => $payment_type,
                'is_paid' => $is_paid,
                'payment_date' => $payment_date,
                'application_date' => $application_date,
                'participation_kbn' => $application_course_info['participation_kbn'],
            ];

            if ($event['event_kbn'] == PLURAL_EVENT) {
                $header_list = [
                    "ID",
                    "イベント名",
                    "講座回数",
                    "会員番号",
                    "ユーザー名",
                    "メールアドレス",
                    "決済方法",
                    "決済状況",
                    "決済日",
                    "申込日"
                ];
            } else {
                $header_list = [
                    "ID",
                    "イベント名",
                    "会員番号",
                    "ユーザー名",
                    "メールアドレス",
                    "決済方法",
                    "決済状況",
                    "決済日",
                    "申込日"
                ];
            }
        }

        $event_list = !empty($event_id) && empty($event_status_id) && empty($category_id) ?  $select_event_list : $event_list;
        $category_list = $this->categoryModel->getCategories();

        $data = [
            'category_list' => $category_list,
            'event_list' => $event_list,
            'is_display' => $is_display,
            'is_single' => $is_single,
            'course_info_id' => $course_info_id,
            'course_no' => $course_no,
            'application_list' => $application_list,
            'total_count' => $total_count,
            'per_page' => $per_page,
            'current_page' => $current_page,
            'page' => $current_page,
            'header_list' => $header_list,
            'course_list' => $course_list,
            'mail_to_list' => $mail_to_list,
            'event_kbn' => $event['event_kbn'] ?? null,
        ];

        return $data;
    }

    // 適塾単位で取得
    private function getTekijuku($USER, $DB, $get_page)
    {
        // 検索項目取得
        $keyword = $_POST['keyword'] ?? null;
        $page = $_POST['page'] ?? null;
        $_SESSION['old_input'] = $_POST;

        $now = new DateTime();
        $year = (int)$now->format('Y');
        $month = (int)$now->format('n');

        // 4月より前なら前年を年度として扱う
        $year = $month < 4 ? $year - 1 : $year;

        // ページネーション
        $per_page = 15;
        $current_page = $get_page;

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
            ];

            return $data;
        }

        $filters = [];
        $filters['year'] = $year;
        $filters['payment_status'] = '決済済';
        if (!empty($keyword)) {
            $filters['keyword'] = $keyword;
        }

        $tekijuku_commemoration_list = $this->TekijukuCommemorationModel->getTekijukuUser($filters, $current_page, $per_page);
        $tekijuku_commemoration_count = $this->TekijukuCommemorationModel->getTekijukuUser($filters, 1, 1000000);
        $total_count = 0;
        if (!empty($tekijuku_commemoration_count)) {
            $total_count = count($tekijuku_commemoration_count);
        }

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

        // メール送信先を取得する
        $mail_to_list = [];
        foreach ($tekijuku_commemoration_count as $tekijuku_commemoration) {
            $mail_to_list[] = $tekijuku_commemoration['email'];
        }

        $header_list = [
            "会員番号",
            "ユーザー名",
            "メールアドレス",
            "メニュー",
            "口数",
            "所属部局",
            "部課・専攻名",
            "職名",
            "決済状況",
            "決済方法",
            "支払日",
            "申込日",
            "旧会員番号"
        ];

        $data = [
            'tekijuku_commemoration_list' => $tekijuku_commemoration_list,
            'header_list' => $header_list,
            'total_count' => $total_count,
            'per_page' => $per_page,
            'current_page' => $current_page,
            'page' => $current_page,
            'mail_to_list' => $mail_to_list,
        ];
        return $data;
    }
    // ユーザー単位で取得
    private function getUser($USER, $DB, $get_page)
    {
        // 検索項目取得
        $page = $_POST['page'] ?? null;
        $keyword = $_POST['keyword'] ?? null;
        $_SESSION['old_input'] = $_POST;

        // ページネーション
        $per_page = 15;
        $current_page = $get_page;

        if ($current_page < 0) {
            $current_page = 1;
        }
        if (empty($current_page) && !empty($page)) {
            $current_page  = $page;
        }
        if (empty($current_page) && empty($page)) {
            $current_page  = 1;
        }

        $filters = [];
        if (!empty($keyword)) {
            $filters['keyword'] = $keyword;
        }

        $total_count = 0;
        $user_list = $this->userModel->getUsers($filters, $current_page, $per_page);
        $user_count_list = $this->userModel->getUsers($filters, 1, 1000000);
        if (!empty($user_count_list)) {
            $total_count = count($user_count_list);
        }

        // メール送信先を取得する
        $mail_to_list = [];
        foreach ($user_count_list as $user_count) {
            $mail_to_list[] = $user_count['email'];
        }

        $data_list = [];
        foreach ($user_list as $Key => $user) {
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
                $user['tekijuku']['is_deposit_' . $fiscal_year]) == 1) {
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
                'gurdian_name' =>  $user['guardian_name'],
                'gurdian_email' =>  $user['guardian_email'],
                'gurdian_phone' =>  $user['guardian_phone'],
                'is_tekijuku' => $is_tekijuku,
                'pay_method' => $payment_method,
                'is_apply' => $user['is_apply']
            ];

            $header_list = [
                "会員番号",
                "氏名",
                "フリガナ",
                "生年月日",
                "住所",
                "メールアドレス",
                "電話番号",
                "保護者氏名",
                "保護者メールアドレス",
                "保護者電話番号",
                "適塾記念会入会状況",
                "支払方法"
            ];
        }

        $data = [
            'user_list' => $data_list,
            'total_count' => $total_count,
            'per_page' => $per_page,
            'current_page' => $current_page,
            'page' => $current_page,
            'header_list' => $header_list,
            'mail_to_list' => $mail_to_list,
        ];

        return $data;
    }
}
