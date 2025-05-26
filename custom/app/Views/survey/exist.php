<?php
include('/var/www/html/moodle/custom/app/Views/common/header.php');
unset($_SESSION['errors'], $_SESSION['old_input']);
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="QUESTIONNAIRE">アンケート</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="form" class="complete">
            <div class="whitebox form_cont">
                <p class="cpt_txt">このアンケートにはすでにご回答いただいています。</p>
                <p class="sent">
                    ご協力ありがとうございました。
                </p>
            </div>
            <a href=" /custom/app/Views/event/register.php" class="btn btn_blue arrow box_bottom_btn">申し込みイベントへ戻る</a>
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