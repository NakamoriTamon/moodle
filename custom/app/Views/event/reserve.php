<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Controllers/event/event_application_reserve_controller.php');
require_once($CFG->dirroot . '/custom/app/Controllers/event/event_detail_controller.php');
include($CFG->dirroot . '/custom/app/Views/common/header.php');

$reserve_id = isset($_GET['id']) ? $_GET['id'] : null;

$reserve_controller = new EventReserveController();
$reserve_list = $reserve_controller->index($reserve_id);
$event_list = $reserve_controller->getReserveById($reserve_list->event_id);
$user_guardian = $reserve_controller->getUserGardianById($reserve_list->user_id);
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />
<main id="subpage">
  <section id="heading" class="inner_l">
    <h2 class="head_ttl" data-en="EVENT RESERVATION DETAILS">イベント予約情報詳細</h2>
  </section>
  <!-- heading -->
  <div class="inner_l">
    <section id="form" class="event confirm">
      <div class="whitebox form_cont">
        <div class="inner_m">
          <ul class="list">
            <li class="list_item01">
              <p class="list_label">氏名</p>
              <p class="list_field f_txt"><?= htmlspecialchars($reserve_list->name); ?></p>
            </li>
            <li class="list_item02">
              <p class="list_label">フリガナ</p>
              <p class="list_field f_txt"><?= htmlspecialchars($reserve_list->name_kana); ?></p>
            </li>
            <li class="list_item03 long_item">
              <p class="list_label">チケット名称</p>
              <p class="list_field f_txt"><?= htmlspecialchars($event_list->name); ?></p>
            </li>
            <li class="list_item04">
              <p class="list_label">枚数選択</p>
              <p class="list_field f_txt"><?= htmlspecialchars($reserve_list->ticket_count); ?>枚</p>
            </li>
            <li class="list_item05">
              <p class="list_label">金額</p>
              <p class="list_field f_txt"><?= number_format($reserve_list->price); ?>円</p>
            </li>
            <li class="list_item06">
              <p class="list_label">お支払方法</p>
              <p class="list_field f_txt">
                <?php if (($reserve_list->pay_method) == 0) {
                  echo "無料";
                } elseif (($reserve_list->pay_method) == 1) {
                  echo "クレジット";
                } elseif (($reserve_list->pay_method) == 2) {
                  echo "銀行振込";
                } else {
                  echo "コンビニ決済";
                }
                ?>
              </p>
            </li>
            <li class="list_item07">
              <p class="list_label">決済状況</p>
              <p class="list_field f_txt">
                <?php if ($reserve_list->payment_date) {
                  echo "決済済み";
                } else {
                  echo "未決済";
                }
                ?>
            </li>
            <?php if ($user_guardian) { ?>
              <li class="list_item08">
                <p class="list_label">お連れ様の氏名</p>
                <p class="list_field f_txt"><?= htmlspecialchars($reserve_list->companion_name); ?></p>
              </li>
              <li class="list_item09">
                <p class="list_label">お連れ様のメールアドレス</p>
                <p class="list_field f_txt"><?= htmlspecialchars($reserve_list->companion_email); ?></p>
              </li>
            <?php } ?>
          </ul>
        </div>
      </div>
      <a href="/custom/app/Views/mypage/index.php" class="btn btn_blue arrow box_bottom_btn">前へ戻る</a>
    </section>
  </div>
</main>

<ul id="pankuzu" class="inner_l">
  <li><a href="../index.php">トップページ</a></li>
  <li>イベント予約情報詳細</li>
</ul>

<?php include($CFG->dirroot . '/custom/app/Views/common/footer.php') ?>
</body>

</html>