<?php
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/InformationModel.php');

class InformationController
{
    private $informationModel;

    public function __construct()
    {
        $this->informationModel = new InformationModel();
    }

    public function index()
    {
        $filters = [
            'limit' => 10, // 1ページあたりの表示件数
        ];
        $per_page = 10; // 1ページあたりの表示件数
        $current_page = $_GET['page'] ?? 1; // 現在のページ番号（デフォルト: 1）
        $information_list = $this->informationModel->getAllInformation($filters, $current_page, $per_page);
        $total_count =$this->informationModel->getInformationCount($filters);
        $data = [
            'information_list' => $information_list,
            'currentPage' => $current_page,
            'totalCount' => $total_count,
            'perPage' => $per_page,
            'queryString' => '',
        ];

        return  $data;
    }
    public function detail(int $id)
    {
        $information = $this->informationModel->find($id);
        /* ▲ 懸念点として該当要素に既にスタイルが当たっていた際に打ち消されてしまう可能性がある。 当てる時はインライン + important OR 専用クラスの作成
    　　    ただ完全にフリーで当てさせると大幅なレイアウト崩れにつながる危険性があるので、導線は残す必要あり 
    　　 */

        // body内をサニタイズ( 登録時も確認する事 )
        // $config = HTMLPurifier_Config::createDefault();
        // $config->set('CSS.AllowTricky', true);
        // $config->set('HTML.TargetBlank', true);
        // $config->set('HTML.SafeInlineCSS', true);
        // $config->set('HTML.Allowed', implode(',', [
        //     'p[style]',
        //     'b',
        //     'strong',
        //     'i',
        //     'em',
        //     'ul[style]',
        //     'ol[style]',
        //     'li[style]',
        //     'a[href|target|rel|style]',
        //     'iframe[src|width|height|frameborder|allowfullscreen]',
        //     'br',
        //     'span[style]',
        //     'div[style]',
        //     'h1[style]',
        //     'h2[style]',
        //     'h3[style]',
        //     'h4[style]',
        //     'h5[style]',
        //     'h6[style]',
        //     'img[src|alt|width|height]',
        // ]));

        // $config->set('CSS.AllowedProperties', [
        //     'color',
        //     'background-color',
        //     'font-size',
        //     'text-align',
        //     'line-height',
        //     'margin',
        //     'margin-top',
        //     'margin-bottom',
        //     'margin-left',
        //     'margin-right',
        //     'padding',
        //     'padding-top',
        //     'padding-bottom',
        //     'padding-left',
        //     'padding-right',
        //     'display',
        //     'border',
        //     'border-radius'
        // ]);

        // $purifier = new HTMLPurifier($config);
        // $clean_html = $purifier->purify($information['body']); // サニタイズ

        $information['body'] = htmlspecialchars_decode($information['body']);

        // 結果を返す
        return $information;
    }
}
