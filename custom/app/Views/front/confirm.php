<?php
require_once('/var/www/html/moodle/custom/app/Controllers/EventCustomFieldController.php');

unset($SESSION->formdata); // セッションをクリア

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $eventId = htmlspecialchars(required_param('event_id', PARAM_INT), ENT_QUOTES, 'UTF-8');
    $name = htmlspecialchars(required_param('name', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
    $kana = htmlspecialchars(required_param('kana', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars(required_param('email', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
    $eventName = htmlspecialchars(required_param('event_name', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
    $ticket = htmlspecialchars(required_param('ticket', PARAM_INT), ENT_QUOTES, 'UTF-8');
    $price =  required_param('price', PARAM_INT);
    $triggerOther = htmlspecialchars(required_param('trigger_othier', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
    $payMethod = htmlspecialchars(required_param('pay_method', PARAM_INT), ENT_QUOTES, 'UTF-8');
    $request_mail_kbn = htmlspecialchars(required_param('request_mail_kbn', PARAM_INT), ENT_QUOTES, 'UTF-8');
    $note = htmlspecialchars(required_param('note', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
    $companionMails = optional_param_array('companion_mails', [], PARAM_EMAIL);
    if(!empty($companionMails)) {
        $companionMailsString = implode(',', $companionMails);
    } else {
        $companionMailsString = '';
    }
    $triggers = optional_param_array('trigger', [], PARAM_INT);
    $triggersString = implode(',', $triggers);
    $applicant_kbn = optional_param('applicant_kbn', '', PARAM_INT);
    $event_customfield_id = optional_param('event_customfield_id', '', PARAM_TEXT);
    $guardian_kbn = optional_param('guardian_kbn', 0, PARAM_INT);
    $guardian_name = optional_param('guardian_name', '', PARAM_TEXT);
    $guardian_kana = optional_param('guardian_kana', '', PARAM_TEXT);
    $guardian_email = optional_param('guardian_email', '', PARAM_TEXT);
} else {
    header("Location: register.php");
    exit;
}

$eventCustomFieldModel = new eventCustomFieldModel();
$eventCustomFieldList = $eventCustomFieldModel->getEventsCustomFieldByEventId($eventId);
$cognitionModel = new cognitionModel();
$cognitions = $cognitionModel->getCognitionByIds($triggers);
$paymentTypeModel = new paymentTypeModel();
$paymentType = $paymentTypeModel->getPaymentTypesById($payMethod);

// mdl_cognition

$passages = '';
foreach ($eventCustomFieldList as $eventCustomField) {
    $passages .= '<p><strong>' . $eventCustomField['field_name'] . '</strong><br>' . $_POST[$eventCustomField['name']];
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>確認画面</title>
</head>
<!-- スタイルは完全仮の状態なのでとりえず直書きする 後で個別ファイルに記述する -->
<style>
    h2 {
        padding-left: 3rem;
        margin-top: 80px;
        color: #2D287F;
    }

    p {
        margin-bottom: 1rem;
    }

    .passage {
        padding: 1rem 3rem 0rem 3rem;
        font-size: 15px;
        color: 272727;
    }

    .confirm-details {
        padding: 3rem;
        padding-top: 1rem;
    }

    .confirm-details p {
        margin-top: 3.5vh;
    }

    strong {
        color: #2D287F;
    }

    form {
        padding-left: 3rem;
    }
</style>
<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<div class="container">
    <h2>確認画面</h2>
    <p class="passage">以下の内容で登録しますか？</p>

    <div class="confirm-details">
        <p><strong>名前</strong> <br><?php echo $name; ?></p>
        <p><strong>フリガナ</strong> <br><?php echo $kana; ?></p>
        <p><strong>メールアドレス</strong><br> <?php echo $email; ?></p>
        <p><strong>チケット名称</strong><br> <?php echo $eventName; ?></p>
        <p><strong>チケット枚数</strong><br> <?php echo $ticket . '枚'; ?></p>
        <p><strong>金額</strong><br> <?php echo number_format($price) . '円'; ?></p>
        <p><strong>本イベントのことはどうやってお知りになりましたか。（複数選択可）</strong><br>
            <?php
            if (is_array($triggers)) {
                foreach ($cognitions as $cognition) {
                    if(in_array($cognition['id'], $triggers))
                    echo htmlspecialchars($cognition["name"], ENT_QUOTES, 'UTF-8') . "<br>";
                }
            }
            ?>
        <p><strong>その他</strong> <br><?php echo $triggerOther; ?></p>
        <p><strong>支払方法</strong> <br><?php echo $paymentType['name'] ?? ''; ?></p>
        <p><strong>今後、大阪大学からメールによるイベントのご案内を希望されますか</strong><br><?php echo $request_mail_kbn == 1 ? "はい" : "いいえ"; ?></p>
        <?php if (count($companionMails) > 0): ?>
            <p><strong>複数チケット申し込み者の場合、お連れ様のメールアドレス</strong><br>
                <?php
                if (is_array($companionMails)) {
                    foreach ($companionMails as $companionMail) {
                        echo htmlspecialchars($companionMail, ENT_QUOTES, 'UTF-8') . "<br>";
                    }
                }
                ?>
            </p>
        <?php endif ?>
        <?php if(!empty($applicant_kbn)): ?>
            <p><strong>この申し込みは保護者の許可を得ています</strong><br>
                <?php if(!empty($applicant_kbn)): ?>許可済<?php else: ?>不許可<?php endif; ?>
            </p>
        <?php endif ?>
        <p><strong>備考欄</strong><br><?php echo $note; ?></p>
        <?php echo $passages ?>
    </div>
    <form action="/custom/app/Controllers/event/EventApplicationInsertController.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="action" value="insert">
        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($eventId) ?>">
        <input type="hidden" name="name" value="<?php echo htmlspecialchars($name); ?>">
        <input type="hidden" name="kana" value="<?php echo htmlspecialchars($kana); ?>">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <input type="hidden" name="ticket" value="<?php echo htmlspecialchars($ticket); ?>">
        <input type="hidden" name="price" value="<?php echo htmlspecialchars($price); ?>">
        <input type="hidden" name="triggers" value="<?php echo htmlspecialchars($triggersString); ?>">
        <input type="hidden" name="trigger_other" value="<?php echo htmlspecialchars($triggerOther); ?>">
        <input type="hidden" name="pay_method" value="<?php echo htmlspecialchars($payMethod); ?>">
        <input type="hidden" name="applicant_kbn" value="<?php echo htmlspecialchars($applicant_kbn); ?>">
        <input type="hidden" name="request_mail_kbn" value="<?php echo htmlspecialchars($request_mail_kbn); ?>">
        <input type="hidden" name="companion_mails" value="<?php echo htmlspecialchars($companionMailsString); ?>">
        <input type="hidden" name="note" value="<?php echo htmlspecialchars($note); ?>">
        <input type="hidden" name="event_customfield_id" value="<?php echo htmlspecialchars($event_customfield_id); ?>">
        <input type="hidden" name="guardian_kbn" value="<?php echo htmlspecialchars($guardian_kbn); ?>">
        <input type="hidden" name="guardian_name" value="<?php echo htmlspecialchars($guardian_name); ?>">
        <input type="hidden" name="guardian_kana" value="<?php echo htmlspecialchars($guardian_kana); ?>">
        <input type="hidden" name="guardian_email" value="<?php echo htmlspecialchars($guardian_email); ?>">
        <button type="submit" name="action" value="register">登録する</button>
        <button type="submit" name="action" value="edit">修正する</button>
    </form>
</div>
</body>
<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>

</html>