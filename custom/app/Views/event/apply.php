<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Controllers/event/event_application_controller.php');
require_once('/var/www/html/moodle/custom/app/Controllers/mypage/mypage_controller.php'); // 管理者か否か確認用
include('/var/www/html/moodle/custom/app/Views/common/header.php');
// unset($SESSION->formdata);
$eventId = isset($_GET['id']) ? $_GET['id'] : null;
$courseInfoId = isset($_GET['course_info_id']) ? $_GET['course_info_id'] : null;
$formdata = null;
if (isset($SESSION->formdata)) {
    if (is_null($eventId)) {
        $eventId = isset($SESSION->formdata) && isset($SESSION->formdata['id']) ? $SESSION->formdata['id'] : null;
    }
    if (is_null($eventId)) {
        $courseInfoId = is_null($courseInfoId) && isset($SESSION->formdata) && isset($SESSION->formdata['course_info_id']) ? $SESSION->formdata['course_info_id'] : null;
    }
    $formdata = isset($SESSION->formdata) ? $SESSION->formdata : null;
}
$eventApplicationController = new EventApplicationController();
$responce = $eventApplicationController->getEvenApplication($eventId, $courseInfoId, $formdata);
$event = $responce['event'];
$event_kbn = $event['event_kbn'];
if($event_kbn == EVERY_DAY_EVENT && is_null($courseInfoId)) {
    foreach ($event['select_course'] as $no => $course) {
        $courseInfoId = $course['id'];
    }
} elseif($event_kbn == EVERY_DAY_EVENT && is_null($courseInfoId) && count($event['select_course']) == 1) {
    foreach ($event['select_course'] as $no => $course) {
        $courseInfoId = $course['id'];
    }
}

$name = "";
$kana = "";
$email = "";
$age = 0;
$guardian_kbn = "";
$guardian_name = "";
$guardian_kana = "";
$guardian_email = "";
$guardian_phone = "";
$capacity = $event['capacity'];

if($responce['event']['capacity'] == 0) {
    $aki_ticket = 50;
} else {
    $aki_ticket = $responce['aki_ticket'];
}

$event_customfield_category_id = $event['event_customfield_category_id'];
if($event_kbn === PLURAL_EVENT && !is_null($courseInfoId)) {
    $participation_fee = $event['single_participation_fee'];
} else {
    $participation_fee = $event['participation_fee'];
}
$price = 0;
$event_name = $event['name'];
$triggerOther = "";
$payMethod = null;
$ticket = 1;
$notification_kbn = 1;
$now_notification = 1;
$note = "";
$triggersArray = [];
$mailsArray = [];
// 値をDateTimeオブジェクトに変換
if(is_null($courseInfoId)) {
    $deadline = $event['deadline'];
} else {
    foreach ($event['select_course'] as $no => $course) {
        $deadline = $course['deadline_date'];
    }
}
$day = new DateTime($deadline);
$dayDate = $day->format('Y-m-d');
// 現在の日付
$now = new DateTime();
$nowDate = $now->format('Y-m-d');
$tekijuku_discount = 0;
$tekijuku_text = "";
if (isloggedin() && isset($_SESSION['USER'])) {
    global $DB, $USER;
    
    $mypage_controller = new MypageController;
    $user = $mypage_controller->getUser(); // ユーザーの情報を引っ張ってくる
    $tekijuku = $mypage_controller->getTekijukuCommemoration();
    $is_general_user = $mypage_controller->isGeneralUser($user->id);
    if (!$is_general_user && $user) {
        echo '<script type="text/javascript">
            window.location.href = "/custom/app/Views/logout/index.php";
            </script>';
        exit();
    }

    if($tekijuku) {
        $tekijuku_flg = true;
        $tekijuku_discount = empty($event['tekijuku_discount']) ? 0 : $event['tekijuku_discount'];
        if($tekijuku_discount > 0) {
            $price = $participation_fee - $tekijuku_discount;
            $tekijuku_text = "　(適塾記念会会員割引: {$tekijuku_discount}円　適用価格)";
        }
    }
    // 必要な情報を取得
    $name = $user->name ?? "";
    $kana = $user->name_kana ?? "";
    $email = $_SESSION['USER']->email ?? "";
    $now_notification = $user->notification_kbn;
    $birthday = $user->birthday ?? "";
    $birthDate = new DateTime($birthday);
    $today = new DateTime(); // 現在の日付
    $age = $birthDate->diff($today)->y; // 年齢を取得
    if($age <= ADULT_AGE) {
        $guardian_name = $user->guardian_name ?? "";
        $guardian_kbn = $user->guardian_kbn ?? "";
        $guardian_email = $user->guardian_email ?? "";
        $guardian_phone = $user->guardian_phone ?? "";
    }
}
if (!empty($old_input)) {
    $payMethod = isset($old_input['pay_method']) ? $old_input['pay_method'] : null;
    $ticket = isset($old_input['ticket']) ? $old_input['ticket'] : 1;
    $price = $ticket * $participation_fee;
    $triggerOther = isset($old_input['trigger_other']) ? $old_input['trigger_other'] : "";
    $note = isset($old_input['note']) ? $old_input['note'] : "";
    $triggersArray = isset($old_input['trigger']) ? $old_input['trigger'] : [];
    $mailsArray = isset($old_input['companion_mails']) ? $old_input['companion_mails'] : [];
    $notification_kbn = isset($old_input['notification_kbn']) ? $old_input['notification_kbn'] : null;
    $guardian_name = isset($old_input['guardian_name']) ? $old_input['guardian_name'] : $guardian_name;
    $guardian_kana = isset($old_input['guardian_kana']) ? $old_input['guardian_kana'] : $guardian_kana;
    $guardian_kbn = isset($old_input['guardian_kbn']) ? $old_input['guardian_kbn'] : $guardian_kbn;
    $guardian_email = isset($old_input['guardian_email']) ? $old_input['guardian_email'] : $guardian_email;
    $guardian_phone = isset($old_input['guardian_phone']) ? $old_input['guardian_phone'] : $guardian_phone;
} else if (!is_null($formdata) && empty($errors)) {
    $payMethod = isset($formdata['pay_method']) ? $formdata['pay_method'] : null;
    $ticket = isset($formdata['ticket']) ? $formdata['ticket'] : 1;
    $price = $ticket * $participation_fee;
    $triggerOther = isset($formdata['trigger_other']) ? $formdata['trigger_other'] : "";
    $note = isset($formdata['note']) ? $formdata['note'] : "";
    $triggers = isset($formdata['triggers']) ? $formdata['triggers'] : [];
    if (is_array($triggers)) {
        $triggersArray = $triggers;
    } else {
        $triggersArray = explode(',', $triggers); // 配列に変換
    }
    $companion_mails = isset($formdata['companion_mails']) ? $formdata['companion_mails'] : [];
    if (is_array($companion_mails)) {
        $mailsArray = $companion_mails;
    } else {
        $mailsArray = explode(',', $companion_mails); // 配列に変換
    }
    $notification_kbn = isset($formdata['notification_kbn']) ? $formdata['notification_kbn'] : null;
    $applicant_kbn = isset($formdata['applicant_kbn']) ? $formdata['applicant_kbn'] : $applicant_kbn;
    $guardian_name = isset($formdata['guardian_name']) ? $formdata['guardian_name'] : $guardian_name;
    $guardian_kana = isset($formdata['guardian_kana']) ? $formdata['guardian_kana'] : $guardian_kana;
    $guardian_email = isset($formdata['guardian_email']) ? $formdata['guardian_email'] : $guardian_email;
    $guardian_phone = isset($formdata['guardian_phone']) ? $formdata['guardian_phone'] : $guardian_phone;
}

?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/event_apply.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="APPLICATION">イベント申し込み</h2>
    </section>
    <!-- heading -->
    <?php if ($deadline != null && $dayDate < $nowDate): ?>
        <div class="inner_l">
            <?php if (!empty($basic_error)) { ?><p class="error"> <?= $basic_error ?></p><?php } ?>
            <section id="form" class="event entry">
                <form method="POST" action="confirm.php" class="whitebox form_cont">
                    <div class="inner_m">
                        <div class="form_btn">
                            <p class="list_label">申し込みの受付を終了致しました。</p>
                        </div>
                        <div class="form_btn">
                            <a href="index.php?id=<?= $eventId ?>" class="btn btn_gray">戻る</a>
                        </div>
                        <div>
                </form>
            </section>
        </div>
    <?php elseif ($capacity != 0 && $aki_ticket <= 0): ?>
        <div class="inner_l">
            <?php if (!empty($basic_error)) { ?><p class="error"> <?= $basic_error ?></p><?php } ?>
            <section id="form" class="event entry">
                <form method="POST" action="confirm.php" class="whitebox form_cont">
                    <div class="inner_m">
                        <div class="form_btn">
                            <p class="list_label">定員数に達したため受付を終了致しました。</p>
                        </div>
                        <div class="form_btn">
                            <a href="index.php?id=<?= $eventId ?>" class="btn btn_gray">戻る</a>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    <?php else: ?>
        <div class="inner_l">
            <?php if (!empty($basic_error)) { ?><p class="error"> <?= $basic_error ?></p><?php } ?>
            
            <section id="form" class="event entry">
                <ul id="flow">
                    <li class="active">入力</li>
                    <li>確認</li>
                    <li>完了</li>
                </ul>
                <form method="POST" action="/custom/app/Controllers/event/event_application_confirm_controller.php" class="whitebox form_cont">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($eventId ?? "") ?>">
                    <input type="hidden" name="course_info_id" value="<?= htmlspecialchars($courseInfoId ?? "") ?>">
                    <input type="hidden" name="event_customfield_category_id" value="<?= htmlspecialchars($event_customfield_category_id) ?>">
                    <input type="hidden" id="guardian_kbn" name="guardian_kbn" value="<?= htmlspecialchars($guardian_kbn) ?>">
                    <input type="hidden" id="participation_fee" name="participation_fee" value="<?= htmlspecialchars($participation_fee) ?>">
                    <input type="hidden" id="name" name="name" value="<?= htmlspecialchars($name) ?>">
                    <input type="hidden" id="kana" name="kana" value="<?= htmlspecialchars($kana) ?>">
                    <input type="hidden" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
                    <input type="hidden" id="age" name="age" value="<?= htmlspecialchars($age) ?>">
                    <input type="hidden" id="event_name" name="event_name" value="<?= htmlspecialchars($event_name) ?>">
                    <input type="hidden" id="aki_ticket" name="aki_ticket" value="<?= htmlspecialchars($aki_ticket) ?>">
                    <input type="hidden" id="price" name="price" value="<?= htmlspecialchars($price) ?>">
                    <input type="hidden" id="now_notification" name="now_notification" value="<?= htmlspecialchars($now_notification) ?>">
                    <input type="hidden" id="event_kbn" name="event_kbn" value="<?= htmlspecialchars($event_kbn) ?>">
                    <input type="hidden" id="tekijuku_discount" name="tekijuku_discount" value="<?= htmlspecialchars($tekijuku_discount) ?>">
                    <div class="inner_m">
                        <ul class="list">
                            <span class="error-msg">
                                <?php if (!empty($errors['message_error'])): ?>
                                    <?= htmlspecialchars($errors['message_error']); ?>
                                <?php endif; ?>
                            </span>
                            <li class="list_item01">
                                <p class="list_label">お名前</p>
                                <p class="list_field"><?= htmlspecialchars($name) ?></p>
                            </li>
                            <li class="list_item02">
                                <p class="list_label">フリガナ</p>
                                <p class="list_field"><?= htmlspecialchars($kana) ?></p>
                            </li>
                            <li class="list_item03">
                                <p class="list_label">チケット名称</p>
                                <p class="list_field"><?= htmlspecialchars($event_name) ?></p>
                            </li>
                            <li class="long_item">
                                <p class="list_label">開催日</p>
                                <div class="list_field f_txt list_col">
                                    <?php foreach ($event['select_course'] as $no => $course): ?>
                                        <p class="f_check">
                                            <?= $no ?>回目：<?= (new DateTime($course['course_date']))->format('Y年m月d日'); ?>
                                        </p>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                            <span class="error-msg" id="ticket-error">
                                <?php if (!empty($errors['ticket'])): ?>
                                    <?= htmlspecialchars($errors['ticket']); ?>
                                <?php endif; ?>
                            </span>
                            <li class="list_item04 req">
                                <p class="list_label">枚数選択</p>
                                <div class="list_field f_num">
                                    <button type="button" class="num_min">ー</button>
                                    <input type="number" min="1" id="ticket" name="ticket" value="<?= htmlspecialchars($ticket) ?>" class="num_txt" />
                                    <button type="button" class="num_plus">＋</button>
                                    <?php if($event_kbn == EVERY_DAY_EVENT): ?>
                                        (申込できる枚数：<?= htmlspecialchars(number_format($aki_ticket)) ?>)
                                    <?php else: ?> 
                                        (空き枠：<?= htmlspecialchars(number_format($aki_ticket)) ?>)
                                    <?php endif; ?>
                                </div>
                            </li>
                            
                            <li class="list_item05">
                                <p class="list_label">金額</p>
                                <?php if($price < 1): ?>
                                    <p class="list_field" id="show_price">無料<?= $tekijuku_text ?></p>
                                <?php else: ?>
                                    <p class="list_field" id="show_price"><?= htmlspecialchars(number_format($price)); ?>円</p>
                                <?php endif; ?>
                            </li>
                            <span class="error-msg" id="companion_mails-error">
                                <?php if (!empty($errors['companion_mails'])): ?>
                                    <?= htmlspecialchars($errors['companion_mails']); ?>
                                <?php endif; ?>
                            </span>
                            <li class="list_item07 long_item">
                                <p class="list_label" id="other_mails_tag" <?php if (empty($mailsArray)): ?>style="display: none;"<?php endif; ?>>複数チケット申し込みの場合お連れ様のメールアドレス</p>
                                <div class="list_field f_txt list_col" id="input_emails">
                                    <?php if (!empty($mailsArray)): ?>
                                        <?php foreach ($mailsArray as $key => $mail): ?>
                                            <p class="f_check">
                                                <input type="email" style="margin-right: 2rem" name="companion_mails[]" value="<?= htmlspecialchars($mail) ?>" placeholder="メールアドレス <?= $key + 1 ?>" ;>
                                            </p>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </li>
                            <span class="error-msg" id="trigger-error">
                                <?php if (!empty($errors['trigger'])): ?>
                                    <?= htmlspecialchars($errors['trigger']); ?>
                                <?php endif; ?>
                            </span>
                            <li class="list_item09 long_item">
                                <p class="list_label">
                                    本イベントはどのようにお知りになりましたか？<span>※複数選択可</span>
                                </p>
                                <div class="list_field list_col">
                                    <?php foreach ($responce['cognitions'] as $cognition): ?>
                                        <p class="f_check">
                                            <label><input type="checkbox" name="trigger[]" value="<?= $cognition['id'] ?>" <?php echo in_array($cognition['id'], $triggersArray) ? 'checked' : ''; ?> /><?= htmlspecialchars($cognition['name']) ?></label>
                                            <?php if ($cognition['id'] == 1 || $cognition['id'] == 2 || $cognition['id'] == 10): ?>
                                                <span class="comment">※「その他」の欄にどこでご覧になったか具体的にご記載ください</span>
                                            <?php endif; ?>
                                        </p>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                            <span class="error-msg" id="trigger-error">
                                <?php if (!empty($errors['trigger_other'])): ?>
                                    <?= htmlspecialchars($errors['trigger_other']); ?>
                                <?php endif; ?>
                            </span>
                            <li class="list_item10 long_item">
                                <p class="list_label">その他</p>
                                <div class="list_field f_txtarea">
                                    <textarea name="trigger_other"><?= htmlspecialchars($triggerOther) ?></textarea>
                                </div>
                            </li>
                            <span class="error-msg" id="trigger-error">
                                <?php if (!empty($errors['pay_method'])): ?>
                                    <?= htmlspecialchars($errors['pay_method']); ?>
                                <?php endif; ?>
                            </span>
                            <?php if($participation_fee < 1): ?>
                                <input type="hidden" name="pay_method" value="<?= FREE_EVENT ?>">
                            <?php else: ?>
                                <li class="list_item06 req">
                                    <p class="list_label">お支払方法</p>
                                    <div class="list_field list_row">
                                        <?php foreach ($responce['paymentTypes'] as $paymentType): ?>
                                            <label class="f_radio">
                                                <input type="radio" name="pay_method" value="<?= $paymentType['id'] ?>" <?php if ($paymentType['id'] == $payMethod): ?>checked<?php endif; ?>>
                                                <?= $paymentType['name'] ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </li>
                            <?php endif; ?>
                            <span class="error-msg" id="notification_kbn-error">
                                <?php if (!empty($errors['notification_kbn'])): ?>
                                    <?= htmlspecialchars($errors['notification_kbn']); ?>
                                <?php endif; ?>
                            </span>
                            <?php if ("1" == $now_notification): ?>
                                <input type="hidden" name="notification_kbn" value="1">
                            <?php else: ?>
                            <li class="list_item08 req">
                                <p class="list_label">
                                    今後大阪大学からのメールによるイベントのご案内を希望されますか？
                                </p>
                                <div class="list_field list_row">
                                    <label class="f_radio"><input type="radio" name="notification_kbn" value="1" <?php if ("1" == $notification_kbn): ?>checked<?php endif; ?> />希望する</label>
                                    <label class="f_radio"><input type="radio" name="notification_kbn" value="2" <?php if ("2" == $notification_kbn): ?>checked<?php endif; ?> />希望しない</label>
                                </div>
                            </li>
                            <?php endif; ?>
                            <span class="error-msg" id="note-error">
                                <?php if (!empty($errors['note'])): ?>
                                    <?= htmlspecialchars($errors['note']); ?>
                                <?php endif; ?>
                            </span>
                            <li class="list_item10 long_item">
                                <p class="list_label">備考欄</p>
                                <div class="list_field f_txtarea">
                                    <textarea name="note"><?= htmlspecialchars($note) ?></textarea>
                                </div>
                            </li>
                            <?php if (!empty($errors['passage'])): ?>
                                <?php foreach ($errors['passage'] as $key => $message): ?>
                                    <?php if (!empty($message)): ?>
                                        <div class="error-msg"><?= htmlspecialchars($message); ?></div><br>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php echo $responce['passage'] ?>
                            <?php if (!empty($guardian_kbn) && $age <= ADULT_AGE): ?>
                                <li>
                                    <span class="error-msg" id="applicant_kbn-error">
                                        <?php if (!empty($errors['applicant_kbn'])): ?>
                                            <?= htmlspecialchars($errors['applicant_kbn']); ?>
                                        <?php endif; ?>
                                    </span>
                                </li>
                                <li class="long_item">
                                    <div class="list_field list_col">
                                        <p class="f_check">
                                            <label>
                                                <input type="checkbox" id="applicant_kbn" name="applicant_kbn" value="1" <?php if (!empty($applicant_kbn)): ?> checked <?php endif ?>><span style="font-weight: bold; color: #2D287F;">この申し込みは保護者の許可を得ています</span>
                                            </label>
                                        </p>
                                    </div>
                                </li>
                                <span class="error-msg" id="guardian_name-error">
                                    <?php if (!empty($errors['guardian_name'])): ?>
                                        <?= htmlspecialchars($errors['guardian_name']); ?>
                                    <?php endif; ?>
                                </span>
                                <li class="long_item">
                                    <p class="list_label">保護者名</p>
                                    <div class="list_field f_txt" id="guardian_name">
                                        <input type="text" style="margin-right: 2rem" name="guardian_name" value="<?= htmlspecialchars($guardian_name) ?>" ;>
                                    </div>
                                </li>
                                <span class="error-msg" id="guardian_kana-error">
                                    <?php if (!empty($errors['guardian_kana'])): ?>
                                        <?= htmlspecialchars($errors['guardian_kana']); ?>
                                    <?php endif; ?>
                                </span>
                                <li class="long_item">
                                    <p class="list_label">保護者名フリガナ</p>
                                    <div class="list_field f_txt" id="guardian_kana">
                                        <input type="text" style="margin-right: 2rem" name="guardian_kana" value="<?= htmlspecialchars($guardian_kana) ?>" ;>
                                    </div>
                                </li>
                                <span class="error-msg" id="guardian_email-error">
                                    <?php if (!empty($errors['guardian_email'])): ?>
                                        <?= htmlspecialchars($errors['guardian_email']); ?>
                                    <?php endif; ?>
                                </span>
                                <li class="long_item">
                                    <p class="list_label">保護者連絡先メールアドレス</p>
                                    <div class="list_field f_txt" id="guardian_email">
                                        <input type="email" style="margin-right: 2rem" name="guardian_email" value="<?= htmlspecialchars($guardian_email) ?>" ;>
                                    </div>
                                </li>
                                <span class="error-msg" id="guardian_phone-error">
                                    <?php if (!empty($errors['guardian_phone'])): ?>
                                        <?= htmlspecialchars($errors['guardian_phone']); ?>
                                    <?php endif; ?>
                                </span>
                                <li class="long_item">
                                    <p class="list_label">保護者連絡先電話番号</p>
                                    <div class="list_field f_txt" id="guardian_phone">
                                        <input type="text" style="margin-right: 2rem" name="guardian_phone" value="<?= htmlspecialchars($guardian_phone) ?>" ;>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>

                        <div class="form_btn">
                            <?php if (isloggedin()): ?>
                                <input type="submit" id="entry_btn" class="btn btn_red" value="確認画面へ進む" />
                            <?php else: ?>
                                <label class="label_name" for="name"><span id="warning" class="error-msg">ログインしてください。</span></label>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </section>
            <!-- contact -->
        </div>
    <?php endif; ?>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>イベント申し込み</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>

<script>
    const tekijuku_text = <?= json_encode($tekijuku_text, JSON_UNESCAPED_UNICODE) ?>;
    const participation_fee = $('#participation_fee').val();
    const tekijuku_discount = $('#tekijuku_discount').val();
    const akiTicketCount = $('#aki_ticket').val();
    // ブラウザバック対応
    $('input[name="ticket"]').on('change', function() {
        const price = participation_fee * $(this).val() - tekijuku_discount;
        if(price == 0) {
            $('#show_price').text("無料" + tekijuku_text);
        } else {
            $('#show_price').text(price.toLocaleString() + "円" + tekijuku_text);
        }

        $('#price').val(price);
    });

    document.getElementById('ticket').addEventListener('blur', function() {
        createInputMail();
    });

    function createInputMail() {
        const ticketInput = document.getElementById('ticket'); // チケット枚数の入力欄
        const emailContainer = document.getElementById('input_emails'); // メール入力欄を追加するコンテナ
        const other_mails_tag = document.getElementById('other_mails_tag');

        let ticketCount = parseInt(ticketInput.value); // 入力されたチケット枚数
        var maxValue = $('input[type="number"]').attr('max');

        // 現在のメール入力欄の数を取得
        const currentEmailFields = emailContainer.querySelectorAll('input[type="email"]').length;

        if (ticketCount > 1 && akiTicketCount >= ticketCount && ticketCount > currentEmailFields) {
            other_mails_tag.style.display = 'block';

            // チケット数が増えた場合、追加
            for (let i = currentEmailFields; i < ticketCount - 1; i++) {
                const pElement = document.createElement('p');
                pElement.classList.add('f_check');
                const newInput = document.createElement('input');
                newInput.type = 'email';
                newInput.name = 'companion_mails[]';
                newInput.style.marginRight = '2rem';
                newInput.placeholder = `メールアドレス ${i + 1}`;
                // <p> の中に <input> を追加
                pElement.appendChild(newInput);
                emailContainer.appendChild(pElement);
            }
        } else if (ticketCount <= currentEmailFields) {
            // チケット数が減った場合、余分な入力欄を削除
            for (let i = 0; currentEmailFields - ticketCount >= i; i++) {
                emailContainer.removeChild(emailContainer.lastChild);
            }
            if (ticketCount <= 1) {
                other_mails_tag.style.display = 'none';
            }
        }
    }

    $(function() {
        var number, total_numner;
        var max = akiTicketCount; //合計最大数
        var $input = $(".f_num .num_txt"); //カウントする箇所
        var $plus = $(".f_num .num_plus"); //アップボタン
        var $minus = $(".f_num .num_min"); //ダウンボタン
        //合計カウント用関数
        function total() {
            total_numner = 0;
            $input.each(function(val) {
                total_numner = $(this).val();
                const price = participation_fee * $(this).val() - tekijuku_discount;
                if(price == 0) {
                    $('#show_price').text("無料" + tekijuku_text);
                } else {
                    $('#show_price').text(price.toLocaleString() + "円" + tekijuku_text);
                }
                
                $('#price').val(price);
            });
            createInputMail();
            return total_numner;
        }
        //ロード時
        $(document).ready(function() {
            $input.each(function() {
                number = Number($(this).val());
                if (number == max) {
                    $(this).next($plus).prop("disabled", true);
                } else if (number == 0) {
                    $(this).prev($minus).prop("disabled", true);
                }
            });
            total();
            if (total_numner == max) {
                $plus.prop("disabled", true);
            } else {
                $plus.prop("disabled", false);
            }
        });
        //ダウンボタンクリック時
        $minus.on("click", function() {
            total();
            number = Number($(this).next($input).val());
            if (number > 1) {
                $(this)
                    .next($input)
                    .val(number - 1);
                if (number - 1 == 0) {
                    $(this).prop("disabled", true);
                }
                $(this).next().next($plus).prop("disabled", false);
            } else {
                $(this).prop("disabled", true);
            }
            total();
            if (total_numner < max) {
                $plus.prop("disabled", false);
            }
        });
        //アップボタンクリック時
        $plus.on("click", function() {
            number = Number($(this).prev($input).val());
            if (number < max) {
                $(this)
                    .prev($input)
                    .val(number + 1);
                if (number + 1 == max) {
                    $(this).prop("disabled", true);
                }
                $(this).prev().prev($minus).prop("disabled", false);
            } else {
                $(this).prop("disabled", true);
            }
            total();
            if (total_numner == max) {
                $plus.prop("disabled", true);
            } else {
                $plus.prop("disabled", false);
            }
        });
    });
</script>