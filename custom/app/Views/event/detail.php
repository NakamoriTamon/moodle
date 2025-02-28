<?php include('/var/www/html/moodle/custom/app/Views/common/header.php');
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Controllers/event/event_detail_controller.php');

$dateTime = DateTime::createFromFormat('H:i:s', $event['start_hour']);
$start_hour = $dateTime->format('H:i'); // "00:00"
$dateTime = DateTime::createFromFormat('H:i:s', $event['end_hour']);
$end_hour = $dateTime->format('H:i'); // "00:00"
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/event.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="EVENT DETAIL">イベント詳細</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="desc">
            <div class="desc_info">
                <h3 class="event_ttl"><?= htmlspecialchars($event['name']); ?></h3>
                <ul class="event_status">
                    <li class="no"><?= htmlspecialchars(EVENT_STATUS_LIST[$event['event_status']]); ?></li>
                    <li class="no"><?= htmlspecialchars(DEADLINE_LIST[$event['deadline_status']]); ?></li>
                </ul>
                <div class="event_sched">
                    <p class="term">開催日</p>
                    <div class="date">
                        <?php foreach ($event['select_course'] as $no => $course): ?>
                            <p class="dt01"><?= $no ?>回目：<?= (new DateTime($course['course_date']))->format('Y年m月d日'); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="category" id="category">
                    <?php foreach ($select_categorys as $select_category): ?>
                        <div class="cat_item category01 active">
                            <div class="cat_btn">
                                <?php if (!empty($select_category['path'])) { ?>
                                    <object
                                        type="image/svg+xml"
                                        data="<?= htmlspecialchars($select_category['path']) ?>"
                                        class="obj">
                                    </object>
                                <?php } ?>
                                <p class="txt"><?php if (in_array($select_category, array_column($categorys, 'id'))) ?><?= $categorys[array_search($select_category, array_column($categorys, 'id'))]['name'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="desc_img">
                <div class="img"><img src="<?= htmlspecialchars($event['thumbnail_img']); ?>" alt="" /></div>
                <p class="big">タップで拡大する</p>
            </div>
        </section>
        <!-- desc -->

        <section id="detail">
            <div class="whitebox">
                <div class="inner_m">
                    <div class="detail_item">
                        <h2 class="block_ttl">内容</h2>
                        <p class="sent">
                            <?= nl2br($event['description']); ?>
                        </p>
                    </div>
                    <div class="detail_item">
                        <h2 class="block_ttl">概要</h2>
                        <div class="summary sent">
                            <ul class="summary_list">
                                <li>
                                    <p class="term">開催日</p>
                                    <p class="desc"><?= (new DateTime($event['event_date']))->format('Y年m月d日') . '（' . WEEKDAYS[(new DateTime($event['event_date']))->format('w')] . '）'; ?></p>
                                </li>
                                <li>
                                    <p class="term">時間</p>
                                    <p class="desc"><?= htmlspecialchars($start_hour); ?>～<?= htmlspecialchars($end_hour); ?></p>
                                </li>
                                <li>
                                    <p class="term">対象</p>
                                    <p class="desc">○○○○</p>
                                </li>
                                <li>
                                    <p class="term">定員</p>
                                    <p class="desc"><?= htmlspecialchars(number_format($event['capacity'])); ?>名</p>
                                </li>
                                <li>
                                    <p class="term">講義形式</p>
                                    <p class="desc">
                                        <?php foreach ($select_lecture_formats as $lecture_format): ?>
                                            <?= htmlspecialchars($lecture_format['name']) ?><br />
                                        <?php endforeach; ?>
                                    </p>
                                </li>
                            </ul>
                            <ul class="summary_list">
                                <li>
                                    <p class="term">参加費</p>
                                    <p class="desc">1回 <?= htmlspecialchars(number_format($event['participation_fee'])) ?>円
                                        <?php if (count($event['select_course']) > 1): ?>、全て受講の場合<?= htmlspecialchars(number_format($event['participation_fee'] * count($event['select_course']))) ?>円<?php endif; ?>
                                    </p>
                                </li>
                                <li>
                                    <p class="term">申込締切</p>
                                    <p class="desc">
                                        <?php if (count($event['select_course']) > 1): ?>＜全受講＞<?php endif; ?><?= (new DateTime($event['deadline']))->format('Y年m月d日'); ?>まで<br />
                                        ＜各回受講＞開催日の<?= htmlspecialchars($event['all_deadline']) ?>日前
                                    </p>
                                </li>
                            </ul>
                        </div>
                        <div class="access">
                            <h4 class="sub_ttl">アクセス</h4>
                            <div class="access_item01">
                                <?php if (empty($event['google_map'])): ?>
                                    <div class="map">
                                        <iframe
                                            src="<?= $event['google_map'] ?>"
                                            width="400"
                                            height="300"
                                            style="border: 0"
                                            allowfullscreen=""
                                            loading="lazy"
                                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                                    </div>
                                <?php endif ?>
                                <div class="sent">
                                    <p>
                                        【会場】<?= htmlspecialchars($event['venue_name']) ?>
                                    </p>
                                    <p>
                                        【交通アクセス】<br />
                                        <?= nl2br($event['access']) ?>
                                    </p>
                                </div>
                            </div>
                            <div class="access_item02">
                                <ul class="info inner_s sent">
                                    <li>【主催】<?= htmlspecialchars($event['sponsor']) ?></li>
                                    <li>【協力】<?= htmlspecialchars($event['cooperation']) ?></li>
                                    <li>【共催】<?= htmlspecialchars($event['co_host']) ?></li>
                                    <li>【企画】<?= htmlspecialchars($event['plan']) ?></li>
                                    <li>【後援】<?= htmlspecialchars($event['sponsorship']) ?></li>
                                    <li>【お問い合わせ窓口】○○○○</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <a href="#" class="btn btn_red arrow btn_entry">全日程を一括で申し込む</a>
                    <p class="detail_txt">
                        ※単発でお申込みされる場合は開催日程の各講義内容下のボタンよりお申し込みください。
                    </p>
                    <div class="detail_item">
                        <h2 class="block_ttl">プログラム</h2>
                        <?php foreach ($event['select_course'] as $no => $course): ?>
                            <div class="program">
                                <h4 class="sub_ttl">【第<?= $no ?>講座】<?= (new DateTime($course['course_date']))->format('m月d日') . '（' . WEEKDAYS[(new DateTime($course['course_date']))->format('w')] . '）'; ?><?= htmlspecialchars($start_hour); ?>～<?= htmlspecialchars($end_hour); ?></p>
                                    <p class="sent">
                                        <?= $course['details'][0]['program'] ?>
                                    </p>
                                    <a href="#" class="btn btn_red arrow">この日程で申し込む</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="detail_item">
                        <h2 class="block_ttl">登壇者</h2>
                        <?php foreach ($select_tutor as $turor): ?>
                            <div class="speaker">
                                <div class="speaker_img"><img src="" alt="" /></div>
                                <div class="speaker_desc">
                                    <h4 class="sub_ttl"><?= htmlspecialchars($turor['name']) ?></h4>
                                    <p class="sent">
                                        <?= nl2br($turor['overview']) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="/custom/app/Views/contact/index.php" class="btn btn_contact btn_navy">このイベントを問い合わせる</a>
                </div>
            </div>
            <a href="index.php" class="btn btn_blue arrow box_bottom_btn">一覧へ戻る</a>
        </section>
        <!-- detail -->
    </div>
</main>

<div id="modal">
    <div class="modal_bg js_close"></div>
    <div class="modal_cont inner_s">
        <span class="cross js_close"></span>
        <img src="/custom/public/assets/img/event/event01.jpg" alt="" class="img" />
    </div>
</div>

<ul id="pankuzu" class="inner_l">
    <li><a href="/custom/app/Views/index.php">トップページ</a></li>
    <li><a href="/custom/app/Views/event/index.php">イベント一覧</a></li>
    <li>○○○○講座名が入ります</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>

<script>
    $(".big").on("click", function() {
        srlpos = $(window).scrollTop();
        let imgSrc = $(this).closest(".desc_img").find(".img img").attr("src");
        $("#modal .modal_cont .img").attr("src", imgSrc);
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