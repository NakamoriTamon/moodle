<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/setting.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="PASSWORD RESET">パスワードの再設定</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="setting" class="pass_reset">
            <form method="" action="/custom/app/Views/mypage/index.php" class="whitebox set_form">
                <div class="set_inner">
                    <p class="sent">新しく設定するパスワードを入力して下さい。</p>
                    <ul class="list">
                        <li class="list_item01">
                            <p class="list_label">新しいパスワード</p>
                            <div class="list_field f_txt">
                                <input type="password" />
                            </div>
                            <p class="list_note">
                                8文字以上20文字以内、数字・アルファベットを組み合わせてご入力ください。
                            </p>
                            <p class="list_note">使用できる記号!"#$%'()*+,-./:;<=>?@[¥]^_{|}~</p>
                        </li>
                        <li class="list_item02">
                            <p class="list_label">パスワード（確認用）</p>
                            <div class="list_field f_txt">
                                <input type="password" />
                            </div>
                        </li>
                    </ul>
                    <input type="submit" class="btn btn_red" value="変更する" />
                </div>
            </form>
        </section>
        <!-- contact -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>パスワードの再設定</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>