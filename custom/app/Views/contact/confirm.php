<?php
include('/var/www/html/moodle/custom/app/Views/common/header.php');
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Controllers/EventController.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.php");
    exit;
}

$user_id       = htmlspecialchars($_SESSION['USER']->id, ENT_QUOTES, 'UTF-8');
$name          = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
$email         = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
$email_confirm = htmlspecialchars($_POST['email_confirm'], ENT_QUOTES, 'UTF-8');
$heading       = htmlspecialchars($_POST['heading'], ENT_QUOTES, 'UTF-8');
$message       = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');
$csrf_token    = htmlspecialchars($_POST['csrf_token'], ENT_QUOTES, 'UTF-8');

$eventController = new EventController();
$event = $eventController->getEventDetails($heading);

$name_error          = validate_contact_name($name);
$email_error         = validate_contact_email($email);
$email_confirm_error = validate_contact_email_confirm($email, $email_confirm);
$message_error       = validate_contact_message($message);

if ($name_error || $email_error || $email_confirm_error || $message_error) {
    $_SESSION['errors'] = [
        'name'          => $name_error,
        'email'         => $email_error,
        'email_confirm' => $email_confirm_error,
        'message'       => $message_error,
    ];
    $_SESSION['old_input'] = $_POST;
    $_SESSION['message_error'] = '登録に失敗しました。';
    header("Location: /custom/app/Views/contact/index.php");
    exit;
}

if (!isset($_SESSION['confirm_token'])) {
    $_SESSION['confirm_token'] = bin2hex(random_bytes(32));
}
?>


<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
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
            <form method="POST" action="contact_upsert.php" class="whitebox form_cont">
                <div class="inner_m">
                    <ul class="list">
                        <li class="list_item01">
                            <p class="list_label">お名前</p>
                            <p class="list_field f_txt"><?= htmlspecialchars($name); ?></p>
                        </li>
                        <li class="list_item02">
                            <p class="list_label">メールアドレス</p>
                            <p class="list_field f_txt"><?= htmlspecialchars($email); ?></p>
                        </li>
                        <li class="list_item03">
                            <p class="list_label">メールアドレス（確認用）</p>
                            <p class="list_field f_txt"><?= htmlspecialchars($email_confirm); ?></p>
                        </li>
                        <li class="list_item04">
                            <p class="list_label">お問い合わせの項目</p>
                            <p class="list_field f_select"><?= htmlspecialchars($event['name']); ?></p>
                        </li>
                        <li class="list_item05 long_item">
                            <p class="list_label">お問い合わせ内容</p>
                            <p class="list_field f_txtarea">
                                <?= htmlspecialchars(nl2br($message)); ?>
                            </p>
                        </li>
                    </ul>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <input type="hidden" name="name" value="<?php echo $name; ?>">
                    <input type="hidden" name="email" value="<?php echo $email; ?>">
                    <input type="hidden" name="email_confirm" value="<?php echo $email_confirm; ?>">
                    <input type="hidden" name="heading" value="<?php echo $event['name']; ?>">
                    <input type="hidden" name="message" value="<?php echo $message; ?>">
                    <input type="hidden" name="confirm_token" value="<?php echo $_SESSION['confirm_token']; ?>">
                </div>

                <div class="form_btn">
                    <input type="submit" class="btn btn_red" value="この内容で送信する" />
                    <input type="button" class="btn btn_gray" value="内容を修正する" onclick="location.href='index.php';" />
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
<script>
    // 送信時にボタンを無効化して二重クリックを防止
    document.getElementById('confirmForm').addEventListener('submit', function(e) {
        document.getElementById('submitBtn').disabled = true;
    });
</script>
<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>