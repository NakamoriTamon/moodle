<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="PASSWORD RESET COMPLETED">パスワード再設定完了</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="form" class="complete">
            <ul id="flow">
                <li>入力</li>
                <li class="active">完了</li>
            </ul>
            <div class="whitebox form_cont">
                <p class="cpt_txt">パスワードの再設定が完了いたしました。</p>
                <p class="sent">
                    新しいパスワードでログインが可能です。<br />
                </p>
                <!-- <p class="sent red">
                    ※パスワード再設定メールが届かない場合、ご入力いただきましたメールアドレスが間違っている可能性があります。<br />
                    再度、お手数ですがユーザー情報入力を行ってください。
                </p> -->
            </div>
            <a href="/custom/app/Views/login/index.php" class="btn btn_blue arrow box_bottom_btn">ログイン</a>
        </section>
        <!-- contact -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="/custom/app/Views/index.php">トップページ</a></li>
    <li>パスワード再設定完了</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>
</body>
</html>