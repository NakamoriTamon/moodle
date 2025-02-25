<?php
include('/var/www/html/moodle/custom/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/app/Controllers/event/EventApplicationController.php');
// unset($SESSION->formdata);
$eventId = isset($_GET['id']) ? $_GET['id'] : null;
$courseInfoId = isset($_GET['course_info_id']) ? $_GET['course_info_id'] : null;
$formdata = null;
if (isset($SESSION->formdata)) {
    if(is_null($eventId)) {
        $eventId = isset($SESSION->formdata) && isset($SESSION->formdata['id']) ? $SESSION->formdata['id'] : null;
    }
    if(is_null($eventId)) {
        $courseInfoId = is_null($courseInfoId) && isset($SESSION->formdata) && isset($SESSION->formdata['course_info_id']) ? $SESSION->formdata['course_info_id'] : null;
    }
    $formdata = isset($SESSION->formdata) ? $SESSION->formdata : null;
}
$eventApplicationController = new EventApplicationController();
$responce = $eventApplicationController->getEvenApplication($eventId, $courseInfoId, $formdata);

$aki_ticket = $responce['event']['capacity'] - $responce['sum_ticket_count'];

$name = "";
$kana = "";
$email = "";
$guardian_kbn = "";
$guardian_name = "";
$guardian_kana = "";
$guardian_email = "";
$event = $responce['event'];
$event_customfield_category_id = $event['event_customfield_category_id'];
$participation_fee = $event['participation_fee'] * count($event['select_course']);
$price = 0;
$event_name = $event['name'];
$triggerOther = "";
$payMethod = null;
$ticket = 0;
$notification_kbn = null;
$note = "";
$triggersArray = [];
$mailsArray = [];
$deadline = $event['deadline'];
// 値をDateTimeオブジェクトに変換
$day = new DateTime($deadline);
$dayDate = $day->format('Y-m-d');
// 現在の日付
$now = new DateTime();
$nowDate = $now->format('Y-m-d');
if (isloggedin() && isset($_SESSION['USER'])) {
    global $DB, $USER;

    // 必要な情報を取得
    $userData = $DB->get_record('user', ['id' => $USER->id], 'name, name_kana, guardian_kbn
    , guardian_name, guardian_kana, guardian_email');
    $name = $userData->name ?? '';
    $kana = $userData->name_kana ?? '';
    $email = $_SESSION['USER']->email ?? "";
    $guardian_name = $userData->guardian_name ?? '';
    $guardian_kana = $userData->guardian_kana ?? '';
    $guardian_kbn = $userData->guardian_kbn ?? "";
    $guardian_email = $userData->guardian_email ?? "";
}
if(!empty($old_input)) {
    $payMethod = isset($old_input['pay_method']) ? $old_input['pay_method'] : null;
    $ticket = isset($old_input['ticket']) ? $old_input['ticket'] : null;
    $price = $ticket * $participation_fee;
    $triggerOther = $old_input['trigger_other'];
    $note = $old_input['note'];
    $triggersArray = isset($old_input['trigger']) ? $old_input['trigger'] : [];
    $mailsArray = isset($old_input['companion_mails']) ? $old_input['companion_mails'] : [];
    $notification_kbn = isset($old_input['notification_kbn']) ? $old_input['notification_kbn'] : null;
    $guardian_name = isset($old_input['guardian_name']) ? $old_input['guardian_name'] : $guardian_name;
    $guardian_kana = isset($old_input['guardian_kana']) ? $old_input['guardian_kana'] : $guardian_kana;
    $guardian_kbn = isset($old_input['guardian_kbn']) ? $old_input['guardian_kbn'] : $guardian_kbn;
    $guardian_email = isset($old_input['guardian_email']) ? $old_input['guardian_email'] : $guardian_email;
} else if (!is_null($formdata) && empty($errors)) {
    $payMethod = $formdata['pay_method'];
    $ticket = $formdata['ticket'];
    $price = $ticket * $participation_fee;
    $triggerOther = $formdata['trigger_other'];
    $note = $formdata['note'];
    $triggers = $formdata['triggers'];
    if(is_array($triggers)) {
        $triggersArray = $triggers;
    } else {
        $triggersArray = explode(',', $triggers); // 配列に変換
    }
    $companion_mails = $formdata['companion_mails'];
    if(is_array($companion_mails)) {
        $mailsArray = $companion_mails;
    } else {
        $mailsArray = explode(',', $companion_mails); // 配列に変換
    }
    $notification_kbn = $formdata['notification_kbn'];
    $applicant_kbn = $formdata['applicant_kbn'];
    $guardian_name = $formdata['guardian_name'];
    $guardian_kana = $formdata['guardian_kana'];
    $guardian_email = $formdata['guardian_email'];
}

?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="APPLICATION">イベント申し込み</h2>
    </section>
    <!-- heading -->
    <?php if($deadline != null && $dayDate < $nowDate): ?>
        <div class="inner_l">
        <?php if (!empty($basic_error)) { ?><p class="error"> <?= $basic_error ?></p><? } ?>
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
    <?php elseif($aki_ticket <= 0): ?>
        <div class="inner_l">
        <?php if (!empty($basic_error)) { ?><p class="error"> <?= $basic_error ?></p><? } ?>
            <section id="form" class="event entry">
            <form method="POST" action="confirm.php" class="whitebox form_cont">
                <div class="inner_m">
                    <div class="form_btn">
                        <p class="list_label">定員数に達したため受付を終了致しました。</p>
                    </div>
                    <div class="form_btn">
                    <a href="index.php?id=<?= $eventId ?>" class="btn btn_gray">戻る</a>
                    </div>
                <div>
            </form>
            </section>
        </div>
    <?php else: ?>
        <div class="inner_l">
        <?php if (!empty($basic_error)) { ?><p class="error"> <?= $basic_error ?></p><? } ?>
            <section id="form" class="event entry">
                <ul id="flow">
                    <li class="active">入力</li>
                    <li>確認</li>
                    <li>完了</li>
                </ul>
                <form method="POST" action="/custom/app/Controllers/event/EventApplicationConfirmController.php" class="whitebox form_cont">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($eventId ?? "") ?>">
                    <input type="hidden" name="course_info_id" value="<?= htmlspecialchars($courseInfoId ?? "") ?>">
                    <input type="hidden" name="event_customfield_category_id" value="<?= htmlspecialchars($event_customfield_category_id) ?>">
                    <input type="hidden" id="guardian_kbn" name="guardian_kbn" value="<?= htmlspecialchars($guardian_kbn) ?>">
                    <input type="hidden" id="participation_fee" name="participation_fee" value="<?= htmlspecialchars($participation_fee) ?>">
                    <input type="hidden" id="name" name="name" value="<?= htmlspecialchars($name) ?>">
                    <input type="hidden" id="kana" name="kana" value="<?= htmlspecialchars($kana) ?>">
                    <input type="hidden" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
                    <input type="hidden" id="event_name" name="event_name" value="<?= htmlspecialchars($event_name) ?>">
                    <input type="hidden" id="aki_ticket" name="aki_ticket" value="<?= htmlspecialchars($aki_ticket) ?>">
                    <input type="hidden" id="price" name="price" value="<?= htmlspecialchars($price) ?>">
                    <div class="inner_m">
                        <ul class="list">
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
                                    <?php foreach($event['select_course'] as $no => $course): ?>
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
                                    <input type="text" id="ticket" name="ticket" value="<?= htmlspecialchars($ticket) ?>" class="num_txt" />
                                    <button type="button" class="num_plus">＋</button>
                                    (空き枠：<?= htmlspecialchars(number_format($aki_ticket)) ?>)
                                </div>
                            </li>
                            <li class="list_item05">
                                <p class="list_label">金額</p>
                                <p class="list_field" id="show_price"><?= htmlspecialchars(number_format($price)) ?>円</p>
                            </li>
                            <span class="error-msg" id="companion_mails-error">
                                <?php if (!empty($errors['companion_mails'])): ?>
                                    <?= htmlspecialchars($errors['companion_mails']); ?>
                                <?php endif; ?>
                            </span>
                            <span id="other_mails_tag" <?php if(empty($mailsArray)): ?>style="display: none"<?php endif; ?>>
                                <span class="error-msg" id="companion-mails-error">
                                    <?php if (!empty($errors['companion_mails'])): ?>
                                        <?= htmlspecialchars($errors['companion_mails']); ?>
                                    <?php endif; ?>
                                </span>
                                <li class="list_item07 long_item">
                                    <p class="list_label">複数チケット申し込みの場合お連れ様のメールアドレス</p>
                                    <div class="list_field f_txt list_col" id="input_emails">
                                        <?php if(!empty($mailsArray)): ?>
                                        <?php foreach($mailsArray as $key => $mail): ?>
                                            <p class="f_check">
                                                <input type="email" style="margin-right: 2rem" name="companion_mails[]" value="<?= htmlspecialchars($mail) ?>" placeholder="メールアドレス <?= $key+1 ?>";>
                                            </p>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            </span>
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
                                    <?php foreach($responce['cognitions'] as $cognition): ?>
                                        <p class="f_check">
                                            <label><input type="checkbox" name="trigger[]" value="<?= $cognition['id'] ?>" <?php echo in_array($cognition['id'], $triggersArray) ? 'checked' : ''; ?> /><?= htmlspecialchars($cognition['name']) ?></label>
                                            <?php if($cognition['id'] == 1 || $cognition['id'] == 2 || $cognition['id'] == 10): ?>
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
                            <li class="list_item06 req">
                                <p class="list_label">お支払方法</p>
                                <div class="list_field list_row">
                                <?php foreach($responce['paymentTypes'] as $paymentType): ?>
                                    <label class="f_radio">
                                        <input type="radio" name="pay_method" value="<?= $paymentType['id'] ?>" <?php if($paymentType['id'] == $payMethod): ?>checked<?php endif; ?>>
                                            <?= $paymentType['name'] ?>
                                    </label>
                                <?php endforeach; ?>
                                </div>
                            </li>
                            <span class="error-msg" id="notification_kbn-error">
                                <?php if (!empty($errors['notification_kbn'])): ?>
                                    <?= htmlspecialchars($errors['notification_kbn']); ?>
                                <?php endif; ?>
                            </span>
                            <li class="list_item08 req">
                                <p class="list_label">
                                    今後大阪大学からのメールによるイベントのご案内を希望されますか？
                                </p>
                                <div class="list_field list_row">
                                    <label class="f_radio"><input type="radio" name="notification_kbn" value="1" <?php if("1" == $notification_kbn): ?>checked<?php endif; ?> />希望する</label>
                                    <label class="f_radio"><input type="radio" name="notification_kbn" value="2" <?php if("2" == $notification_kbn): ?>checked<?php endif; ?> />希望しない</label>
                                </div>
                            </li>
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
                            <?php if(!empty($errors['passage'])): ?>
                                <?php foreach($errors['passage'] as $key => $message): ?>
                                    <?php if(!empty($message)): ?>
                                        <div class="error-msg"><?= htmlspecialchars($message); ?></div><br>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php echo $responce['passage'] ?>
                            <?php if($guardian_kbn): ?>
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
                                        <input type="checkbox" id="applicant_kbn" name="applicant_kbn" value="1" <?php if(!empty($applicant_kbn)): ?> checked <?php endif ?>><span style="font-weight: bold; color: #2D287F;">この申し込みは保護者の許可を得ています</span>
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
                                    <input type="text" style="margin-right: 2rem" name="guardian_name" value="<?= htmlspecialchars($guardian_name) ?>";>
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
                                    <input type="text" style="margin-right: 2rem" name="guardian_kana" value="<?= htmlspecialchars($guardian_kana) ?>";>
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
                                    <input type="email" style="margin-right: 2rem" name="guardian_email" value="<?= htmlspecialchars($guardian_email) ?>";>
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
    const participation_fee = $('#participation_fee').val();
    const akiTicketCount = $('#aki_ticket').val();
    // ブラウザバック対応
    $('input[name="ticket"]').on('change', function() {
        const price = participation_fee * $(this).val();
        var test = $('#price').text();
        $('#price').val(price);
        $('#show_price').text(price.toLocaleString() + "円");
    });

    document.getElementById('ticket').addEventListener('blur', function () {
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
            for (let i = currentEmailFields; i < ticketCount-1; i++) {    
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
            for (let i = 0; currentEmailFields-ticketCount >= i; i++) {
                emailContainer.removeChild(emailContainer.lastChild);
            }
            if(ticketCount == 1){
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
                const price = participation_fee * $(this).val();
                $('#price').val(price);
                $('#show_price').text(price.toLocaleString() + "円");
            });
            createInputMail();
            return total_numner;
        }
        //ロード時
        $(window).on("load", function() {
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
            if (number > 0) {
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