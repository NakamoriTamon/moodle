<?php
require_once('/var/www/html/moodle/config.php');
include('/var/www/html/moodle/custom/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/event/event_controller.php');
$eventId = 2;
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="CONTACT">お問い合わせ</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="form" class="contact entry">
            <ul id="flow">
                <li class="active">入力</li>
                <li>確認</li>
                <li>完了</li>
            </ul>
            <form method="POST" action="confirm.php" class="whitebox form_cont">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_SESSION['USER']->id); ?>">
                <input type="hidden" name="event_id" value="<?php echo $eventId ?>">
                <div class="inner_m">
                    <ul class="list">
                        <li class="list_item01 req">
                            <p class="list_label">お名前</p>
                            <div class="list_field f_txt">
                                <input type="text" id="name" name="name" value="<?php echo $_SESSION['USER']->username ?>" required>
                                <?php if (!empty($errors['name'])): ?>
                                    <div class="error-message">
                                        <?= htmlspecialchars($errors['name']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item02 req">
                            <p class="list_label">メールアドレス</p>
                            <div class="list_field f_txt">
                                <input type="email" id="email" name="email" value="<?php echo $_SESSION['USER']->email ?>" required>
                                <?php if (!empty($errors['email'])): ?>
                                    <div class="error-message">
                                        <?= htmlspecialchars($errors['email']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item03 req">
                            <p class="list_label">メールアドレス（確認用）</p>
                            <div class="list_field f_txt">
                                <input type="email" id="email_confirm" name="email_confirm" autocomplete="off" value="<?php echo $_SESSION['USER']->email_confirm ?>" required>
                                <?php if (!empty($errors['email_confirm'])): ?>
                                    <div class="error-message">
                                        <?= htmlspecialchars($errors['email_confirm']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item04">
                            <p class="list_label">お問い合わせの項目</p>
                            <div class="list_field f_select select">
                                <select name="heading">
                                    <option value="" disabled selected>選択してください</option>
                                    <?php if (isset($events) && !empty($events)): ?>
                                        <?php foreach ($events as $event): ?>
                                            <option value="<?= htmlspecialchars($event['id']) ?>"
                                                <?= isset($old_input['event_id']) && $event['id'] == $old_input['event_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($event['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <option value="会員登録前のご質問">会員登録前のご質問</option>
                                    <option value="その他一般的なお問い合わせ">その他一般的なお問い合わせ</option>
                                </select>
                            </div>
                        </li>
                        <li class="list_item05 long_item req">
                            <p class="list_label">お問い合わせ内容</p>
                            <div class="list_field f_txtarea">
                                <textarea name="message" cols="40" rows="20" required><?php echo $old_input['message']; ?></textarea>
                                <?php if (!empty($errors['message'])): ?>
                                    <div class="error-message">
                                        <?= htmlspecialchars($errors['message']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                    </ul>
                    <div class="agree">
                        <p class="agree_txt">個人情報の取扱いについて</p>
                        <label for="agree"><input type="checkbox" id="agree" required />同意する</label>
                    </div>
                    <div class="form_btn">
                        <input type="submit" class="btn btn_red" value="入力内容の確認" />
                    </div>
                </div>
            </form>
        </section>
        <!-- contact -->
    </div>
</main>
<script>
    const emailInput = document.getElementById('email_confirm');

    emailInput.addEventListener('copy', function(e) {
        e.preventDefault();
    });
    emailInput.addEventListener('paste', function(e) {
        e.preventDefault();
    });
    emailInput.addEventListener('cut', function(e) {
        e.preventDefault();
    });
    emailInput.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
</script>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>お問い合わせ</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>