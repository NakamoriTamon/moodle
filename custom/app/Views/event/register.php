<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Controllers/event/event_application_register_controller.php');
require_once($CFG->dirroot . '/custom/app/Controllers/event/event_detail_controller.php');
include($CFG->dirroot . '/custom/app/Views/common/header.php');

$reserve_controller = new EventRegisterController();
$events = $reserve_controller->events();
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/event.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="REGISTERED EVENT">申し込みイベント</h2>
    </section>

    <div class="inner_l">
        <section id="result" class="register">
            <p class="sent">
                お申込みされたイベントの資料DLと動画の閲覧が一定期間可能です。<br />
                各項目よりご利用いただけます。
            </p>
            <ul class="result_list" id="event">
                <?php foreach ($events as $event): ?>
                    <?php
                    $eventDate = new DateTime($event->event_date);
                    $interval = new DateInterval('P' . intval($event->archive_streaming_period) . 'D');
                    $eventDate->add($interval);
                    $formattedDate = $eventDate->format('Y年m月d日');
                    $pdf_list = $reserve_controller->pdf_list($event->id);

                    $course_info_list = $reserve_controller->course_info_list($event->id);
                    ?>
                    <li class="event_item">
                        <figure class="img">
                            <img src="/custom/public/assets/img/event/event01.jpg" alt="" />
                        </figure>
                        <div class="event_info">
                            <p class="event_ttl">
                                【第<?= htmlspecialchars($course_info_list->course_info_id) ?>回】
                                <?= htmlspecialchars($event->name) ?>
                            </p>
                            <div class="event_btns">
                                <?php if ($pdf_list) {
                                    echo "<a href='#' class='btn_pdf' data-event-id='" . htmlspecialchars($event->id) . "'>PDF資料</a>";
                                } else {
                                    echo "<a href='#' class='btn_pdf' style='pointer-events: none;background: #E3E3E3;'></a>";
                                }
                                ?>

                                <a href="movie.php" class="btn_movie">イベント動画</a>
                                <a href="../survey/index.php?event_id=<?= htmlspecialchars($event->id) ?>" class="btn_answer">アンケートに回答する</a>
                            </div>
                            <p class="event_term">
                                閲覧期間<span>～<?= htmlspecialchars($formattedDate) ?>まで</span>
                            </p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="navigation" style="position: relative; z-index: 0;">
                <a href="/custom/app/Views/mypage/index.php" class="btn btn_blue arrow box_bottom_btn">前へ戻る</a>
                <ul class="result_pg">
                    <li><a href="" class="prev"></a></li>
                    <li><a href="" class="num active">1</a></li>
                    <li><a href="" class="num">2</a></li>
                    <li><a href="" class="num">3</a></li>
                    <li><a href="" class="num">4</a></li>
                    <li><a href="" class="num">5</a></li>
                    <li><a href="" class="next"></a></li>
                </ul>
            </div>
        </section>
    </div>
</main>

<div id="pdf_contents" style="display:none;">
    <?php foreach ($events as $event): ?>
        <?php $pdf_list = $reserve_controller->pdf_list($event->id); ?>
        <div id="pdf_content_<?= htmlspecialchars($event->id) ?>">
            <ul class="pdf_list">
                <?php if ($pdf_list): ?>
                    <?php foreach ($pdf_list as $pdf): ?>
                        <li>
                            <p class="name"><?= htmlspecialchars($pdf->file_name) ?></p>
                            <a href="/uploads/material/<?= htmlspecialchars($pdf->file_name) ?>" class="btn btn_navy pdf" target="_blank">PDF資料</a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>
                        <p class="name">PDF資料なし</p>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endforeach; ?>
</div>

<div id="modal" class="modal_pdf">
    <div class="modal_bg js_close"></div>
    <div class="modal_cont inner_m">
        <span class="cross js_close"></span>
        <div id="modal_pdf_content"></div>
    </div>
</div>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>申し込みイベント</li>
</ul>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var srlpos = 0;

    $(".btn_pdf").on("click", function(e) {
        e.preventDefault();
        var eventId = $(this).data("event-id");
        srlpos = $(window).scrollTop();

        var pdfHtml = $("#pdf_content_" + eventId).html();
        $("#modal_pdf_content").html(pdfHtml);

        $("#modal").fadeIn();
        $("body").addClass("modal_fix").css({
            top: -srlpos
        });
    });

    $(".js_close").on("click", function() {
        $("#modal").fadeOut();
        $("body").removeClass("modal_fix").css({
            top: 0
        });
        $(window).scrollTop(srlpos);
    });
</script>

<?php include($CFG->dirroot . '/custom/app/Views/common/footer.php'); ?>
</body>

</html>