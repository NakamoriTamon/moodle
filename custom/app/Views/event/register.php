<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Controllers/event/event_application_register_controller.php');
require_once($CFG->dirroot . '/custom/app/Controllers/event/event_detail_controller.php');
include($CFG->dirroot . '/custom/app/Views/common/header.php');

// コントローラーのインスタンスを作成
$reserve_controller = new EventRegisterController();

// ページ数と1ページあたりの件数を設定（ここでは例として1ページあたり12件）
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;

// ページネーション対応のイベント情報を取得
$eventsData = $reserve_controller->events($currentPage, $perPage);
$events = $eventsData['data'];
$pagination = $eventsData['pagination'];

$now = new DateTime();
function viewDates($event)
{
    if (empty($event->release_date)) { // リリース情報が無い場合開催時刻　～　終日がアンケートや資料が見れる
        $start_hour = $event->start_hour;
        $date = new DateTime($event->course_date);
        $date_part = $date->format('Y-m-d');
        $releaseDate = new DateTime("$date_part $start_hour"); // 公開開始

        $end_hour = '23:59:59';
        $releaseEndDate = new DateTime("$date_part $end_hour"); // 比較用　公開終了
        $formattedDate = $releaseEndDate->format('Y年m月d日'); // 表示用　公開終了
    } else { // リリース情報が有る場合開催時刻　～　がアンケートや資料が見れる
        $date = new DateTime($event->release_date);
        $date_part = $date->format('Y-m-d H:i:s');
        $releaseDate = new DateTime($date_part); // 公開開始

        $releaseEndDate = new DateTime($event->release_date);
        $interval = new DateInterval('P' . intval($event->archive_streaming_period) . 'D');
        $releaseEndDate->add($interval); // 比較用　公開終了
        $formatReleaseEndDate = new DateTime($event->release_date);
        $formatReleaseEndDate->add($interval);
        $formatReleaseEndDate->modify('-1 day');
        $formattedDate = $formatReleaseEndDate->format('Y年m月d日'); // 表示用　公開終了
    }
    return [
        'releaseDate' => $releaseDate,
        'releaseEndDate' => $releaseEndDate,
        'formattedDate' => $formattedDate
    ];
}
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
                    $view_date = viewDates($event);
                    ?>
                    <li class="event_item">
                        <figure class="img">
                            <img src="<?php echo empty($event->thumbnail_img) ? DEFAULT_THUMBNAIL : $event->thumbnail_img ?>" alt="" />
                        </figure>
                        <div class="event_info">
                            <p class="event_ttl">
                                【第<?= htmlspecialchars($event->no) ?>回】
                                <?= htmlspecialchars($event->name) ?>
                            </p>
                            <div class="event_btns">
                                <?php
                                // PDFボタン
                                if ($now >= $view_date['releaseDate'] && $now <= $view_date['releaseEndDate'] && isset($event->materials)) {
                                    echo "<a href='#' class='btn_pdf' data-course-info-id='" . htmlspecialchars($event->course_info_id) . "'>PDF資料</a>";
                                } else {
                                    echo "<a href='#' class='btn_pdf' style='pointer-events: none;background: #E3E3E3;'>PDF資料</a>";
                                }

                                // 動画ボタン
                                if ($now >= $view_date['releaseDate'] && $now <= $view_date['releaseEndDate'] && isset($event->movies)) {
                                    echo '<a href="movie.php?event_id=' . htmlspecialchars($event->event_id) . '" class="btn_movie">イベント動画</a>';
                                } else {
                                    echo "<a href='#' class='btn_movie' style='pointer-events: none;background: #E3E3E3;'>イベント動画</a>";
                                }

                                // アンケートボタン
                                if ($now >= $view_date['releaseDate'] && $now <= $view_date['releaseEndDate']) {
                                    echo "<a href='../survey/index.php?event_id=" . htmlspecialchars($event->event_id) . "' class='btn_answer'>アンケートに回答する</a>";
                                } else {
                                    echo "<a href='#' class='btn_answer' style='pointer-events: none;background: #E3E3E3;'>アンケートに回答する</a>";
                                }
                                ?>
                            </div>
                            <p class="event_term">
                                閲覧期間<span>～<?= htmlspecialchars($view_date['formattedDate']) ?>まで</span>
                            </p>
                        </div>
                    </li>
                    <div id="pdf_contents" style="display:none;">
                        <div id="pdf_content_<?= htmlspecialchars($event->course_info_id) ?>">
                            <ul class="pdf_list">
                                <?php if ($event->materials): ?>
                                    <?php foreach ($event->materials as $pdf): ?>
                                        <li>
                                            <p class="name"><?= htmlspecialchars($pdf) ?></p>
                                            <a href="#" class="open-pdf btn btn_navy pdf" data-course_no="<?= htmlspecialchars($event->no) ?>" data-course_info="<?= htmlspecialchars($event->course_info_id) ?>" data-file_name="<?= htmlspecialchars($pdf) ?>">PDF資料</a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li>
                                        <p class="name">PDF資料なし</p>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            </ul>
            <div class="navigation" style="position: relative; z-index: 0;">
                <ul class="result_pg">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <li><a href="?page=<?= intval($pagination['current_page']) - 1 ?>" class="prev"></a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li><a href="?page=<?= $i ?>" class="num <?= $i == $pagination['current_page'] ? 'active' : '' ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <li><a href="?page=<?= intval($pagination['current_page']) + 1 ?>" class="next"></a></li>
                    <?php endif; ?>
                </ul>
                <a href="/custom/app/Views/mypage/index.php" class="btn btn_blue arrow box_bottom_btn">前へ戻る</a>
            </div>
        </section>
    </div>
</main>

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

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var srlpos = 0;

    $(".btn_pdf").on("click", function(e) {
        e.preventDefault();
        var courseInfoId = $(this).data("course-info-id");
        srlpos = $(window).scrollTop();

        var pdfHtml = $("#pdf_content_" + courseInfoId).html();
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

    $(document).on("click", ".open-pdf", function(e) {
        e.preventDefault();
        var materialCourseNo = $(this).data("course_no");
        var materialCourseId = $(this).data("course_info");
        var materialFileName = $(this).data("file_name");
        const pdfUrl = "/uploads/material/" + materialCourseId + '/' + materialCourseNo + '/' + materialFileName;
        window.open(`/custom/app/Views/event/pdf.php?file=${encodeURIComponent(pdfUrl)}`, "_blank");
    });
</script>