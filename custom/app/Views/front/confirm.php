<?php
require_once('/var/www/html/moodle/custom/app/Controllers/EventCustomFieldController.php');
require_once('/var/www/html/moodle/custom/app/Controllers/event/event_application_confirm_controller.php');

$eventCustomFieldModel = new eventCustomFieldModel();
$eventCustomFieldList = $eventCustomFieldModel->getCustomFieldById($event_customfield_category_id);
$cognitionModel = new cognitionModel();
$cognitions = $cognitionModel->getCognitionByIds($triggers);
$paymentTypeModel = new paymentTypeModel();
$paymentType = $paymentTypeModel->getPaymentTypesById($payMethod);

// mdl_cognition

$passages = '';
$hiddens = '';
foreach ($eventCustomFieldList as $eventCustomField) {
    $tag_name = $customfield_type_list[$eventCustomField['field_type']] . '_' . $eventCustomField['id'] . '_' . $eventCustomField['field_type'];
    
    if ($eventCustomField['field_type'] == 3) {
        $passages .= '<p><strong>' . $eventCustomField['field_name'] . '</strong>';
        $input_value = optional_param_array($tag_name, [], PARAM_INT);
        
        $options = explode(",", $eventCustomField['selection']);
        foreach ($options as $i => $option) {
            if(in_array($i+1, $input_value)) {
                $passages .= '<br>' . $option;
            }
        }
        $passages .= '</p>';
        $inputValueString = implode(',', $input_value);
        $hiddens .= '<input type="hidden" name="' . $tag_name . '" value="' . $inputValueString . '">';
    } elseif ($eventCustomField['field_type'] == 4) {
        $passages .= '<p><strong>' . $eventCustomField['field_name'] . '</strong>';
        $input_value = optional_param($tag_name, 0, PARAM_INT);
        $options = explode(",", $eventCustomField['selection']);
        foreach ($options as $i => $option) {
            if($i+1 == $input_value) {
                $passages .= '<br>' . $option;
            }
        }
        $passages .= '</p>';
        $hiddens .= '<input type="hidden" name="' . $tag_name . '" value="' . $input_value . '">';
    } elseif ($eventCustomField['field_type'] == 5) {
        $input_value = optional_param($tag_name, '', PARAM_TEXT);
        $value = str_replace("-", "/", $input_value);
        $passages .= '<p><strong>' . $eventCustomField['field_name'] . '</strong><br>' . $value . '</p>';
        $hiddens .= '<input type="hidden" name="' . $tag_name . '" value="' . $input_value . '">';
    } else {
        $input_value = optional_param($tag_name, '', PARAM_TEXT);
        $passages .= '<p><strong>' . $eventCustomField['field_name'] . '</strong><br>' . $input_value . '</p>';
        $hiddens .= '<input type="hidden" name="' . $tag_name . '" value="' . $input_value . '">';
    }
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
        <p><strong>名前</strong> <br><?= htmlspecialchars($name); ?></p>
        <p><strong>フリガナ</strong> <br><?= htmlspecialchars($kana); ?></p>
        <p><strong>メールアドレス</strong><br> <?= htmlspecialchars($email); ?></p>
        <p><strong>チケット名称</strong><br> <?= htmlspecialchars($eventName); ?></p>
        <p><strong>チケット枚数</strong><br> <?= htmlspecialchars($ticket . '枚'); ?></p>
        <p><strong>金額</strong><br> <?= htmlspecialchars(number_format($price) . '円'); ?></p>
        <p><strong>本イベントのことはどうやってお知りになりましたか。（複数選択可）</strong><br>
            <?php
            if (is_array($triggers)) {
                foreach ($cognitions as $cognition) {
                    if(in_array($cognition['id'], $triggers))
                    htmlspecialchars(htmlspecialchars($cognition["name"]), ENT_QUOTES, 'UTF-8') . "<br>";
                }
            }
            ?>
        <p><strong>その他</strong> <br><?= htmlspecialchars($triggerOther); ?></p>
        <p><strong>支払方法</strong> <br><?= htmlspecialchars($paymentType['name']) ?? ''; ?></p>
        <p><strong>今後、大阪大学からメールによるイベントのご案内を希望されますか</strong><br><?= htmlspecialchars($notification_kbn) == 1 ? "はい" : "いいえ"; ?></p>
        <?php if (count($companionMails) > 0): ?>
            <p><strong>複数チケット申し込み者の場合、お連れ様のメールアドレス</strong><br>
                <?php if (is_array($companionMails)): ?>
                    <?php foreach ($companionMails as $companionMail): ?>
                        <?= htmlspecialchars($companionMail, ENT_QUOTES, 'UTF-8') . "<br>" ?>
                    <?php endforeach ?>
                <?php endif ?>
            </p>
        <?php endif ?>
        <?php if(!empty($applicant_kbn)): ?>
            <p><strong>この申し込みは保護者の許可を得ています</strong><br>
                <?php if(!empty($applicant_kbn)): ?>許可済<?php else: ?>不許可<?php endif; ?>
            </p>
        <?php endif ?>
        <p><strong>備考欄</strong><br><?= htmlspecialchars($note); ?></p>
        <?php echo $passages ?>
        <?php if($guardian_kbn == 1): ?>
            <p><strong>保護者名</strong> <br><?= htmlspecialchars($guardian_name); ?></p>
            <p><strong>保護者連絡先メールアドレス</strong> <br><?= htmlspecialchars($guardian_email); ?></p>
        <?php endif ?>
    </div>
    <form action="/custom/app/Controllers/event/event_application_insert_controller.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" id="event_id" name="event_id" value="<?= htmlspecialchars($eventId) ?>">
        <input type="hidden" name="name" value="<?= htmlspecialchars($name); ?>">
        <input type="hidden" name="kana" value="<?= htmlspecialchars($kana); ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email); ?>">
        <input type="hidden" name="ticket" value="<?= htmlspecialchars($ticket); ?>">
        <input type="hidden" name="price" value="<?= htmlspecialchars($price); ?>">
        <input type="hidden" name="triggers" value="<?= htmlspecialchars($triggersString); ?>">
        <input type="hidden" name="trigger_other" value="<?= htmlspecialchars($triggerOther); ?>">
        <input type="hidden" name="pay_method" value="<?= htmlspecialchars($payMethod); ?>">
        <input type="hidden" name="notification_kbn" value="<?= htmlspecialchars($notification_kbn); ?>">
        <input type="hidden" name="companion_mails" value="<?= htmlspecialchars($companionMailsString); ?>">
        <input type="hidden" name="note" value="<?= htmlspecialchars($note); ?>">
        <input type="hidden" name="event_customfield_id" value="<?= htmlspecialchars($event_customfield_id); ?>">
        <?php if($guardian_kbn == 1): ?>
        <input type="hidden" name="applicant_kbn" value="<?= htmlspecialchars($applicant_kbn); ?>">
        <input type="hidden" name="guardian_name" value="<?= htmlspecialchars($guardian_name); ?>">
        <input type="hidden" name="guardian_email" value="<?= htmlspecialchars($guardian_email); ?>">
        <?php endif ?>
        <input type="hidden" name="event_customfield_category_id" value="<?= htmlspecialchars($event_customfield_category_id); ?>">
        <button type="submit" name="action" value="register">登録する</button>
        <button type="submit" name="action" value="edit">修正する</button>
        <?php echo $hiddens ?>
    </form>
</div>
</body>
<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>

</html>
<script>
window.history.pushState(null, null, window.location.href);
window.onpopstate = function() {
    window.history.pushState(null, null, window.location.href);
    const event_id = document.getElementById('event_id');
    window.location.href = './custom/app/Views/front/event_application.php?id=' + event_id;  // 入力画面にリダイレクト
};
</script>