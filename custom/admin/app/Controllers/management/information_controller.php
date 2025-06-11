<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/UserModel.php');
require_once('/var/www/html/moodle/custom/app/Models/InformationModel.php');

class InformationController
{
    private $userModel;
    private $informationModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->informationModel = new InformationModel();
    }

    public function index()
    {
        // セッションからキーワードを取得
        $keyword = $_POST['keyword'];
        $per_page = 15;
        $current_page = $_POST['page'] ?? 1; // 現在のページ番号（デフォルト: 1）
        $page = $current_page;

        $_SESSION['old_input'] = $_POST;
        $filters = [];
        if (!empty($keyword)) {
            $filters['keyword'] = $keyword;
        }
        $data_list = $this->informationModel->getAllInformation($filters, $current_page, $per_page);
        $total_count =$this->informationModel->getInformationCount($filters);
        // 日付のフォーマットを整える
        foreach ($data_list as &$item) {
            if (!empty($item['publish_start_at'])) {
                $item['start_date'] = date('Y年n月j日 G時i分', strtotime($item['publish_start_at']));
            }
            if (!empty($item['publish_end_at'])) {
                $item['end_date'] = date('Y年n月j日 G時i分', strtotime($item['publish_end_at']));
            }
        }

        // data_listのbodyのHTMLタグを除去
        foreach ($data_list as &$item) {
            if (isset($item['body'])) {
                // HTMLタグを除去
                $item['body'] = strip_tags($item['body']);
            }
        }
        
        $data = [
            'data_list'      => $data_list,
            'total_count'    => $total_count,
            'per_page'       => $per_page,
            'current_page'   => $current_page,
            'page'           => $page,
            'keyword'        => $keyword,
        ];

        return $data;
    }

    /**
     * お知らせの新規登録・編集画面を表示
     *
     * @param int|null $id お知らせのID
     * @return array|void
     */
    public function edit($id = null)
    {
        $data_item = $this->informationModel->find($id);
        if (!$data_item && $id) {
            // お知らせが見つからない場合はエラーメッセージを設定
            $_SESSION['message_error'] = 'お知らせが見つかりません';
            redirect('/custom/admin/app/Views/management/information.php');
            return;
        }
        foreach ($data_item as $key => $value) {
            // publish_start_atとpublish_end_atの日付と日時に分ける
            if (in_array($key, ['publish_start_at', 'publish_end_at']) && !empty($value)) {
                $date = new DateTime($value);
                $prefix = ($key === 'publish_start_at') ? 'publish_start' : 'publish_end';
                $data_item[$prefix . '_date'] = $date->format('Y-m-d');
                $data_item[$prefix . '_hour'] = $date->format('G');
            }
        }
        $data = [
            'data_item' => $data_item,
            'is_edit' => ($data_item && $id) ? true : false,
        ];
        return $data;
    }
}
