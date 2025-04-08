<?php
unset($_SESSION['old_input']);
include('/var/www/html/moodle/custom/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/app/Controllers/user/user_pass_reset_controller.php');

$pass_reset_controller = new UserPassResetController();
$pass_reset_result = $pass_reset_controller->index($_GET['token']);

?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/setting.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="PASSWORD RESET">パスワードの再設定</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="setting" class="pass_reset">
            <form method="POST" action="/custom/app/Controllers/user/user_pass_upsert_controller.php" class="whitebox set_form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                <div class="set_inner">
                    <?php if (!empty($basic_error)) { ?><p class="error"> <?= $basic_error ?></p><?php } ?>
                    <?php if ($pass_reset_result) { ?>
                        <p class="sent">新しく設定するパスワードを入力して下さい。</p>
                        <ul class="list">
                            <li class="list_item01">
                                <p class="list_label">新しいパスワード</p>
                                <div class="list_field f_txt">
                                    <input type="password" name="password" />
                                    <?php if (!empty($errors['password'])): ?>
                                        <div class="error-msg mt-2">
                                            <?= htmlspecialchars($errors['password']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <p class="list_note">
                                    8文字以上20文字以内、数字・アルファベットを組み合わせてご入力ください。
                                </p>
                                <p class="list_note">使用できる記号!"#$%'()*+,-./:;<=>?@[¥]^_{|}~</p>
                            </li>
                            <li class="list_item02">
                                <p class="list_label">パスワード（確認用）</p>
                                <div class="list_field f_txt">
                                    <input type="password" name="confirm_password" />
                                    <?php if (!empty($errors['confirm_password'])): ?>
                                        <div class="error-msg mt-2">
                                            <?= htmlspecialchars($errors['confirm_password']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        </ul>
                        <input type="submit" class="btn btn_red" value="変更する" />
                    <?php } else { ?>
                        <p class="pass_reset_sent">有効期限が切れています。</p>
                        <p class="pass_reset_sent">再度、パスワード再設定のリクエストを行ってください。</p>
                    <?php } ?>
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
</body>
</html>