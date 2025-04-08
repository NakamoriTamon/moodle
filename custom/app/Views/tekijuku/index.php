<?php
include('/var/www/html/moodle/custom/app/Views/common/header.php');

require_once('/var/www/html/moodle/custom/app/Controllers/tekijuku/tekijuku_index_controller.php');

$tekijuku_index_controller = new TekijukuIndexController;
$isTekijukuCommemorationMember = $tekijuku_index_controller->isTekijukuCommemorationMember();
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/tekijuku.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="ABOUT TEKIJUKU COMMEMORATION ASSOCIATION">
            適塾記念会について
        </h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="juku">
            <div class="juku_about01">
                <p class="desc sent">
                    適塾記念会では、昭和27年(1952)の設立以来、現在に至るまで、適塾に関する研究・顕彰活動を、市民の皆様とともに進めて来ました。
                    その成果は会誌『適塾』や、適塾特別展示、適塾講座などの形で一般に公開しています。 適塾記念会に入会いただくと、適塾の参観料が無料となる等、
                    下記の特典があります。適塾記念会の活動に賛同いただける方は、ぜひご入会ください。
                    なお、適塾記念会の沿革や活動等の詳細については、
                    <a href="https://www.tekijuku.osaka-u.ac.jp/ja" target="_blank">適塾記念センターのホームページ</a> をご覧ください。
                </p>
                <!-- <img src="/custom/public/assets/common/img/img_juku.png" alt="" /> -->
            </div>
            <div class="juku_about02">
                <div class="inner_m juku_about-cont">
                    <div class="juku_about-txt">
                        <h2 class="juku_about-txt__ttl">適塾について</h2>
                        <p class="sent">
                            大阪市中央区北浜のオフィス街に建つ適塾は、国の重要文化財に指定されている歴史的建造物です。現存唯一の蘭学塾遺構であり、大阪の町家として最古級に属す点で貴重です。
                            適塾は天保9年(1838)、 幕末の蘭医学研究所の第一人者とされる緒方洪庵(1810～1863)により開設されました。洪庵は西洋医学研究や感染症対策に尽力し、医学史上に多くの業績を残しました。教育では福沢諭吉・大村益次郎・橋本左内・長与専斎等、日本の近代化に貢献する幾多の人物を育成しました。
                            適塾建物は昭和17年(1942)、緒方家より国に寄付されて以来、大阪大学が管理しています。
                        </p>
                    </div>
                    <img src="/custom/public/assets/common/img/img_juku02.png" alt="" />
                </div>
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
                    <li>4.会誌『適塾』を発行いたします。（毎年１回、12～1月頃）</li>
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
                        初年度のお支払いでクレジットカードを選択いただいた方は、次年度からの年会費を自動引落にすることができます。
                        また、マイページからいつでも変更可能です。
                    </li>
                    <li>
                        口数は、普通会員、賛助会員とも年間何口でもお申込みいただけます。<br />
                        なお、一度に複数年度分の年会費のお支払いはできなくなりましたので、クレジットカードによる自動引落をご利用ください。
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
                <?php if ($isTekijukuCommemorationMember === 'isNotMember') : ?>
                    <div id="entry_btn-container" class="btn-container ">
                        <form action="/custom/app/Controllers/tekijuku/tekijuku_index_controller.php" method="POST">
                            <input type="hidden" name="post_kbn" value="tekijuku_route">
                            <button type="submit" class="btn arrow btn_entry btn_red box_bottom_btn">入会する</button>
                        </form>
                    </div>
                <?php elseif ($isTekijukuCommemorationMember === 'isActive'): ?>
                    <div class="btn-container">
                        <button class="btn btn_gray tekijuki_add_btn" disabled>入会済み</button>
                    </div>
                <?php else: ?>
                    <div class="btn-container">
                        <button class="btn btn_gray tekijuki_add_btn" disabled>退会済み</button>
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
</body>

</html>