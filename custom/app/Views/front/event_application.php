<?php
require_once('/var/www/html/moodle/custom/helpers/form_helpers.php');
require_once('/var/www/html/moodle/custom/app/Controllers/event/event_application_controller.php');
$eventId = isset($_GET['id']) ? $_GET['id'] : null;
$courseInfoId = isset($_GET['course_info_id']) ? $_GET['course_info_id'] : null;
if (isset($SESSION->formdata) && is_null($eventId)) {
    $eventId = isset($SESSION->formdata) ? $SESSION->formdata['id'] : null;
    $courseInfoId = isset($SESSION->formdata) ? $SESSION->formdata['course_info_id'] : null;
}
$eventApplicationController = new EventApplicationController();
$responce = $eventApplicationController->getEvenApplication($eventId, $courseInfoId);

$aki_ticket = $responce['event']['capacity'] - $responce['sum_ticket_count'];

$name = "";
$kana = "";
$email = "";
$guardian_kbn = "";
$guardian_name = "";
$guardian_email = "";
$event_customfield_category_id = $responce['event']['event_customfield_category_id'];
$participation_fee = $responce['event']['participation_fee'];
$price = $participation_fee;
$event_name = $responce['event']['name'];
$triggerOther = "";
$payMethod = null;
$ticket = 1;
$request_mail_kbn = null;
$note = "";
$triggersArray = [];
$mailsArray = [];
$deadline = $responce['event']['deadline'];
// 値をDateTimeオブジェクトに変換
$day = new DateTime($deadline);
$dayDate = $day->format('Y-m-d');
// 現在の日付
$now = new DateTime();
$nowDate = $now->format('Y-m-d');
if (isset($SESSION->formdata)) {
    $formdata = $SESSION->formdata;
    $payMethod = $formdata['pay_method'];
    $ticket = $formdata['ticket'];
    $triggerOther = $formdata['trigger_other'];
    $note = $formdata['note'];
    $triggers = $formdata['triggers'];
    $triggersArray = explode(',', $triggers); // 配列に変換
    $companion_mails = $formdata['companion_mails'];
    $mailsArray = explode(',', $companion_mails); // 配列に変換
    $request_mail_kbn = $formdata['request_mail_kbn'];
}
if (isloggedin() && isset($_SESSION['USER'])) {
    global $DB, $USER;

    // 必要な情報を取得
    $userData = $DB->get_record('user', ['id' => $USER->id], 'lastname_kana, firstname_kana, guardian_kbn
    , guardian_lastname, guardian_firstname, guardian_lastname_kana, guardian_firstname_kana, guardian_email');
    $lastname_kana = $userData->lastname_kana ?? '';
    $firstname_kana = $userData->firstname_kana ?? '';
    $guardian_lastname = $userData->guardian_lastname ?? '';
    $guardian_firstname = $userData->guardian_firstname ?? '';
    $guardian_lastname_kana = $userData->guardian_lastname_kana ?? '';
    $guardian_firstname_kana = $userData->guardian_firstname_kana ?? '';
    $name = $_SESSION['USER']->lastname . $_SESSION['USER']->firstname;
    $kana = $lastname_kana . $firstname_kana;
    $guardian_name = $guardian_lastname . $guardian_firstname;
    $email = $_SESSION['USER']->email ?? "";
    $guardian_kbn = $userData->guardian_kbn ?? "";
    $guardian_email = $userData->guardian_email ?? "";
}

// セッションからエラーメッセージを取得
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']); // 一度表示したら削除
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お申込みフォーム</title>
</head>
<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" href="/custom/public/css/event.css" type="text/css">
<div class="container">
    <h2>お申込みフォーム</h2>
    <?php if($deadline != null && $dayDate < $nowDate): ?>
        <form>
            <div>
                <label class="label_name" for="name"><span id="warning">申し込みの受付を終了致しました。</span></label>
            </div>
        </form>
    <?php elseif($aki_ticket <= 0): ?>
        <form>
            <div>
                <label class="label_name" for="name"><span id="warning">定員数に達したため受付を終了致しました。</span></label>
            </div>
        </form>
    <?php else: ?>
        <form id="form" action="/custom/app/Views/front/confirm.php" method="post">
            <?php if (!isloggedin()): ?>
                <div>
                    <label class="label_name" for="name"><span id="warning" class="error-msg">ログインしてください。</span></label>
                </div>
            <?php elseif(!empty($errors['message_error'])): ?>
                <div>
                    <label class="label_name" for="name"><span id="warning" class="error-msg"><?= htmlspecialchars($errors['message_error']); ?></span></label>
                </div>
            <?php endif; ?>
            <input type="hidden" name="event_id" value="<?= htmlspecialchars($eventId) ?>">
            <input type="hidden" name="event_customfield_category_id" value="<?= htmlspecialchars($event_customfield_category_id) ?>">
            <input type="hidden" id="guardian_kbn" name="guardian_kbn" value="<?= htmlspecialchars($guardian_kbn) ?>">
            <input type="hidden" id="participation_fee" name="participation_fee" value="<?= htmlspecialchars($participation_fee) ?>">
            <input type="hidden" id="hidden_price" name="hidden_price" value="<?= htmlspecialchars($participation_fee) ?>">
            <label class="label_name" for="name">名前</label>
            <input type="text" id="name" readonly name="name" value="<?= htmlspecialchars($name) ?>" required>
            <label class="label_name" for="kana">フリガナ</label>
            <input type="text" id="kana" readonly name="kana" value="<?= htmlspecialchars($kana) ?>" required>
            <label class="label_name" for="email">メールアドレス</label>
            <input type="email" id="email" readonly name="email" value="<?= htmlspecialchars($email) ?>" required>
            <label class="label_name" for="event_name">チケット名称</label>
            <input type="event_name" name="event_name" value="<?= htmlspecialchars($event_name) ?>">
            <label class="label_name" for="ticket">チケット枚数(空き枠：<?= htmlspecialchars($aki_ticket) ?>)</label>
            <input type="hidden" id="aki_ticket" value="<?= htmlspecialchars($aki_ticket) ?>">
            <input type="number" id="ticket" name="ticket" min="1" max="<?= htmlspecialchars($aki_ticket) ?>" value="<?= htmlspecialchars(isSetValue($ticket, $old_input['ticket'] ?? '')) ?>">
            <div id="warning" class="error-msg" style="display: none;">0以上、空き枠数以下の数字を入力してください。</div>
            <span class="error-msg" id="ticket-error"><?php if (!empty($errors['ticket'])): ?><?= htmlspecialchars($errors['ticket']); ?><?php endif; ?></span>
            <label class="label_name" for="price">金額</label>
            <input type="text" name="price" readonly value="<?php number_format($participation_fee) ?>">
            <label class="label_name" for="trigger">本イベントのことはどうやってお知りになりましたか</label>
            <div class="error-msg" id="trigger-error"><?php if (!empty($errors['trigger'])): ?><?= htmlspecialchars($errors['trigger']); ?><?php endif; ?></div>
            <div class="checkbox-group">
                <?php foreach($responce['cognitions'] as $cognition): ?>
                    <label>
                        <input type="checkbox" name="trigger[]" value="<?= $cognition['id'] ?>" <?php echo in_array($cognition['id'], $triggersArray) ? 'checked' : ''; ?>><span><?= htmlspecialchars($cognition['name']) ?></span>
                    </label><br>
                <?php endforeach; ?>
            </div>
            <label class="label_name" for="trigger_other">その他</label>
            <textarea row="20px" name="trigger_other"><?= htmlspecialchars($triggerOther) ?></textarea>
            <div class="error-msg"><?php if (!empty($errors['trigger_other'])): ?><?= htmlspecialchars($errors['trigger_other']); ?><?php endif; ?></div>
            <label class="label_name" style="width: 100%" for="pay_method">支払方法</label>
            <span class="error-msg" id="pay_method-error"><?php if (!empty($errors['pay_method'])): ?><?= htmlspecialchars($errors['pay_method']); ?><?php endif; ?></span>
            <div class="radio-group">
                <?php foreach($responce['paymentTypes'] as $paymentType): ?>
                    <label>
                        <input type="radio" name="pay_method" value="<?= $paymentType['id'] ?>" <?php if($paymentType['id'] == $payMethod): ?>checked<?php endif; ?>><?= $paymentType['name'] ?>
                    </label><br>
                <?php endforeach ?>
            </div>
            <label class="label_name" style="width: 100%" for="notification_kbn">今後大阪大学からメールによるイベントのご案内を希望されますか</label>
            <div class="error-msg"><?php if (!empty($errors['notification_kbn'])): ?><?= htmlspecialchars($errors['notification_kbn']); ?><?php endif; ?></div>
            <div class="radio-group">
                <label>
                    <input type="radio" checked name="notification_kbn" value="1">はい
                </label><br>
                <label>
                    <input type="radio" <?php if(1 == $request_mail_kbn): ?>checked<?php endif; ?> name="notification_kbn" value="0">いいえ
                </label><br>
            </div>
            <span id="other_mails_tag">
                <label class="label_name" for="other_mails">複数チケット申し込み者の場合、お連れ様のメールアドレス</label>
                <div id="input_emails">
                    <span class="error-msg" id="companion-mails-error"></span>
                    <?php if(empty($mailsArray)): ?>
                    <?php foreach($mailsArray as $key => $mail): ?>
                        <input type="email" style="margin-right: 2rem" name="companion_mails[]" value="<?= htmlspecialchars($mail) ?>" placeholder="メールアドレス <?= $key+1 ?>";>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </span>
            <label id="note" class="label_name" for="note">備考欄</label>
            <textarea row="20px" name="note"><?= htmlspecialchars($note) ?></textarea>
            <?php if(!empty($errors['passage'])): ?>
                <?php foreach($errors['passage'] as $key => $message): ?>
                    <?php if(!empty($message)): ?>
                        <div class="error-msg"><?= htmlspecialchars($message); ?></div><br>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php echo $responce['passage'] ?><br>
            <?php if($guardian_kbn): ?>
            <div class="error-msg"><?php if (!empty($errors['applicant_kbn'])): ?><?= htmlspecialchars($errors['applicant_kbn']); ?><?php endif; ?></div>
            <div class="radio-group">
                <label>
                    <input type="checkbox" id="applicant_kbn" name="applicant_kbn" value="1"><span style="font-weight: bold; color: #2D287F;">この申し込みは保護者の許可を得ています</span>
                </label><br>
            </div>
            <label class="label_name" for="name">保護者名</label>
            <div class="error-msg" id="guardian_name-error"><?php if (!empty($errors['guardian_name'])): ?><?= htmlspecialchars($errors['guardian_name']); ?><?php endif; ?></div>
            <input type="text" id="guardian_name" name="guardian_name" value="<?= htmlspecialchars($guardian_name) ?>" required>
            <label class="label_name" for="email">保護者連絡先メールアドレス</label>
            <div class="error-msg" id="guardian_email-error"><?php if (!empty($errors['guardian_email'])): ?><?= htmlspecialchars($errors['guardian_email']); ?><?php endif; ?></div>
            <input type="email" id="guardian_email" name="guardian_email" value="<?= htmlspecialchars($guardian_email) ?>" required>
            <?php endif; ?>
            <?php if (isloggedin()): ?>
                <button id="entry_btn" type="submit">確認画面へ</button>
            <?php else: ?>
                <div>
                    <label class="label_name" for="name"><span id="warning" class="error-msg">ログインしてください。</span></label>
                </div>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</div>
</body>
<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>

</html>
<script>
    const participation_fee = $('#participation_fee').val();
    // ブラウザバック対応
    $(window).on('pageshow', function() {
        if ($('#applicant_check').length > 0) {
            $('#applicant_check').prop('checked', false);
            $('#entry_btn').css('background-color', '#5b5b5b');
            $('#entry_btn').prop('disabled', true);
        } else {
            $('#entry_btn').css('background-color', '#2D287F');
            $('#entry_btn').prop('disabled', false);
        }
        const price = participation_fee * $('input[name="ticket"]').val();
        $('input[name="price"]').val(price.toLocaleString());
        $('#hidden_price').val(price);
    });
    $('input[name="ticket"]').on('change', function() {
        const price = participation_fee * $(this).val();
        $('input[name="price"]').val(price.toLocaleString());
        $('#hidden_price').val(price);
    });
    $('#add_email').on('click', function() {
        event.preventDefault();
        const elem = '<input type="mail" name="companion_mails[]" value="">';
        $("#note").before(elem);
    });
    $('#applicant_check').change(function() {
        if ($(this).prop('checked')) {
            $('#entry_btn').css('background-color', '#2D287F');
            $('#entry_btn').prop('disabled', false);
        } else {
            $('#entry_btn').css('background-color', '#5b5b5b');
            $('#entry_btn').prop('disabled', true);
        }
    });

    document.getElementById('ticket').addEventListener('blur', function () {
        const ticketInput = document.getElementById('ticket'); // チケット枚数の入力欄
        const emailContainer = document.getElementById('input_emails'); // メール入力欄を追加するコンテナ
        const warningMessage = document.getElementById('warning'); // 警告メッセージ
        const other_mails_tag = document.getElementById('other_mails_tag');

        let ticketCount = parseInt(ticketInput.value); // 入力されたチケット枚数
        var maxValue = $('input[type="number"]').attr('max');
        if (isNaN(ticketCount) || ticketCount < 1) {
            // 0以下の数字が入力された場合は警告を表示
            warningMessage.style.display = 'block';
            return;
        } else if(ticketCount > maxValue) {
            // 空き数以上の数字が入力された場合は警告を表示
            warningMessage.style.display = 'block';
            return;
        }
        

        // 警告を非表示
        warningMessage.style.display = 'none';

        // 現在のメール入力欄の数を取得
        const currentEmailFields = emailContainer.querySelectorAll('input[type="email"]').length;

        if (ticketCount > currentEmailFields) {
            other_mails_tag.style.display = 'block';

            // チケット数が増えた場合、追加
            for (let i = currentEmailFields; i < ticketCount-1; i++) {
                const newInput = document.createElement('input');
                newInput.type = 'email';
                newInput.name = 'companion_mails[]';
                newInput.style.marginRight = '2rem';
                newInput.placeholder = `メールアドレス ${i + 1}`;
                emailContainer.appendChild(newInput);
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
    });

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form');
        const ticketInput = document.getElementById('ticket');
        const triggers = document.getElementsByName('trigger[]');
        const paymentMethods = document.getElementsByName('pay_method');
        const submitButton = document.getElementById('entry_btn');
        const emailContainer = document.getElementById('input_emails'); // メール入力欄を追加するコンテナ
        const other_mails_tag = document.getElementById('other_mails_tag');
        const guardian_kbn = document.getElementById('guardian_kbn');

        const ticketError = document.getElementById('ticket-error');
        const triggerError = document.getElementById('trigger-error');
        const payMethodError = document.getElementById('pay_method-error');
        const companionMailsError = document.getElementById('companion-mails-error');
        
        other_mails_tag.style.display = 'none';
    });
</script>