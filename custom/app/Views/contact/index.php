<?php include('/var/www/html/moodle/custom/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/app/Controllers/mypage/mypage_controller.php');
require_once('/var/www/html/moodle/custom/app/Controllers/contact/contact_controller.php');
$mypage_controller = new MypageController();
$user = $mypage_controller->getUser();
$contact_controller = new ContactController();
$events = $contact_controller->getEventList();

$event_id = $_GET['event_id'] ?? "";

$name = "";
$email = "";
$email_confirm = "";
$inquiry_details = "";
$formdata = isset($SESSION->formdata) ? $SESSION->formdata : null;
if(isset($old_input) && !empty($old_input)) {
    if(isset($old_input['name']) && !empty($old_input['name'])) {
        $name = $old_input['name'];
    } else if($user) {
        $name = $user->name;
    }
    if(isset($old_input['email']) && !empty($old_input['email'])) {
        $email = $old_input['email'];
    } else if($user) {
        $email = $user->email;
        $email_confirm = $email;
    }
    if(isset($old_input['email_confirm']) && !empty($old_input['email_confirm'])) {
        $email_confirm = $old_input['email_confirm'];
    }
    if(isset($old_input['inquiry_details']) && !empty($old_input['inquiry_details'])) {
        $inquiry_details = $old_input['inquiry_details'];
    }
} else if (!is_null($formdata) && empty($errors)) {
    $formdata = $SESSION->formdata;
    if(isset($formdata['name']) && !empty($formdata['name'])) {
        $name = $formdata['name'];
    } else if($user) {
        $name = $user->name;
    }
    if(isset($formdata['email']) && !empty($formdata['email'])) {
        $email = $formdata['email'];
    } else if($user) {
        $email = $user->email;
        $email_confirm = $email;
    }
    if(isset($formdata['email_confirm']) && !empty($formdata['email_confirm'])) {
        $email_confirm = $formdata['email_confirm'];
    }
    if(isset($formdata['event_id']) && !empty($formdata['event_id'])) {
        $event_id = $formdata['event_id'];
    }
    if(isset($formdata['inquiry_details']) && !empty($formdata['inquiry_details'])) {
        $inquiry_details = $formdata['inquiry_details'];
    }
} elseif($user) {
    $name = $user->name;
    $email = $user->email;
    $email_confirm = $email;
}

$message_error = $_SESSION['message_error'];
unset($_SESSION['errors'], $_SESSION['old_input'], $SESSION->formdata, $_SESSION['message_error']);
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
            <form method="POST" action="/custom/app/Controllers/contact/contact_confirm_controller.php" class="whitebox form_cont">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="inner_m">
                    <ul class="list">
                        <p class="error-msg">
                            <?php if (!empty($message_error)): ?>
                                <?= htmlspecialchars($message_error); ?>
                            <?php endif; ?>
                        </p>
                        <li class="list_item01 req">
                            <p class="list_label">お名前</p>
                            <div class="list_field f_txt">
                                <input type="text" name="name" value="<?= $name ?>" />
                                <?php if (!empty($errors['name'])): ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['name']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item02 req">
                            <p class="list_label">メールアドレス</p>
                            <div class="list_field f_txt">
                                <input type="email" name="email" value="<?= $email ?>" />
                                <?php if (!empty($errors['email'])): ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['email']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item03 req">
                            <p class="list_label">メールアドレス（確認用）</p>
                            <div class="list_field f_txt">
                                <input type="email" name="email_confirm" value="<?= $email_confirm ?>" onpaste="return false" autocomplete="off"/>
                                <?php if (!empty($errors['email_confirm'])): ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['email_confirm']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item04">
                            <p class="list_label">お問い合わせの項目</p>
                            <div class="list_field f_select select">
                                <select name="event_id">
                                    <?php foreach($events as $event): ?>
                                        <option value="<?= $event['id'] ?>" <? if($event_id == $event['id']): ?>selected<?php endif; ?>>【<?= $event['name'] ?>】について</option>
                                    <?php endforeach; ?>
                                    <option value="その他「『阪大知の広場』に関しての一般的なお問い合わせ" >その他「『阪大知の広場』に関しての一般的なお問い合わせ</option>
                                </select>
                                <?php if (!empty($errors['event_id'])): ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['event_id']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item05 long_item req">
                            <p class="list_label">お問い合わせ内容</p>
                            <div class="list_field f_txtarea">
                                <textarea name="inquiry_details"><?= htmlspecialchars($inquiry_details) ?></textarea>※300文字以内
                            <?php if (!empty($errors['inquiry_details'])): ?>
                                <div class="error-msg mt-2">
                                    <?= htmlspecialchars($errors['inquiry_details']); ?>
                                </div>
                            <?php endif; ?>
                            </div>
                        </li>
                    </ul>
                    <div class="agree">
                        <p class="agree_txt">個人情報の取扱いについて</p>
                        <label for="agree"><input type="checkbox" id="agree" />同意する</label>
                    </div>
                    <div class="form_btn">
                        <input type="submit" class="btn btn_gray" id="submitBtn" value="入力内容の確認" disabled />
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
<script>
    $(document).ready(function() {
        $('#agree').on('change', function() {
            let submitBtn = $('#submitBtn');
            if ($(this).is(':checked')) {
                submitBtn.prop('disabled', false); // 有効化
                submitBtn.addClass('btn_red');
                submitBtn.removeClass('btn_gray');
            } else {
                submitBtn.prop('disabled', true);  // 無効化
                submitBtn.addClass('btn_gray');
                submitBtn.removeClass('btn_red');
            }
        });
    });
</script>