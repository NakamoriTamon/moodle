<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="RECONFIGURATION EMAIL SENT">再設定メールの送信完了</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="form" class="complete">
            <ul id="flow">
                <li>入力</li>
                <li class="active">完了</li>
            </ul>
            <div class="whitebox form_cont">
                <p class="cpt_txt">再設定メールを送信いたしました。</p>
                <p class="sent">
                    ご入力いただきましたメールアドレス宛にパスワード再設定を行う為のURLを送信しております。<br class="sp-none" />メールのURLよりパスワード再設定画面へお進み頂き、再設定をお願いいたします。
                </p>
                <!-- <p class="sent red">
                    ※パスワード再設定メールが届かない場合、ご入力いただきましたメールアドレスが間違っている可能性があります。<br />
                    再度、お手数ですがユーザー情報入力を行ってください。
                </p> -->
            </div>
            <a href="../index.php" class="btn btn_blue arrow box_bottom_btn">TOPへ戻る</a>
        </section>
        <!-- contact -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="/custom/app/Views/index.php">トップページ</a></li>
    <li>再設定メールの送信完了</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>
</body>
</html>