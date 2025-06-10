<?php
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');

class InformationController
{

    public function index()
    {

        // ダミーデータ
        $information_list = [
            [
                'start_date' => '2024/02/15',
                'title' => '春学期履修ガイダンスのお知らせ'
            ],
            [
                'start_date' => '2024/02/28',
                'title' => '卒業予定者向け事務手続きの案内'
            ],
            [
                'start_date' => '2024/03/05',
                'title' => '図書館蔵書点検に伴う一時休館のお知らせ'
            ],
            [
                'start_date' => '2024/03/10',
                'title' => '2024年度前期奨学金申請受付開始について'
            ],
            [
                'start_date' => '2024/03/24',
                'title' => '新入生オリエンテーション日程のご案内'
            ],
            [
                'start_date' => '2024/04/01',
                'title' => '2024年度入学式の開催について'
            ],
            [
                'start_date' => '2024/04/05',
                'title' => '授業開始に伴う教室変更のお知らせ'
            ],
            [
                'start_date' => '2024/04/12',
                'title' => '健康診断実施のお知らせ'
            ],
            [
                'start_date' => '2024/04/20',
                'title' => '避難訓練実施のお知らせ（全学部対象）'
            ],
            [
                'start_date' => '2024/05/01',
                'title' => 'ゴールデンウィーク期間中の窓口対応について'
            ],
        ];

        // 日付の降順（新しい順）に並び替え
        usort($information_list, function ($a, $b) {
            return strtotime($b['start_date']) - strtotime($a['start_date']);
        });

        $data = [
            'information_list' => $information_list,
            'currentPage' => 1,
            'totalCount' => 40,
            'perPage' => 10,
            'queryString' => '',

        ];

        return  $data;
    }
    public function detail(int $id)
    {

        // ダミーデータ
        $information = [
            'title' => '【研究紹介】CRISPR技術を活用した次世代農業の取り組み',
            'body' => <<<HTML
                <h2 style="border-bottom: 2px solid #81bfda; margin-bottom: 30px">本学における研究開発の取り組み</h2>
        
                <p>本学では、近年急速に発展しているゲノム編集技術 <strong>「CRISPR-Cas9（クリスパー・キャスナイン）」</strong> を用いて、農作物の品種改良および環境耐性強化に関する研究を進めています。</p><br>
        
                <p>とくに、病害虫耐性の向上、干ばつ耐性の強化、高栄養価作物の開発など、気候変動や人口増加といった社会課題に対応した農業技術の確立を目指しています。</p>
        
                <h3 style="margin-top: 2.5em; margin-bottom: 2rem; font-weight: bold">▼ 主な研究テーマ</h3>
        
                <ul style="margin-left: 0; padding-left: 0; list-style: none; line-height: 1.8;">
                    <li style="margin-bottom: 0.5em; padding-left: 2em; position: relative;">
                        <span style="position: absolute; left: 0; color: #81bfda;">■</span>
                        <strong>イネやトマトにおける病害抵抗性遺伝子の高精度編集</strong>
                    </li>
                    <li style="margin-bottom: 0.5em; padding-left: 2em; position: relative;">
                        <span style="position: absolute; left: 0; color: #81bfda;">■</span>
                        <strong>ゲノム安定性を保ちながらの多重遺伝子編集</strong>
                    </li>
                    <li style="margin-bottom: 0.5em; padding-left: 2em; position: relative;">
                        <span style="position: absolute; left: 0; color: #81bfda;">■</span>
                        <strong>非モデル植物へのCRISPR技術適用研究</strong>
                    </li>
                    <li style="margin-bottom: 0.5em; padding-left: 2em; position: relative;">
                        <span style="position: absolute; left: 0; color: #81bfda;">■</span>
                        <strong>ゲノム編集作物に関する倫理的・社会的受容に関する調査</strong>
                    </li>
                </ul>
        
                <h3 style="margin-top: 2.5em; margin-bottom: 2rem; font-weight: bold">▼ 関連研究機関・資料</h3>
        
                <ul style="line-height: 1.6;">
                    <li style="margin-bottom: 0.5em; padding-left: 2em; position: relative;">
                        <span style="position: absolute; left: 0; color: #81bfda;">■</span>
                        <strong><a style="display: inline-block;" href="https://egr.biken.osaka-u.ac.jp/" target="_blank" rel="noopener">遺伝子機能解析研究室の紹介ページ ↗</a></strong>
                    </li>
                    <li style="margin-bottom: 0.5em; padding-left: 2em; position: relative;">
                        <span style="position: absolute; left: 0; color: #81bfda;">■</span>
                        <strong><a style="display: inline-block;" href="https://www.eurofins.co.jp/clinical-testing/%E3%82%B5%E3%83%BC%E3%83%93%E3%82%B9/geneticlab/%E6%8A%80%E8%A1%93%E3%82%B3%E3%83%A9%E3%83%A0/crisprcas%E3%82%B7%E3%82%B9%E3%83%86%E3%83%A0%E3%81%A8%E5%8C%BB%E7%99%82%E3%81%B8%E3%81%AE%E5%BF%9C%E7%94%A8%E3%81%AB%E3%81%A4%E3%81%84%E3%81%A6-%E3%81%93%E3%82%8C%E3%81%A3%E3%81%A6%E4%BD%95-%E3%83%90%E3%82%A4%E3%82%AA%E3%82%B3%E3%83%A9%E3%83%A0-%E7%AC%AC37%E5%9B%9E/" target="_blank" rel="noopener">CRISPR-Cas9技術の概説と応用事例（外部資料） ↗</a></strong>
                    </li>
                </ul>
        
                <div style="margin-top: 1.5em; padding: 1em; background-color: #fff; border: 1px solid #ccc; border-radius: 5px;">
                    <p style="font-size: 0.95em; color: #333; ">
                        ※ 本研究は、農林水産省による「次世代農業技術開発プロジェクト」の一環として推進されています。関連する研究成果や実証実験の進展は、順次本学ウェブサイト等にて公開予定です。
                    </p>
                </div>
                <script>alert('test')</script>
            HTML
        ];

        /* ▲ 懸念点として該当要素に既にスタイルが当たっていた際に打ち消されてしまう可能性がある。 当てる時はインライン + important OR 専用クラスの作成
    　　    ただ完全にフリーで当てさせると大幅なレイアウト崩れにつながる危険性があるので、導線は残す必要あり 
    　　 */

        // body内をサニタイズ( 登録時も確認する事 )
        $config = HTMLPurifier_Config::createDefault();
        $config->set('CSS.AllowTricky', true);
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.SafeInlineCSS', true);
        $config->set('HTML.Allowed', implode(',', [
            'p[style]',
            'b',
            'strong',
            'i',
            'em',
            'ul[style]',
            'ol[style]',
            'li[style]',
            'a[href|target|rel|style]',
            'iframe[src|width|height|frameborder|allowfullscreen]',
            'br',
            'span[style]',
            'div[style]',
            'h1[style]',
            'h2[style]',
            'h3[style]',
            'h4[style]',
            'h5[style]',
            'h6[style]',
            'img[src|alt|width|height]',
        ]));

        $config->set('CSS.AllowedProperties', [
            'color',
            'background-color',
            'font-size',
            'text-align',
            'line-height',
            'margin',
            'margin-top',
            'margin-bottom',
            'margin-left',
            'margin-right',
            'padding',
            'padding-top',
            'padding-bottom',
            'padding-left',
            'padding-right',
            'display',
            'border',
            'border-radius'
        ]);

        $purifier = new HTMLPurifier($config);
        $clean_html = $purifier->purify($information['body']); // サニタイズ

        $information['body'] =  $clean_html;

        // 結果を返す
        return $information;
    }
}
