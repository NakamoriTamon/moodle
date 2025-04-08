<?php
include('/var/www/html/moodle/custom/app/Views/common/header.php');
include('/var/www/html/moodle/custom/app/Controllers/event/event_application_history_controller.php');

$event_application_history_controller = new EventHistoryController();
$result_list = $event_application_history_controller->index($_POST);

$common_array = $result_list['common_array'];
$common_application = $result_list['common_application'];
$event_name = $result_list['event_name'];
$price = $result_list['price'];
$pay_method = $result_list['pay_method'];
$is_payment = $result_list['is_payment'];
$companion_array = $result_list['companion_array'];
$child_name = $result_list['child_name'];
?>



<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />
<main id="subpage">
  <section id="heading" class="inner_l">
    <h2 class="head_ttl" data-en="EVENT HISTORY DETAILS">イベント履歴詳細</h2>
  </section>
  <!-- heading -->

  <div class="inner_l">
    <section id="form" class="event confirm">
      <div class="whitebox form_cont">
        <div class="inner_m">
          <ul class="list">
            <li class="list_item01">
              <p class="list_label">氏名</p>
              <p class="list_field f_txt">
                <?= htmlspecialchars($common_application['name']) ?>
              </p>
            </li>
            <li class="list_item02">
              <p class="list_label">フリガナ</p>
              <p class="list_field f_txt"><?= htmlspecialchars($common_application['name_kana']) ?></p>
            </li>
            <li class="list_item03 long_item">
              <p class="list_label">チケット名称</p>
              <p class="list_field f_txt">
                <?= htmlspecialchars($event_name) ?>
              </p>
            </li>
            <li class="list_item04">
              <p class="list_label">枚数選択</p>
              <p class="list_field f_txt"><?= htmlspecialchars($common_application['ticket_count']) ?>枚</p>
            </li>
            <li class="list_item05">
              <p class="list_label">金額</p>
              <p class="list_field f_txt"><?= htmlspecialchars($price) ?></p>
            </li>
            <?php if ($price != '無料') { ?>
              <li class="list_item06">
                <p class="list_label">お支払方法</p>
                <p class="list_field f_txt"><?= htmlspecialchars($pay_method) ?></p>
              </li>
              <li class="list_item07">
                <p class="list_label">決済状況</p>
                <p class="list_field f_txt"><?= htmlspecialchars($is_payment) ?></p>
              </li>
            <?php } ?>
            <?php if (!empty($child_name)) { ?>
              <li class="list_item08">
                <p class="list_label">お連れ様の氏名</p>
                <p class="list_field f_txt"><?= htmlspecialchars($child_name) ?></p>
              </li>
            <?php } ?>
            <?php if (!empty($companion_array)) { ?>
              <li class="list_item09 flex-wrap">
                <p class="list_label">お連れ様のメールアドレス</p>
                <?php $email_count = 0; ?>
                <?php foreach ($companion_array as $companion_email) { ?>
                  <?php if ($email_count > 0) { ?><p class="list_label ano_list_label"><?php } ?>
                    <p class="list_field f_txt <?php if ($email_count > 0) { ?>ano_f_txt<?php } ?> "><?= htmlspecialchars($companion_email['participant_mail']) ?></p>
                  <?php } ?>
              </li>
            <?php } ?>
          </ul>
        </div>
      </div>
      <a href="/custom/app/Views/mypage/index.php" class="btn btn_blue arrow box_bottom_btn">前へ戻る</a>
    </section>
    <!-- contact -->
  </div>
</main>

<ul id="pankuzu" class="inner_l">
  <li><a href="../index.php">トップページ</a></li>
  <li>イベント履歴詳細</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php') ?>
</body>

</html>