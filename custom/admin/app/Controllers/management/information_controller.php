<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/UserModel.php');

class InformationController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {

        // ページネーションダミー
        $per_page = 1;
        $current_page = 1;
        $page = 1;

        // ダミーデータ
        $sample_list =
            [
                0 => [
                    'id' => '1',
                    'title' => 'オープンキャンパス開催のお知らせ',
                    'body' => '2025年度オープンキャンパスを開催します。模擬講義やキャンパスツアーを体験...',
                    'start_date' => '2025年7月20日 12時00分',
                    'end_date' => '2025年7月21日 23時00分'
                ],
                1 => [
                    'id' => '2',
                    'title' => '学園祭のお知らせ',
                    'body' => '学生による模擬店やステージイベントなどをお楽しみいただける学園祭を...',
                    'start_date' => '2025年10月5日 00時00分',
                    'end_date' => '2025年10月6日 23時59分'
                ],
                2 => [
                    'id' => '3',
                    'title' => '就職ガイダンスのご案内',
                    'body' => '3年生向けに就職活動準備のガイダンスを実施します。企業担当者による説明会も予定し...',
                    'start_date' => '2025年6月12日 00時00分',
                    'end_date' => '2025年6月12日 23時59分'
                ],
                3 => [
                    'id' => '4',
                    'title' => '夏季休業期間のお知らせ',
                    'body' => '本学では下記の期間、夏季休業とさせていただきます。窓口業務は休止となり...',
                    'start_date' => '2025年8月10日 00時00分',
                    'end_date' => '2025年8月17日 23時59分'
                ],
                4 => [
                    'id' => '5',
                    'title' => '前期試験日程の公開',
                    'body' => '前期試験の実施日程をWebポータルにて公開しました。必ず確認...',
                    'start_date' => '2025年6月6日 00時00分',
                    'end_date' => ''
                ],
                5 => [
                    'id' => '6',
                    'title' => '卒業論文提出締切について',
                    'body' => '卒業予定の学生は、卒業論文の提出締切日を厳守してください。詳細は...',
                    'start_date' => '2025年11月1日 00時00分',
                    'end_date' => ''
                ],
                6 => [
                    'id' => '7',
                    'title' => '図書館臨時休館のお知らせ',
                    'body' => '館内設備点検のため、図書館を臨時休館いたします。利用者の皆様にはご迷惑を...',
                    'start_date' => '2025年9月15日 00時00分',
                    'end_date' => '2025年9月15日 23時59分'
                ],
            ];

        $total_count = 15;
        $data = [
            'sample_list'    => $sample_list,
            'total_count'    => $total_count,
            'per_page'       => $per_page,
            'current_page'   => $current_page,
            'page'           => $page,
        ];

        return $data;
    }

    public function edit($id = null)
    {

        $data = 'mock';

        return $data;
    }
}
