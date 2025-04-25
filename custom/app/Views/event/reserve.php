<?php
require_once('/var/www/html/moodle/config.php');
include('/var/www/html/moodle/custom/app/Views/common/header.php');
include('/var/www/html/moodle/custom/app/Controllers/event/event_application_reserve_controller.php');

$event_application_reserve_controller = new EventReserveController();
$session_course_id = isset($_SESSION['reserve']['course_id']) ? $_SESSION['reserve']['course_id'] : '';
$session_application_id = isset($_SESSION['reserve']['id']) ? $_SESSION['reserve']['id'] : '';
$session_event_application_course_info_id = isset($_SESSION['reserve']['event_application_course_info_id']) ? $_SESSION['reserve']['event_application_course_info_id'] : '';
$course_id = $_POST['course_id'] ?? $session_course_id;
$application_id =  $_POST['id'] ?? $session_application_id;
$event_application_course_info_id =  $_POST['event_application_course_info_id'] ?? $session_event_application_course_info_id;
$result_list = $event_application_reserve_controller->index($course_id, $application_id);
$success = isset($_SESSION['message_success']) ? $_SESSION['message_success'] : null;
$common_array = $result_list['common_array'];
$common_application = $result_list['common_application'];
$course_number = $result_list['course_number'];
$event_name = $result_list['event_name'];
$event_kbn = $result_list['event_kbn'];
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
      <div class="whitebox form_cont">
        <form id="upsert_form" method="POST" action="/custom/app/Controllers/event/event_application_reserve_upsert_controller.php">
          <input type="hidden" name="application_id" value="<?= htmlspecialchars($application_id) ?>">
          <input type="hidden" name="course_id" value="<?= htmlspecialchars($course_id) ?>">
          <input type="hidden" name="event_application_course_info_id" value="<?= htmlspecialchars($event_application_course_info_id) ?>">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
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
                <p class="list_field f_txt">
                  <?php if ($event_kbn == EVERY_DAY_EVENT) : ?>
                    <?php echo htmlspecialchars($event_name) ?>
                  <?php else: ?>
                    <?php echo htmlspecialchars($course_number . $event_name) ?>
                  <?php endif; ?></p>
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
                  <p class="list_label">お子様の氏名</p>
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
        </form>
        <form id="cancel_form" method="POST" action="/custom/app/Controllers/event/event_application_course_info_cancel_controller.php">
          <input type="hidden" name="cancel_event_application_id" value="<?= htmlspecialchars($application_id) ?>">
          <a id="cancel_submit" class="btn btn_gray arrow box_bottom_btn">イベント参加キャンセル</a>
        </form>
      </div>
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
  var price = "<?= htmlspecialchars($price, ENT_QUOTES, 'UTF-8') ?>";
  var message = "イベントの参加をキャンセルしても<br><span style='color:red'>返金はできません。</span><br>本当にキャンセルしてもよろしいですか。";
  if (price == '無料') {
    message = "本当にキャンセルしてもよろしいですか。";
  }
  $(document).ready(function() {
    $('#submit').on("click", function() {
      $('#upsert_form').submit();
    });
  });

  $(document).ready(function() {
    // キャンセルボタンクリック時
    $('#cancel_form').on('click', function(e) {
      e.preventDefault(); // aタグのデフォルト動作を防止
      showModal('イベント参加キャンセル', message);
    });

    $(document).on('click', '.goCancel', function() {
      $('#cancel_form').submit(); // フォームを送信
    });
  });

  // モーダル表示
  function showModal(title, message) {
    var modalHtml = `
        <div id="confirmation-modal">
            <div class="modal_cont">
                <h2>${title}</h2>
                <p>${message}</p>
                <div class="modal-buttons">
                    <button class="modal-withdrawal goCancel">はい</button>
                    <button class="modal-close">いいえ</button>
                </div>
            </div>
        </div>
    `;
    $('body').append(modalHtml);
    $('#confirmation-modal').show();
  }

  // モーダルの閉じるボタン
  $(document).on('click', '.modal-close', function() {
    $('#confirmation-modal').remove();
  });
</script>