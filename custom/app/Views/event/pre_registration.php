<?php
include('/var/www/html/moodle/custom/app/Views/common/header.php');
unset($_SESSION['errors'], $_SESSION['old_input'], $_SESSION['payment_method_type']);
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="PRE-REGISTRATION COMPLETED">仮申し込み完了</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="form" class="complete">
            <ul id="flow">
                <li>入力</li>
                <li class="active">完了</li>
            </ul>
            <div class="whitebox form_cont">
                <p class="cpt_txt">仮申し込みが完了いたしました。</p>
                <p class="sent">
                    この度は、イベントにお申し込みいただき、誠にありがとうございます。<br />
                    お支払い期限内にお手続きをお願いいたします。<br />
                </p>
            </div>
            <a href="/custom/app/Views/event/register.php" class="btn btn_blue arrow box_bottom_btn">申し込みイベント一覧</a>
        </section>
        <!-- contact -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="/custom/app/Views/index.php">トップページ</a></li>
    <li>仮申し込み完了</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>