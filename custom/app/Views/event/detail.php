<?php include('/var/www/html/moodle/custom/app/Views/common/header.php');
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Controllers/event/event_detail_controller.php');

$dateTime = DateTime::createFromFormat('H:i:s', $event['start_hour']);
$start_hour = $dateTime->format('H:i'); // "00:00"
$dateTime = DateTime::createFromFormat('H:i:s', $event['end_hour']);
$end_hour = $dateTime->format('H:i'); // "00:00"
$index = array_search($event['target'], array_column($targets, 'id'));
unset($_SESSION['errors'], $_SESSION['old_input'], $SESSION->formdata);
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
                    <li class="<?php if ($event['event_status'] <= 2): ?>active<?php else: ?>no<?php endif ?>"><?= htmlspecialchars(EVENT_STATUS_LIST[$event['event_status']]); ?></li>
                    <?php foreach (DEADLINE_LIST as $key => $status): ?>
                        <?php if ($key != DEADLINE_END && $key == $event['deadline_status']): ?>
                            <li class="active"><?= DEADLINE_LIST[$event['deadline_status']] ?></li>
                        <?php elseif ($key == DEADLINE_END && $key == $event['deadline_status']): ?>
                            <li class="end"><?= DEADLINE_LIST[$event['deadline_status']] ?></li>
                        <?php endif ?>
                    <?php endforeach; ?>
                </ul>
                <div class="event_sched">
                    <p class="term">開催日</p>
                    <div class="date">
                        <?php if ($event['event_kbn'] != 3): ?>
                            <?php foreach ($event['select_course'] as $no => $course): ?>
                                <p class="dt01"><?php if (count($event['select_course']) > 1): ?><?= $no ?>回目：<?php endif; ?><?= (new DateTime($course['course_date']))->format('Y年m月d日'); ?></p>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="dt01"><?= (new DateTime($event['start_event_date']))->format('Y年m月d日'); ?>～<?= (new DateTime($event['end_event_date']))->format('Y年m月d日'); ?></p>
                        <?php endif; ?>
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
                                <p class="txt"><?php if (in_array($select_category, array_column($categorys, 'id'))) ?><?= $select_category['name'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="desc_img">
                <div class="img"><img src="<?= htmlspecialchars(empty($event['thumbnail_img']) ? DEFAULT_THUMBNAIL : $event['thumbnail_img']); ?>" alt="" /></div>
                <p class="big">タップで拡大する</p>
            </div>
        </section>
        <!-- desc -->

        <section id="detail">
            <div class="whitebox">
                <div class="inner_m">
                    <?php if (!empty($event['description'])): ?>
                        <div class="detail_item">
                            <h2 class="block_ttl">内容</h2>
                            <p class="sent">
                                <?= nl2br(htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8')); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    <div class="detail_item">
                        <h2 class="block_ttl">概要</h2>
                        <div class="summary sent">
                            <ul class="summary_list">
                                <li>
                                    <p class="term">開催日</p>
                                    <p class="desc">
                                        <?php if ($event['event_kbn'] != 3): ?>
                                            <?= (new DateTime($event['event_date']))->format('Y年m月d日') . '（' . WEEKDAYS[(new DateTime($event['event_date']))->format('w')] . '）'; ?>
                                        <?php else: ?>
                                            <?= (new DateTime($event['start_event_date']))->format('Y年m月d日'); ?>～<?= (new DateTime($event['end_event_date']))->format('Y年m月d日'); ?>
                                        <?php endif; ?>
                                    </p>
                                </li>
                                <li>
                                    <p class="term">時間</p>
                                    <p class="desc"><?= htmlspecialchars($start_hour); ?>～<?= htmlspecialchars($end_hour); ?></p>
                                </li>
                                <li>
                                    <p class="term">対象</p>
                                    <?= $index !== false ? htmlspecialchars($targets[$index]['name']) : '対象なし' ?>
                                </li>
                                <li>
                                    <p class="term">定員</p>
                                    <p class="desc">
                                        <?php if ($event['capacity'] == 0): ?>
                                            定員無し
                                        <?php else: ?>
                                            <?= htmlspecialchars(number_format($event['capacity'])); ?>名
                                        <?php endif; ?>
                                    </p>
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

                                    <p class="desc">
                                        <?php if ($event['participation_fee'] < 1 && $event['single_participation_fee'] < 1): ?>
                                            無料
                                        <?php else: ?>
                                            <?php if (count($event['select_course']) > 1): ?>1回<?php endif; ?> <?php if ($event['single_participation_fee'] > 0): ?><?= htmlspecialchars(number_format($event['single_participation_fee'])) ?>円<?php else: ?>無料<?php endif; ?>
                                            <?php if (count($event['select_course']) > 1): ?>、全て受講の場合<?php if ($event['participation_fee'] > 0): ?><?= htmlspecialchars(number_format($event['participation_fee'])) ?>円<?php else: ?>無料<?php endif; ?><?php endif; ?>
                                        <?php endif; ?>
                                    </p>
                                </li>
                                <li>
                                    <p class="term">申込締切</p>
                                    <p class="desc">
                                        <?php if ($event['event_kbn'] != 3): ?>
                                            <?php if (count($event['select_course']) > 1): ?>＜全受講＞<?php endif; ?><?= (new DateTime($event['deadline']))->format('Y年m月d日'); ?>まで<br />
                                            <?php if ($event['event_kbn'] == 2): ?><?php if (count($event['select_course']) > 1): ?>＜各回受講＞<?php endif; ?>
                                            <?php if ($event['all_deadline'] == 0): ?>
                                                開催日の<?= htmlspecialchars($end_hour); ?>まで
                                            <?php else: ?>
                                                開催日の<?= htmlspecialchars($event['all_deadline']) ?>日前
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        各イベント開催日の終了時間まで
                                    <?php endif; ?>
                                    </p>
                                </li>
                            </ul>
                        </div>
                        <div class="access">
                            <?php if (!empty($event['google_map']) || !empty($event['venue_name']) || !empty($event['access'])): ?>
                                <h4 class="sub_ttl">アクセス</h4>
                                <div class="access_item01">
                                    <?php if (!empty($event['google_map'])): ?>
                                        <div class="map"><?= $event['google_map'] ?></div>
                                    <?php endif ?>
                                    <div class="sent">
                                        <?php if (!empty($event['venue_name'])): ?>
                                            <p>
                                                【会場】<?= htmlspecialchars($event['venue_name']) ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($event['access'])): ?>
                                            <p>
                                                【交通アクセス】<br />
                                                <?= nl2br(htmlspecialchars($event['access'], ENT_QUOTES, 'UTF-8')); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif ?>
                            <?php if (!empty($event['sponsor']) || !empty($event['cooperation']) || !empty($event['co_host']) || !empty($event['plan']) || !empty($event['sponsorship']) || (!empty($event['inquiry_mail']) && $event['event_status'] != EVENT_END)): ?>
                                <div class="access_item02">
                                    <ul class="info inner_s sent">
                                        <?php if (!empty($event['sponsor'])): ?><li>【主催】<?= htmlspecialchars($event['sponsor']) ?></li><?php endif; ?>
                                        <?php if (!empty($event['cooperation'])): ?><li>【協力】<?= htmlspecialchars($event['cooperation']) ?></li><?php endif; ?>
                                        <?php if (!empty($event['co_host'])): ?><li>【共催】<?= htmlspecialchars($event['co_host']) ?></li><?php endif; ?>
                                        <?php if (!empty($event['plan'])): ?><li>【企画】<?= htmlspecialchars($event['plan']) ?></li><?php endif; ?>
                                        <?php if (!empty($event['sponsorship'])): ?><li>【後援】<?= htmlspecialchars($event['sponsorship']) ?></li><?php endif; ?>
                                    </ul>
                                    <?php if (!empty($event['inquiry_mail']) && $event['event_status'] != EVENT_END): ?>
                                        <ul class="inquiry inner_s sent" style="display: flex;">
                                            <li>【お問い合わせ窓口】<a class="detail_page_a" href="/custom/app/Views/contact/index.php?event_id=<?= $event['id'] ?>"><?= htmlspecialchars($event['inquiry_mail']) ?></a></li>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($event['event_kbn'] == PLURAL_EVENT && DEADLINE_END != $event['set_event_deadline_status'] && count($event['select_course']) > 1 && $event['is_apply_btn'] === IS_APPLY_BTN['ENABLED']): ?>
                        <a href="apply.php?id=<?= htmlspecialchars($event['id']) ?>" class="btn btn_red arrow btn_entry">全日程を一括で申し込む</a>
                        <p class="detail_txt">
                            ※単発でお申込みされる場合は開催日程の各講義内容下のボタンよりお申し込みください。
                        </p>
                    <?php endif; ?>
                    <div class="detail_item">
                        <h2 class="block_ttl">プログラム</h2>
                        <?php foreach ($event['select_course'] as $no => $course): ?>
                            <div class="program">
                                <h4 class="sub_ttl">【<?php if (count($event['select_course']) > 1): ?>第<?= $no ?><?php endif; ?>講座】<?= (new DateTime($course['course_date']))->format('m月d日') . '（' . WEEKDAYS[(new DateTime($course['course_date']))->format('w')] . '）'; ?><?= htmlspecialchars($start_hour); ?>～<?= htmlspecialchars($end_hour); ?>
                                    <?php if (isset($course['close_date'])): ?>
                                        <span style="color: red;">(申込終了)</span>
                                    <?php endif; ?>
                            </div>
                            <?php foreach ($course['details'] as $key => $detail): ?>
                                <p class="sent">
                                    <?= htmlspecialchars($detail['name']) ?>
                                </p>
                                <p class="sent" <?php if (count($course['details']) != $key + 1): ?>style="margin-bottom: 40px;" <?php endif; ?>>
                                    <?= nl2br(htmlspecialchars($detail['program'], ENT_QUOTES, 'UTF-8')); ?>
                                </p>
                            <?php endforeach; ?>
                            <div class="program">
                                <?php if (!isset($course['close_date']) && $event['is_apply_btn'] === IS_APPLY_BTN['ENABLED']): ?>
                                    <a href="apply.php?id=<?= htmlspecialchars($event['id']) ?>&course_info_id=<?= htmlspecialchars($course['id']) ?>" class="btn btn_red arrow">この日程で申し込む</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($select_tutor) > 0 || count($tutor_names) > 0): ?>
                        <div class="detail_item">
                            <h2 class="block_ttl">登壇者</h2>
                            <?php foreach ($select_tutor as $turor): ?>
                                <div class="speaker">
                                    <div class="speaker_img"><img src="<?= htmlspecialchars(empty($turor['path']) ? DEFAULT_THUMBNAIL_2 : $turor['path']) ?>" alt="<?= htmlspecialchars($turor['name']) ?>" /></div>
                                    <div class="speaker_desc">
                                        <h4 class="sub_ttl"><?= htmlspecialchars($turor['name']) ?></h4>
                                        <p class="sent">
                                            <?= nl2br(htmlspecialchars($turor['overview'], ENT_QUOTES, 'UTF-8')); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php foreach ($tutor_names as $tutor_name): ?>
                                <div class="speaker">
                                    <div class="speaker_img"><img src="<?= DEFAULT_THUMBNAIL_2 ?>" alt="<?= htmlspecialchars($tutor_name) ?>" /></div>
                                    <div class="speaker_desc">
                                        <h4 class="sub_ttl"><?= htmlspecialchars($tutor_name) ?></h4>
                                        <p class="sent"></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <a href="/custom/app/Views/contact/index.php?event_id=<?= $event['id'] ?>" class="btn btn_contact btn_navy">このイベントを問い合わせる</a>
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

<ul id="pankuzu" class="inner_l scrollable-breadcrumb">
    <li><a href="/custom/app/Views/index.php">トップページ</a></li>
    <li><a href="/custom/app/Views/event/index.php">イベント一覧</a></li>
    <li><?= htmlspecialchars($event['name']); ?></li>
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