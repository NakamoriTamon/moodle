<?php
unset($_SESSION['old_input']);
include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/setting.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="SEND PASSWORD RESET E-MAIL">パスワード再設定メールの送信</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="setting" class="pass_mail">
            <form method="POST" action="/custom/app/Controllers/user/user_pass_mail_controller.php" class="whitebox set_form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="set_inner">
                    <p class="sent">
                        パスワード再設定用のURLを<br class="pc" />メールにてお送りいたします。<br />
                        ご登録のメールアドレスを入力して下さい。
                    </p>
                    <ul class="list">
                        <li class="list_item01">
                            <p class="list_label">メールアドレス</p>
                            <div class="list_field f_txt">
                                <input type="email" name="email" value="<?= htmlspecialchars($old_input['email'] ?? '') ?>" />
                                <?php if (!empty($errors['email'])): ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['email']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                    </ul>
                    <input type="submit" class="btn btn_red" value="送信する" />
                </div>
            </form>
        </section>
        <!-- contact -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>パスワードリセットメールの送信</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>