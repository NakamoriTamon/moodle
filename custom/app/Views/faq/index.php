<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/faq.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="FAQ">よくある質問</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="faq">
            <ul class="faq_tab">
                <li><a href="#faq01">ユーザー登録・支払いについて</a></li>
                <li><a href="#faq02">講座申し込みについて</a></li>
                <li><a href="#faq03">講座の案内送付について</a></li>
            </ul>
            <div id="faq01" class="faq_block">
                <h2 class="block_ttl">ユーザー登録・支払いについて</h2>
                <ul class="list">
                    <li>
                        <p class="l_quest">ユーザー登録なしでも申し込めますか？</p>
                        <p class="l_answer">ユーザー登録は全ての方が必要です。</p>
                    </li>
                    <li>
                        <p class="l_quest">決済方法は何がありますか。</p>
                        <p class="l_answer">テキストテキストテキストテキストテキストテキスト</p>
                    </li>
                    <li>
                        <p class="l_quest">メールアドレスを変更したいのですが。</p>
                        <p class="l_answer">テキストテキストテキストテキストテキストテキスト</p>
                    </li>
                    <li>
                        <p class="l_quest">
                            適塾記念会に入会したいのですが、その場合もユーザー登録は必要ですか。
                        </p>
                        <p class="l_answer">テキストテキストテキストテキストテキストテキスト</p>
                    </li>
                    <li>
                        <p class="l_quest">ユーザー登録後の確認のメールが届かないのですが。</p>
                        <p class="l_answer">テキストテキストテキストテキストテキストテキスト</p>
                    </li>
                    <li>
                        <p class="l_quest">
                            適塾記念会の会費ですが、翌年度の会費更新は、事前に案内をいただけるのでしょうか。
                        </p>
                        <p class="l_answer">テキストテキストテキストテキストテキストテキスト</p>
                    </li>
                    <li>
                        <p class="l_quest">
                            マイページへのログインID（メールアドレス）やパスワードを忘れてしまったのですが、どうすればよいですか。
                        </p>
                        <p class="l_answer">テキストテキストテキストテキストテキストテキスト</p>
                    </li>
                    <li>
                        <p class="l_quest">
                            家族／友達同士で一つの会員番号を共有しようと思いますが、構いませんか。
                        </p>
                        <p class="l_answer">テキストテキストテキストテキストテキストテキスト</p>
                    </li>
                </ul>
            </div>
            <div id="faq02" class="faq_block">
                <h2 class="block_ttl">講座申し込みについて</h2>
                <ul class="list">
                    <li>
                        <p class="l_quest">
                            申し込んでいた講座に急用で行けなくなったのですが、返金はしてもらえますか。
                        </p>
                        <p class="l_answer">テキストテキストテキストテキストテキストテキスト</p>
                    </li>
                    <li>
                        <p class="l_quest">
                            12歳（小６）です。自分だけでユーザー登録して申し込んでもいいですか。
                        </p>
                        <p class="l_answer">テキストテキストテキストテキストテキストテキスト</p>
                    </li>
                    <li>
                        <p class="l_quest">
                            自分が代表者として、友人分と２人分のチケットを申し込むつもりです。オンラインチケットのQRコードは、自分と友人それぞれに送っていただけるのですか。
                        </p>
                        <p class="l_answer">テキストテキストテキストテキストテキストテキスト</p>
                    </li>
                    <li>
                        <p class="l_quest">
                            申し込んでいた講座に都合で行けなくなりました。友人や家族に権利を譲渡しても構いませんでしょうか。
                        </p>
                        <p class="l_answer">テキストテキストテキストテキストテキストテキスト</p>
                    </li>
                </ul>
            </div>
            <div id="faq03" class="faq_block">
                <h2 class="block_ttl">講座の案内送付について</h2>
                <ul class="list">
                    <li>
                        <p class="l_quest">
                            申し込んでおいたはずのイベントの直前案内（オンライン講座のURL）が届かないのですが。
                        </p>
                        <p class="l_answer">テキストテキストテキストテキストテキストテキスト</p>
                    </li>
                    <li>
                        <p class="l_quest">講座のお知らせのDMが届くのが煩わしいので、止めてほしい。</p>
                        <p class="l_answer">テキストテキストテキストテキストテキストテキスト</p>
                    </li>
                </ul>
            </div>
        </section>
        <!-- faq -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>よくある質問</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>

<script>
    // スムーススクロール
    $(function() {
        $('a[href^="#"]').click(function() {
            var adjust = 0;
            var speed = 400;
            var href = $(this).attr("href");
            var target = $(href == "#" || href == "" ? "html" : href);
            var position = target.offset().top + adjust - 150;
            $("body,html").animate({
                scrollTop: position
            }, speed, "swing");
            return false;
        });
    });

    // アコーディオン
    $(function() {
        $(".l_quest").click(function() {
            $(this).next(".l_answer").slideToggle();
            $(this).parent().toggleClass("active");
        });
    });
</script>