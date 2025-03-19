<?php include('/var/www/html/moodle/custom/app/Views/common/header.php');
$event_id = "";
$name = "";
$email = "";
$email_confirm = "";
$event_name = "";
$inquiry_details = "";
if (isset($SESSION->formdata)) {
    $formdata = $SESSION->formdata;
    $event_id = $formdata['event_id'];
    $name = $formdata['name'];
    $email = $formdata['email'];
    $email_confirm = $formdata['email_confirm'];
    $event_name = $formdata['event_name'];
    $inquiry_details = $formdata['inquiry_details'];
}
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="CONTACT">お問い合わせ</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="form" class="contact confirm">
            <ul id="flow">
                <li>入力</li>
                <li class="active">確認</li>
                <li>完了</li>
            </ul>
            <form method="POST" action="/custom/app/Controllers/contact/contact_send_controller.php" class="whitebox form_cont">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_id); ?>">
                <input type="hidden" name="name" value="<?php echo htmlspecialchars($name); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="hidden" name="event_name" value="<?php echo htmlspecialchars($event_name); ?>">
                <input type="hidden" name="inquiry_details" value="<?php echo htmlspecialchars($inquiry_details); ?>">
                <div class="inner_m">
                    <ul class="list">
                        <li class="list_item01">
                            <p class="list_label">お名前</p>
                            <p class="list_field f_txt"><?= $name ?></p>
                        </li>
                        <li class="list_item02">
                            <p class="list_label">メールアドレス</p>
                            <p class="list_field f_txt"><?= $email ?></p>
                        </li>
                        <li class="list_item04">
                            <p class="list_label">お問い合わせの項目</p>
                            <p class="list_field f_select"><?= $event_name ?></p>
                        </li>
                        <li class="list_item05 long_item">
                            <p class="list_label">お問い合わせ内容</p>
                            <p class="list_field f_txtarea">
                            <?= htmlspecialchars(nl2br($inquiry_details)) ?>
                            </p>
                        </li>
                    </ul>
                    <div class="form_btn">
                        <input type="submit" class="btn btn_red" value="この内容で送信する" />
                        <input type="button" class="btn btn_gray" value="内容を修正する" onclick="location.href='index.php';" />
                    </div>
                </div>
            </form>
        </section>
        <!-- contact -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>お問い合わせ</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>