<?php
include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/setting.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="LOGIN">ログイン</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="setting" class="login">
            <form method="POST" action="/custom/app/Controllers/login/login_controller.php" class="whitebox set_form">
                <div class="set_inner">
                    <?php if (!empty($basic_error)) { ?><p class="error"> <?= $basic_error ?></p><?php } ?>
                    <ul class=" list">
                        <li class="list_item01">
                            <p class="list_label">メールアドレス（もしくは会員番号）</p>
                            <div class="list_field f_txt">
                                <input type="text" name="email" autocomplete="off" />
                                <?php if (!empty($errors['email'])): ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['email']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item02">
                            <p class="list_label">パスワード</p>
                            <div class="list_field f_txt">
                                <input type="password" name="password" />
                                <?php if (!empty($errors['password'])): ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['password']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                    </ul>
                    <a href="../user/pass_mail.php" class="pass_rink">パスワードをお忘れですか？</a>
                    <input type="submit" class="btn btn_red" value="ログイン" />
                    <p class="new_rink">初めての方は<a href="/custom/app/Views/user/index.php">こちらから会員登録</a></p>
                </div>
            </form>
        </section>
        <!-- contact -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>ログイン</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>