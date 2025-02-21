<?php 
include('/var/www/html/moodle/custom/app/Views/common/header.php'); 

require_once('/var/www/html/moodle/custom/app/Controllers/tekijuku/tekijuku_index_controller.php');

$tekijuku_index_controller = new TekijukuIndexController;
$isTekijukuCommemorationMember = $tekijuku_index_controller->isTekijukuCommemorationMember();
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/tekijuku.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="ABOUT TEKIJUKU">適塾記念会について</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="juku">
            <div class="juku_about">
                <p class="desc sent">
                    適塾記念会では、設立以来現在に至るまで、市民の皆様とともに適塾と緒方洪庵の事績を研究・顕彰し、その成果を会誌『適塾』や、適塾特別展示、適塾講座などの形で一般に公開しています。適塾記念会の活動に賛同いただける方は、ぜひご入会ください。なお、適塾記念会の沿革や活動などの詳細については、<a
                        href="https://www.tekijuku.osaka-u.ac.jp/ja"
                        target="_blank">適塾記念センターのホームページ</a>
                    をご覧ください。
                </p>
                <img src="/custom/public/assets/common/img/img_juku.png" alt="" />
            </div>
            <div class="juku_block prize">
                <h2 class="block_ttl">特典</h2>
                <ul class="juku_list sent">
                    <li>
                        1.『適塾』への参観料がデジタル会員証提示で何回でも無料となります。<br />
                        （「適塾特別展示」など特別なイベントの開催時期を含む）
                    </li>
                    <li>2.毎年春と秋に開催される「適塾見学会」にご参加いただけます。</li>
                    <li>3.毎年開催される「適塾講座」に割引料金でご参加いただけます。</li>
                    <li>4.会誌『適塾』を発行いたします。（毎年１回、12月頃）</li>
                    <li>
                        5.適塾で販売しております図録『緒方洪庵と適塾』と絵はがきを、それぞれ売価の１割引でご購入いただけます。
                    </li>
                </ul>
            </div>
            <div class="juku_block membership">
                <h2 class="block_ttl">年会費</h2>
                <ul class="memb_list">
                    <li>・普通会員　一口2,000円（4月1日から翌年3月31日まで）</li>
                    <li>・賛助会員　一口10,000円（同上）</li>
                </ul>
                <ul class="juku_list sent">
                    <li>
                        年会費は入会時に当該年度分をお支払いください。<br />
                        翌年度からは、登録いただいたメールアドレス宛に、毎年3月頃に更新の案内が届きますので、指定の期日までに次年度分の年会費をお支払いください。
                    </li>
                    <li>
                        初年度のお支払いでクレジットカード、口座振替を選択いただいた方は、次年度からの年会費を自動引落としにすることができます。
                        また、マイページからいつでも変更可能です。
                    </li>
                    <li>
                        口数は、普通会員、賛助会員とも年間何口でもお申込みいただけます。<br />
                        なお、一度に複数年度分の年会費のお支払いはできなくなりましたので、クレジットカードまたは口座振替による自動引落としをご利用ください。
                    </li>
                </ul>
            </div>
            <div class="juku_block membership">
                <h2 class="block_ttl">会員証</h2>
                <p class="sent">
                    入会いただいた方には、デジタル会員証を発行いたします（紙での発行はございません）。<br />
                    入会された当該年度（３月末まで）有効で、適塾の受付でご提示いただくと、適塾（「適塾特別展示」を含む）を何度でもご参観いただけます。
                </p>
            </div>
            <div class="juku_block membership">
                <h2 class="block_ttl">入会方法</h2>
                <p class="sent">
                    まずは、本システムでユーザー登録をしていただき、その後、適塾記念会ホームページより、入会のお申し込みをしてください。
                </p>
                <?php if (!$isTekijukuCommemorationMember) : ?>
                <div class="btn-container">
                    <form action="/custom/app/Controllers/tekijuku/tekijuku_index_controller.php" method="POST">
                        <button type="submit" class="btn btn_red">入会する</button>
                    </form>
                </div>
                <?php else : ?>
                <div class="btn-container">
                    <button class="btn btn_gray" disabled>入会済み</button>
                </div>
                <?php endif ?>
            </div>
        </section>
        <!-- faq -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>適塾記念会について</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>