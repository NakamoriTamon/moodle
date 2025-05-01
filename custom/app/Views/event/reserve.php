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
$result_list = $event_application_reserve_controller->index($course_id, $application_id, $event_application_course_info_id);
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
$format_date = $result_list['format_date'];
$format_hour = $result_list['format_hour'];
$venue_name = $result_list['venue_name'];
$lecture_format_id = $_POST['lecture_format_id'] ?? "";
// QR表示判定
$qr_class = '';
$encrypted_eaci_id = "";
if ($lecture_format_id != ON_DEMAND) {
    if ($price == '無料') {
        $qr_class = 'js_pay';
        $encrypted_eaci_id = $result_list['encrypted_eaci_id'];
    } elseif (!empty($common_application['payment_date'])) {
        $qr_class = 'js_pay';
        $encrypted_eaci_id = $result_list['encrypted_eaci_id'];
    }
}

unset($_SESSION['old_input'], $_SESSION['message_success'], $_SESSION['errors']);
?>

<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/mypage.css" />
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />
<main id="subpage">
  <section id="heading" class="inner_l">
    <h2 class="head_ttl" data-en="EVENT RESERVATION DETAILS">イベント予約情報詳細</h2>
  </section>
  <!-- heading -->
  <div class="inner_l">
    <section id="form" class="event confirm">
      <div class="whitebox form_cont <?= htmlspecialchars($qr_class) ?>">
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
                  <?php if ($event_kbn == PLURAL_EVENT) { ?><?= htmlspecialchars($course_number); ?><?php } ?> <?= htmlspecialchars($event_name); ?></p>
              </li>
              <li class="list_item04">
                <p class="list_label">開催日</p>
                <p class="list_field f_txt"><?php echo htmlspecialchars($format_date) ?></p>
              </li>
              <li class="list_item04">
                <p class="list_label">開催時間</p>
                <p class="list_field f_txt"><?php echo htmlspecialchars($format_hour) ?></p>
              </li>
              <?php if(!empty($venue_name)) { ?>
              <li class="list_item04">
                <p class="list_label">会場</p>
                <p class="list_field f_txt"><?php echo htmlspecialchars($venue_name) ?></p>
              </li>
              <?php } ?>
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
                  <div id="companion_flex">
                    <input id="companion_name_input" class=" list_field" type="text" name="companion_name" value="<?= htmlspecialchars(isSetValue($child_name ?? '', $old_input['companion_name'] ?? '')) ?>">
                    <button id="companion_name_btn">更新</button>
                  </div>
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
              <?php if (!empty($common_application['guardian_name'])) { ?>
                <li class="list_item11">
                  <p class="list_label">保護者氏名</p>
                  <p><?= htmlspecialchars($common_application['guardian_name']) ?></p>
                </li>
              <?php } ?>
              <?php if (!empty($common_application['guardian_email'])) { ?>
                <li class="list_item12">
                  <p class="list_label">保護者メールアドレス</p>
                  <p><?= htmlspecialchars($common_application['guardian_email']) ?></p>
                </li>
              <?php } ?>
              <?php if (!empty($common_application['guardian_phone'])) { ?>
                <li class="list_item13">
                  <p class="list_label">保護者電話番号</p>
                  <p><?= htmlspecialchars($common_application['guardian_phone']) ?></p>
                </li>
              <?php } ?>
            </ul>
          </div>
        </form>
        <?php if(!empty($qr_class)) { ?>
          <a href="#" class="info_wrap_qr btn btn_red arrow box_bottom_btn btn_login" data-event-application-course-info-id="<?= $encrypted_eaci_id ?>" data-name="<?= $event_name ?>" data-date="<?= $format_date ?>">
            デジタルチケットを表示する
          </a>
        <?php } ?>
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

<div id="modal" class="modal_ticket">
    <div class="modal_bg js_close"></div>
    <div class="modal_cont">
        <!-- <span class="cross js_close"></span> -->
        <p id="moodle_ticket_date" class="ticket_date">2025/00/00（金）</p>
        <p id="modal_event_name" class="ticket_ttl">中之島芸術センター 演劇公演<br />『中之島デリバティブⅢ』</p>
        <div id="qrcode" class="ticket_qr"><img id="qrImage" src="" alt="" /></div>
        <p class="ticket_txt">こちらの画面を受付でご提示ください。</p>
    </div>
</div>

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

  $(".info_wrap_qr").on("click", function(e) {
        e.preventDefault();
        if ($(this).parents('div').hasClass('js_pay')) {
            srlpos = $(window).scrollTop();
            $("#modal").fadeIn();
            $("body").addClass("modal_fix").css({
                top: -srlpos
            });
            const encrypted_eaci_id = $(this).data("event-application-course-info-id");
            const name = $(this).data("name");
            const date = $(this).data("date");

            $('#moodle_ticket_date').text(date);
            $('#modal_event_name').text(name);

            // QRコード画像をセット
            $("#qrImage").attr("src", "/custom/app/Views/event/qr_generator.php?eaci_id=" + encrypted_eaci_id);

            return false;
        }
    });
</script>