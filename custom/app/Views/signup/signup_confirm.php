<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Controllers/user/user_registration_controller.php');
$user_registration_controller = new userRegistrationController();
$result = $user_registration_controller->index($_GET['id'], $_GET['expiration_time']);
?>

<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <?php if ($result) { ?>
            <h2 class="head_ttl" data-en="REGISTRATION SUCCESSFUL">本登録<?= $result === 2 ? "済" : "完了" ?></h2>
        <?php } else { ?>
            <h2 class="head_ttl" data-en="REGISTRATION FATAL">本登録失敗</h2>
        <?php } ?>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="form" class="complete">
            <!-- <ul id="flow">
                <li>入力</li>
                <li class="active">完了</li>
            </ul> -->
            <div class="whitebox form_cont">
                <p class="cpt_txt"><?= $result ? ($result === 2 ? "既に本登録されています。" : "本登録が完了いたしました。") : "本登録に失敗しました。" ?></p>
                <p class="sent">
                    <?= $result ? ($result === 2 ? "既に本登録されています。" : "本登録が完了いたしました。") : "本登録に失敗しました。" ?><br />
                    <?php if ($result) { ?>
                        ログイン画面からログインして<br class="pc" />システムをご利用ください
                    <?php } else { ?>
                        再度手続きしてください。<br class="pc" />
                    <?php } ?>
                </p>
            </div>
            <a href="../index.php" class="btn btn_blue arrow box_bottom_btn">TOPへ戻る</a>
        </section>
        <!-- contact -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>お問い合わせ</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>

</body>
</html>