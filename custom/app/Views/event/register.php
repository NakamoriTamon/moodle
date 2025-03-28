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
    $start_hour = $event->start_hour;
    $survey_date = new DateTime($event->course_date);
    $survey_date_part = $survey_date->format('Y-m-d');
    $surveyReleaseDate = new DateTime("$survey_date_part $start_hour");

    // 動画リリース情報の処理
    if (empty($event->release_date)) {
        // リリース情報がない場合は開催日時から終日を公開期間とする
        $date = new DateTime($event->course_date);
        $date_part = $date->format('Y-m-d');
        $videoReleaseDate = new DateTime("$date_part $start_hour"); // 動画公開開始
        $end_hour = '23:59:59';
        $videoReleaseEndDate = new DateTime("$date_part $end_hour"); // 動画公開終了
        $videoFormattedDate = $videoReleaseEndDate->format('Y年m月d日'); // 表示用
    } else {
        // リリース情報がある場合はそれを使用
        $date = new DateTime($event->release_date);
        $date_part = $date->format('Y-m-d H:i:s');
        $videoReleaseDate = new DateTime($date_part); // 動画公開開始

        $videoReleaseEndDate = new DateTime($event->release_date);
        $interval = new DateInterval('P' . intval($event->archive_streaming_period) . 'D');
        $videoReleaseEndDate->add($interval); // 動画公開終了

        $formatVideoReleaseEndDate = new DateTime($event->release_date);
        $formatVideoReleaseEndDate->add($interval);
        $formatVideoReleaseEndDate->modify('-1 day');
        $videoFormattedDate = $formatVideoReleaseEndDate->format('Y年m月d日'); // 表示用
    }

    // 資料(PDF)リリース情報の処理
    if (empty($event->material_release_date)) {
        // 資料リリース情報がない場合は開催日時から終日を公開期間とする
        $date = new DateTime($event->course_date);
        $date_part = $date->format('Y-m-d');
        $materialReleaseDate = new DateTime("$date_part $start_hour"); // 資料公開開始
        $end_hour = '23:59:59';
        $materialReleaseEndDate = new DateTime("$date_part $end_hour"); // 資料公開終了
        $materialFormattedDate = $materialReleaseEndDate->format('Y年m月d日'); // 表示用
    } else {
        // 資料リリース情報がある場合はそれを使用
        $date = new DateTime($event->material_release_date);
        $date_part = $date->format('Y-m-d H:i:s');
        $materialReleaseDate = new DateTime($date_part); // 資料公開開始

        $materialReleaseEndDate = new DateTime($event->material_release_date);
        $interval = new DateInterval('P' . intval($event->material_release_period) . 'D');
        $materialReleaseEndDate->add($interval); // 資料公開終了

        $formatMaterialReleaseEndDate = new DateTime($event->material_release_date);
        $formatMaterialReleaseEndDate->add($interval);
        $formatMaterialReleaseEndDate->modify('-1 day');
        $materialFormattedDate = $formatMaterialReleaseEndDate->format('Y年m月d日'); // 表示用
    }

    // アンケート終了日時は動画と資料の公開終了日時のうち遅い方
    $surveyEndDate = ($videoReleaseEndDate > $materialReleaseEndDate) ? clone $videoReleaseEndDate : clone $materialReleaseEndDate;

    // 表示する終了日時は動画と資料の公開終了日時のうち遅い方
    $formattedDate = ($videoReleaseEndDate > $materialReleaseEndDate) ? $videoFormattedDate : $materialFormattedDate;

    return [
        'videoReleaseDate' => $videoReleaseDate,
        'videoReleaseEndDate' => $videoReleaseEndDate,
        'materialReleaseDate' => $materialReleaseDate,
        'materialReleaseEndDate' => $materialReleaseEndDate,
        'formattedDate' => $formattedDate, // 表示用終了日時
        'surveyReleaseDate' => $surveyReleaseDate, // アンケート公開開始時間
        'surveyEndDate' => $surveyEndDate // アンケート公開終了時間
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
                                if ($now >= $view_date['materialReleaseDate'] && $now <= $view_date['materialReleaseEndDate'] && isset($event->materials)) {
                                    echo "<a href='#' class='btn_pdf' data-course-info-id='" . htmlspecialchars($event->course_info_id) . "'>PDF資料</a>";
                                } else {
                                    echo "<a href='#' class='btn_pdf' style='pointer-events: none;background: #E3E3E3;'>PDF資料</a>";
                                }

                                // 動画ボタン
                                if ($now >= $view_date['videoReleaseDate'] && $now <= $view_date['videoReleaseEndDate'] && isset($event->movies)) {
                                    echo "<a href='#'class='btn_movie' data-course-info-id='" . htmlspecialchars($event->course_info_id) . "'>イベント動画</a>";
                                } else {
                                    echo "<a href='#' class='btn_movie' style='pointer-events: none;background: #E3E3E3;'>イベント動画</a>";
                                }

                                // アンケートボタン
                                if ($now >= $view_date['surveyReleaseDate'] && $now <= $view_date['surveyEndDate']) {
                                    echo "<a href='../survey/index.php?course_info_id=" . htmlspecialchars($event->course_info_id) . "' class='btn_answer'>アンケートに回答する</a>";
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
    $(".btn_movie").on("click", function(e) {
        e.preventDefault();
        const course_info_id = $(this).data("course-info-id");

        // フォームを作成して自動送信
        let form = $('<form>', {
            action: '/custom/app/Views/event/movie.php', // 指定のURLにPOST
            method: 'POST',
            style: 'display: none;'
        });

        $('<input>').attr({
            type: 'hidden',
            name: 'course_info_id',
            value: course_info_id
        }).appendTo(form);

        $('body').append(form);
        form.submit();

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