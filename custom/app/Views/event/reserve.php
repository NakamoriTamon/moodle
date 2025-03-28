<?php
require_once('/var/www/html/moodle/config.php');
include('/var/www/html/moodle/custom/app/Views/common/header.php');
include('/var/www/html/moodle/custom/app/Controllers/event/event_application_reserve_controller.php');

$event_application_reserve_controller = new EventReserveController();
$session_course_id = isset($_SESSION['reserve']['course_id']) ? $_SESSION['reserve']['course_id'] : null;
$session_application_id = isset($_SESSION['reserve']['id']) ? $_SESSION['reserve']['id'] : null;
$course_id = $_POST['course_id'] ?? $session_course_id;
$application_id =  $_POST['id'] ?? $session_application_id;
$result_list = $event_application_reserve_controller->index($course_id, $application_id);
$success = isset($_SESSION['message_success']) ? $_SESSION['message_success'] : null;
$common_array = $result_list['common_array'];
$common_application = $result_list['common_application'];
$event_name = $result_list['event_name'];
$price = $result_list['price'];
$pay_method = $result_list['pay_method'];
$is_payment = $result_list['is_payment'];
$companion_array = $result_list['companion_array'];
$child_name = $result_list['child_name'];
$realtime_path = $result_list['realtime_path'];
$old_input = isset($_SESSION['old_input']) ? $_SESSION['old_input'] : null;

unset($_SESSION['old_input'], $_SESSION['message_success'], $_SESSION['errors']);
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
        <input type="hidden" name="application_id" value="<?= $application_id ?>">
        <input type="hidden" name="course_id" value="<?= $course_id ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="whitebox form_cont">
          <div class="inner_m">
            <?php if (!empty($basic_error)) { ?><p class="error"> <?= $basic_error ?></p><?php } ?>
            <?php if (!empty($success)) { ?><p id="main_success_message"> <?= $success ?></p><?php } ?>
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
                <p class="list_field f_txt">
                  <?= htmlspecialchars($price . ' ') ?>
                  <?php if ($price != '無料') { ?>
                    <?= htmlspecialchars($common_application['event_application_package_types'] == 2 ? '(一括申し込み)' : '') ?>
                  <?php } ?>
                </p>
              </li>
              <?php if ($price != '無料') { ?>
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
              <?php } ?>
              <?php if (!empty($child_name)) { ?>
                <li class="list_item08  <?= !empty($errors['companion_name']) ? 'flex-wrap' : '' ?>">
                  <p class="list_label">お連れ様の氏名</p>
                  <input class="list_field" type="text" name="companion_name" value="<?= htmlspecialchars(isSetValue($child_name ?? '', $old_input['companion_name'] ?? '')) ?>">
                  <?php if (!empty($errors['companion_name'])): ?>
                    <div class="error-msg mt-2">
                      <p class="list_label"></p>
                      <?= htmlspecialchars($errors['companion_name']); ?>
                    </div>
                  <?php endif; ?>
                </li>
              <?php } ?>
              <?php if (!empty($companion_array)) { ?>
                <li class="list_item09 flex-wrap">
                  <p class="list_label">お連れ様のメールアドレス</p>
                  <?php $email_count = 0; ?>
                  <?php foreach ($companion_array as $companion_email) { ?>
                    <?php if ($email_count > 0) { ?><p class="list_label ano_list_label"><?php } ?>
                      <p class="list_field f_txt <?php if ($email_count > 0) { ?>ano_f_txt<?php } ?> ">
                        <?= htmlspecialchars($companion_email['participant_mail']) ?>
                      </p>
                    <?php $email_count = $email_count + 1;
                  } ?>
                </li>
              <?php } ?>
              <?php if (!empty($realtime_path) && ($price == '無料' || $is_payment == '決済済')) { ?>
                <li class="list_item10">
                  <p class="list_label">リアルタイム配信パス</p>
                  <a id="realtime_path" href="<?= htmlspecialchars($realtime_path) ?>" target="_blank" rel="noopener noreferrer" class=" list_field f_txt"><?= htmlspecialchars($realtime_path) ?></a>
                </li>
              <?php } ?>
              <?php if (!empty($child_name)) { ?>
                <a id="submit" class="btn btn_red arrow box_bottom_btn">更新する</a>
              <?php } ?>
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