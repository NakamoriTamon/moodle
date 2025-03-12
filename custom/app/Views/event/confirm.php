<?php
include('/var/www/html/moodle/custom/app/Views/common/header.php');
$eventId = null;
$courseInfoId = null;
$name = "";
$kana = "";
$event_name = "";
$email = "";
$price = "";
$ticket = "";
$trigger_other = "";
$pay_method  = null;
$notification_kbn = null;
$now_notification = null;
$triggers = [];
$triggersString = "";
$note = "";
$companionMails = [];
$companionMailsString = "";
$applicant_kbn = null;
$guardian_kbn = null;
$guardian_name = "";
$guardian_email = "";
$guardian_phone = "";
$event_customfield_category_id = null;
$cognitions = [];
$paymentType = null;


if (isset($SESSION->formdata)) {
    $formdata = $SESSION->formdata;
    $eventId = $formdata['id'];
    $courseInfoId = $formdata['course_info_id'];
    $name = $formdata['name'];
    $kana = $formdata['kana'];
    $event_name = $formdata['event_name'];
    $email = $formdata['email'];
    $ticket = $formdata['ticket'];
    $price = $formdata['price'];
    $pay_method = $formdata['pay_method'];
    $notification_kbn = $formdata['notification_kbn'];
    $now_notification = $formdata['now_notification'];
    $trigger_other = $formdata['trigger_other'];
    $triggers = $formdata['triggers'];
    $triggersString = $formdata['triggersString'];
    $note = $formdata['note'];
    $companion_mails = $formdata['companion_mails'];
    $companionMailsString = $formdata['companionMailsString'];
    $applicant_kbn = $formdata['applicant_kbn'];
    $guardian_kbn = $formdata['guardian_kbn'];
    $guardian_name = $formdata['guardian_name'];
    $guardian_email = $formdata['guardian_email'];
    $guardian_phone = $formdata['guardian_phone'];
    $event_customfield_category_id = $formdata['event_customfield_category_id'];
    $cognitions = $formdata['cognitions'];
    $select_courses = $formdata['select_courses'];
    $paymentType = $formdata['paymentType'];
    $passages = $formdata['passages'];
    $hiddens = $formdata['hiddens'];
}
$url = "apply.php?id=" . $eventId;
if(!is_null($courseInfoId)) {
    $url .= "&course_info_id=" . $courseInfoId;
}
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="CONFIRM APPLICATION DETAILS">申し込み内容確認</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="form" class="event confirm">
            <ul id="flow">
                <li>入力</li>
                <li class="active">確認</li>
                <li>完了</li>
            </ul>
            <!-- 一旦申し込んだイベントリストへ飛ばす -->
            <form action="/custom/app/Controllers/event/event_application_insert_controller.php" method="post" enctype="multipart/form-data" class="whitebox form_cont">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" id="event_id" name="event_id" value="<?= htmlspecialchars($eventId) ?>">
                <input type="hidden" name="course_info_id" value="<?= htmlspecialchars($courseInfoId ?? "") ?>">
                <input type="hidden" name="name" value="<?= htmlspecialchars($name); ?>">
                <input type="hidden" name="kana" value="<?= htmlspecialchars($kana); ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email); ?>">
                <input type="hidden" name="ticket" value="<?= htmlspecialchars($ticket); ?>">
                <input type="hidden" name="price" value="<?= htmlspecialchars($price); ?>">
                <input type="hidden" name="triggers" value="<?= htmlspecialchars($triggersString); ?>">
                <input type="hidden" name="trigger_other" value="<?= htmlspecialchars($trigger_other); ?>">
                <input type="hidden" name="pay_method" value="<?= htmlspecialchars($pay_method); ?>">
                <input type="hidden" name="notification_kbn" value="<?= htmlspecialchars($notification_kbn); ?>">
                <input type="hidden" name="now_notification" value="<?= htmlspecialchars($now_notification); ?>">
                <input type="hidden" name="companion_mails" value="<?= htmlspecialchars($companionMailsString); ?>">
                <input type="hidden" name="note" value="<?= htmlspecialchars($note); ?>">
                <?php if($guardian_kbn == 1): ?>
                <input type="hidden" name="applicant_kbn" value="<?= htmlspecialchars($applicant_kbn); ?>">
                <input type="hidden" name="guardian_name" value="<?= htmlspecialchars($guardian_name); ?>">
                <input type="hidden" name="guardian_email" value="<?= htmlspecialchars($guardian_email); ?>">
                <input type="hidden" name="guardian_phone" value="<?= htmlspecialchars($guardian_phone); ?>">
                <?php endif ?>
                <input type="hidden" name="event_customfield_category_id" value="<?= htmlspecialchars($event_customfield_category_id); ?>">
                <?php echo $hiddens ?>
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
                            <p class="list_field">
                                <?= $event_name ?>
                            </p>
                        </li>
                        <li class="long_item">
                            <p class="list_label">開催日</p>
                            <div class="list_field f_txt list_col">
                            <?php foreach($select_courses as $no => $course): ?>
                                <p class="list_field"><?= htmlspecialchars($no) ?>回目：<?= htmlspecialchars((new DateTime($course['course_date']))->format('Y年m月d日')); ?></p><br />
                            <?php endforeach; ?>
                            </div>
                        </li>
                        <li class="list_item04 req">
                            <p class="list_label">枚数選択</p>
                            <p class="list_field"><?= htmlspecialchars($ticket) ?>枚</p>
                        </li>
                        <?php if(!empty($price)): ?>
                        <li class="list_item05">
                            <p class="list_label">金額</p>
                            <p class="list_field f_txt"><?= htmlspecialchars($price) ?>円</p>
                        </li>
                            <li class="list_item06 req">
                                <p class="list_label">お支払方法</p>
                                <p class="list_field"><?= htmlspecialchars($paymentType['name']) ?></p>
                            </li>
                        <?php endif ?>
                        <?php if(!empty($companion_mails)): ?>
                        <li class="list_item07 long_item">
                            <p class="list_label">複数チケット申し込みの場合お連れ様のメールアドレス</p>
                            <div class="list_field f_txt list_col">
                                <?php foreach($companion_mails as $companion_mail): ?>
                                <p class="list_field"><?= htmlspecialchars($companion_mail) ?></p>
                                <?php endforeach ?>
                            </div>
                        </li>
                        <?php endif; ?>
                        <?php if ("1" != $now_notification): ?>
                        <li class="list_item08 req">
                            <p class="list_label">
                                今後大阪大学からのメールによるイベントのご案内を希望されますか？
                            </p>
                            <p class="list_field"><?= $notification_kbn == 1 ? "希望する" : "希望しない"; ?></p>
                        </li>
                        <?php endif; ?>
                        <li class="list_item09 long_item">
                            <p class="list_label">
                                本イベントはどのようにお知りになりましたか？<span>※複数選択可</span>
                            </p>
                            <p class="list_field">
                                <?php if (is_array($triggers)): ?>
                                    <?php foreach ($cognitions as $cognition): ?>
                                        <?php if(in_array($cognition['id'], $triggers)): ?>
                                            <?= $cognition["name"] ?>
                                        <?php endif ?>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </p>
                        </li>
                        <li class="list_item10 long_item">
                            <p class="list_label">その他</p>
                            <p class="list_field">
                                <?= nl2br($trigger_other) ?>
                            </p>
                        </li>
                        <li class="list_item10 long_item">
                            <p class="list_label">備考欄</p>
                            <p class="list_field">
                                <?= nl2br($note) ?>
                            </p>
                        </li>
                        <?php echo $passages ?>
                        <?php if($guardian_kbn == 1): ?>
                            <li>
                                <p class="list_label">保護者名</p>
                                <p class="list_field"><?= htmlspecialchars($guardian_name) ?></p>
                            </li>
                            <li>
                                <p class="list_label">保護者連絡先メールアドレス</p>
                                <p class="list_field"><?= htmlspecialchars($guardian_email) ?></p>
                            </li>
                            <li>
                                <p class="list_label">保護者連絡先電話番号</p>
                                <p class="list_field"><?= htmlspecialchars($guardian_phone) ?></p>
                            </li>
                        <?php endif ?>
                    </ul>
                    <p class="cancel">申し込み後のキャンセル（返金）はできません。</p>
                    <div class="form_btn">
                        <input type="submit"name="action" class="btn btn_red" value="決済画面へ進む" />
                        <input type="button" class="btn btn_gray" value="内容を修正する" onclick="location.href='<?= htmlspecialchars($url) ?>';" />
                    </div>
                </div>
            </form>
        </section>
        <!-- contact -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>申し込み内容確認</li>
</ul>