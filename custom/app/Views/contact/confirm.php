<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="CONTACT">お問い合わせ</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="form" class="contact confirm">
            <ul id="flow">
                <li>入力</li>
                <li class="active">確認</li>
                <li>完了</li>
            </ul>
            <form method="" action="complete.php" class="whitebox form_cont">
                <div class="inner_m">
                    <ul class="list">
                        <li class="list_item01">
                            <p class="list_label">お名前</p>
                            <p class="list_field f_txt">阪大太郎</p>
                        </li>
                        <li class="list_item02">
                            <p class="list_label">メールアドレス</p>
                            <p class="list_field f_txt">abcdefg@gmail.com</p>
                        </li>
                        <li class="list_item03">
                            <p class="list_label">メールアドレス（確認用）</p>
                            <p class="list_field f_txt">abcdefg@gmail.com</p>
                        </li>
                        <li class="list_item04">
                            <p class="list_label">お問い合わせの項目</p>
                            <p class="list_field f_select">○○○イベントについて</p>
                        </li>
                        <li class="list_item05 long_item">
                            <p class="list_label">お問い合わせ内容</p>
                            <p class="list_field f_txtarea">
                                テキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキスト
                            </p>
                        </li>
                    </ul>
                    <div class="form_btn">
                        <input type="submit" class="btn btn_red" value="この内容で送信する" />
                        <input type="button" class="btn btn_gray" value="内容を修正する" onclick="location.href='index.php';" />
                    </div>
                </div>
            </form>
        </section>
        <!-- contact -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>お問い合わせ</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>