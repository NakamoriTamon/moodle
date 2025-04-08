<?php
include('/var/www/html/moodle/custom/app/Views/common/header.php');
$payment_type = $_SESSION['payment_method_type'];
// 表示内容
$title = $payment_type == 4 ? "申し込み完了" : "仮申し込み完了";
$message_title = $payment_type == 4 ? "申し込みが完了いたしました。" : "仮申し込みが完了いたしました。";
$en_title = $payment_type == 4 ? "REGISTRATION COMPLETED" : "PRE-REGISTRATION COMPLETED";
$message = $payment_type == 4 ? "お申し込みが完了いたしました。" : "お支払い期限内にお手続きをお願いいたします。";
unset($_SESSION['errors'], $_SESSION['old_input'], $_SESSION['payment_method_type']);
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="<?= htmlspecialchars($en_title) ?>"><?= htmlspecialchars($title) ?></h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="form" class="complete">
            <ul id="flow">
                <li>入力</li>
                <li class="active">完了</li>
            </ul>
            <div class="whitebox form_cont">
                <p class="cpt_txt"><?= htmlspecialchars($message_title) ?></p>
                <p class="sent">
                    この度は、イベントにお申し込みいただき、誠にありがとうございます。<br />
                    <?= htmlspecialchars($message) ?><br />
                </p>
            </div>
            <a href="/custom/app/Views/event/register.php" class="btn btn_blue arrow box_bottom_btn">申し込みイベント一覧</a>
        </section>
        <!-- contact -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="/custom/app/Views/index.php">トップページ</a></li>
    <li><?= htmlspecialchars($title) ?></li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>