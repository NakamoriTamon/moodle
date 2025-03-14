<?php
require_once('/var/www/html/moodle/config.php');
include($CFG->dirroot . '/custom/app/Views/common/header.php');
include('/var/www/html/moodle/custom/app/Views/common/header.php');
include('/var/www/html/moodle/custom/app/Controllers/event/event_application_reserve_controller.php');

$event_application_reserve_controller = new EventReserveController();
$result_list = $event_application_reserve_controller->index($_POST);

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
    <h2 class="head_ttl" data-en="EVENT RESERVATION DETAILS">イベント予約情報詳細</h2>
  </section>
  <!-- heading -->
  <div class="inner_l">
    <section id="form" class="event confirm">
      <form id="upsert_form" method="POST" action="/custom/app/Controllers/event/event_application_reserve_upsert_controller.php">
        <input type="hidden" name="application_id" value="<?= $common_application['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="whitebox form_cont">
          <div class="inner_m">
            <ul class="list">
              <li class="list_item01">
                <p class="list_label">氏名</p>
                <p class="list_field f_txt"> <?= htmlspecialchars($common_application['name']) ?></p>
              </li>
              <li class="list_item02">
                <p class="list_label">フリガナ</p>
                <p class="list_field f_txt"><?= htmlspecialchars($common_application['name_kana']) ?></p>
              </li>
              <li class="list_item03 long_item">
                <p class="list_label">チケット名称</p>
                <p class="list_field f_txt"><?= htmlspecialchars($event_name) ?></p>
              </li>
              <li class="list_item04">
                <p class="list_label">枚数選択</p>
                <p class="list_field f_txt"><?= htmlspecialchars($common_application['ticket_count']) ?>枚</p>
              </li>
              <li class="list_item05">
                <p class="list_label">金額</p>
                <p class="list_field f_txt"><?= htmlspecialchars($price) ?></p>
              </li>
              <li class="list_item06">
                <p class="list_label">お支払方法</p>
                <p class="list_field f_txt">
                  <?= htmlspecialchars($pay_method) ?>
                </p>
              </li>
              <li class="list_item07">
                <p class="list_label">決済状況</p>
                <p class="list_field f_txt">
                  <?= htmlspecialchars($is_payment) ?>
              </li>
              <li class="list_item08">
                <p class="list_label">お連れ様の氏名</p>
                <input class="list_field" type="text" name="companion_name" value="<?= htmlspecialchars($child_name) ?>">
                </p>
              </li>
              <?php if (!empty($companion_array)) { ?>
                <li class="list_item09 flex-wrap">
                  <p class="list_label">お連れ様のメールアドレス</p>
                  <?php foreach ($companion_array as $key => $companion_email) { ?>
                    <?php if ($key > 0) { ?><p class="list_label ano_list_label"><? } ?>
                      <p class="list_field f_txt <?php if ($key > 0) { ?>ano_f_txt<? } ?> "><?= htmlspecialchars($companion_email) ?></p>
                    <? } ?>
                </li>
              <?php } ?>
              <a id="submit" class="btn btn_red arrow box_bottom_btn">更新する</a>
            </ul>
          </div>
        </div>
      </form>
      <a href="/custom/app/Views/mypage/index.php" class="btn btn_blue arrow box_bottom_btn">マイページ</a>
    </section>
  </div>
</main>

<ul id="pankuzu" class="inner_l">
  <li><a href="/custom/app/Views/index.php">トップページ</a></li>
  <li>イベント予約情報詳細</li>
</ul>

<?php include($CFG->dirroot . '/custom/app/Views/common/footer.php') ?>
</body>

</html>

<script>
  $('#submit').on("click", function() {
    $('#upsert_form').submit();
  });
</script>