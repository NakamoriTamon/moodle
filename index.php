<?php include('/var/www/html/moodle/custom/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/app/Controllers/home/home_controller.php');
$now = new DateTime();
$now = $now->format('Ymd');
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/home.css" />

<!-- 一時的に検索フォームを非表示にします -->
<style>
    #search {
        display: none;
    }

    #juku {
        margin-top: 170px !important;
    }

    @media (max-width: 992px) {
        #juku {
            margin-top: 130px !important;
        }
    }
</style>
<main>
    <!-- pc版mv -->
    <section id="mv" class="mv-slider nosp">
        <div class="swiper-wrapper mv_img">
            <div class="swiper-slide"><img src="/custom/public/assets/img/home/mv.png" alt="画像1"></div>
            <div class="swiper-slide"><img src="/custom/public/assets/img/home/dummy_pc_mv02.png" alt="画像2"></div>
            <div class="swiper-slide"><img src="/custom/public/assets/img/home/dummy_pc_mv03.png" alt="画像3"></div>
            <div class="swiper-pagination"></div>
        </div>
        <p class="mv_scroll nosp">SCROLL</p>
    </section>
    <!-- sp版mv -->
    <section id="mv" class="mv-slider nopc">
        <div class="swiper-wrapper mv_img">
            <div class="swiper-slide"><img src="/custom/public/assets/img/home/mv-sp.png" alt="画像1"></div>
            <div class="swiper-slide"><img src="/custom/public/assets/img/home/dummy_sp_mv02.png" alt="画像2"></div>
            <div class="swiper-slide"><img src="/custom/public/assets/img/home/dummy_sp_mv01.png" alt="画像3"></div>
        </div>
        <div class="swiper-pagination"></div>
    </section>

    <!-- スライドショーではないmv -->
    <!-- <section id="mv" class="mv-slider">
        <img
            src=" /custom/public/assets/img/home/mv.png"
            alt="大阪大学 社会と未来、学びをつなぐ"
            class="mv_img nosp" />
        <img
            src="/custom/public/assets/img/home/mv-sp.png"
            alt="大阪大学 社会と未来、学びをつなぐ"
            class="mv_img nopc" />
            <p class="mv_scroll nosp">SCROLL</p>
    </section> -->
    <!-- mv -->

    <section id="about">
        <div class="about_cont inner_l">
            <h2 class="ttl_home">
                <span class="en">ABOUT</span>
                大阪大学<br />「知の広場」とは？
            </h2>
            <p class="sent">
                緒方洪庵が江戸末期の大坂に開いた蘭学塾「適塾」には、福沢諭吉をはじめ全国から多くの塾生が集い、ともに切磋琢磨しながら学びました。<br />
                大阪大学「知の広場」は、大阪大学の精神的源流である適塾のように、大阪大学が主催する市民向け講座や子ども向けイベントなど、多様な学びに触れることのできる開かれた広場です。<br />
                地域・社会と大学、そして研究者と市民をつなぐことで、社会との共創を目指します。<br />
                あなたも、大阪大学が拓く学びの世界へ。
            </p>
        </div>
        <div class="swiper about_swiper01">
            <ul class="swiper-wrapper">
                <li class="swiper-slide"><img src="/custom/public/assets/img/home/about_slide01.jpg" alt="" /></li>
                <li class="swiper-slide"><img src="/custom/public/assets/img/home/about_slide02.jpg" alt="" /></li>
                <li class="swiper-slide"><img src="/custom/public/assets/img/home/about_slide03.jpg" alt="" /></li>
                <li class="swiper-slide"><img src="/custom/public/assets/img/home/about_slide04.jpg" alt="" /></li>
                <li class="swiper-slide"><img src="/custom/public/assets/img/home/about_slide05.jpg" alt="" /></li>
                <li class="swiper-slide"><img src="/custom/public/assets/img/home/about_slide06.jpg" alt="" /></li>
            </ul>
        </div>
        <div class="swiper about_swiper02">
            <ul class="swiper-wrapper">
                <li class="swiper-slide"><img src="/custom/public/assets/img/home/deco_text.svg" alt="UOsaka" /></li>
                <li class="swiper-slide"><img src="/custom/public/assets/img/home/deco_text.svg" alt="UOsaka" /></li>
                <li class="swiper-slide"><img src="/custom/public/assets/img/home/deco_text.svg" alt="UOsaka" /></li>
                <li class="swiper-slide"><img src="/custom/public/assets/img/home/deco_text.svg" alt="UOsaka" /></li>
            </ul>
        </div>
    </section>
    <!-- about -->

    <section id="new">
        <div class="new_head inner_l">
            <h2 class="ttl_home">
                <span class="en">NEW ARRIVAL</span>
                新着イベント
            </h2>
            <a href="/custom/app/Views/event/index.php" class="btn btn_blue arrow nosp">全てのイベントを見る</a>
        </div>
        <div class="swiper new_swiper">
            <ul class="swiper-wrapper" id="event">
                <?php if (isset($events) && !empty($events)): ?>
                    <?php foreach ($events as $row): ?>
                        <li class="swiper-slide event_item">
                            <a href="event/detail.php?id=<?= htmlspecialchars($row['id']) ?>">
                                <figure class="img"><img src=<?= htmlspecialchars(empty($row['thumbnail_img']) ? DEFAULT_THUMBNAIL : $row['thumbnail_img']); ?> alt="<?= htmlspecialchars($row['name']); ?>" /></figure>
                                <div class="event_info">
                                    <ul class="event_status">
                                        <?php foreach (DEADLINE_LIST as $key => $status): ?>
                                            <?php if (($key == 1 || $key == 2) && $key == $row['deadline_status']): ?>
                                                <li class="active"><?= DEADLINE_LIST[$row['deadline_status']] ?></li>
                                            <?php elseif ($key == 3 && $key == $row['deadline_status']): ?>
                                                <li class="end"><?= DEADLINE_LIST[$row['deadline_status']] ?></li>
                                            <?php endif ?>
                                        <?php endforeach; ?>
                                    </ul>
                                    <p class="event_ttl"><?= htmlspecialchars($row['name']); ?></p>
                                    <div class="event_sched">
                                        <p class="term">開催日</p>
                                        <div class="date">
                                            <?php if ($row['event_kbn'] != EVERY_DAY_EVENT): ?>
                                                <?php foreach ($row['select_course'] as $no => $course): ?>
                                                    <?php $course_date = (new DateTime($course['course_date']))->format('Ymd'); ?>
                                                    <?php if ($course_date >= $now): ?>
                                                        <p class="dt0<?= $no ?>"><?php if (count($row['select_course']) > 1): ?><?= $no ?>回目：<?php endif ?><?= (new DateTime($course['course_date']))->format('Y年m月d日'); ?></p>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <?= (new DateTime($row['start_event_date']))->format('Y年m月d日'); ?>～<?= (new DateTime($row['end_event_date']))->format('Y年m月d日'); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <div class="new_btns">
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
        <a href="/custom/app/Views/event/index.php" class="btn btn_blue arrow nopc">全てのイベントを見る</a>
    </section>
    <!-- new -->

    <section id="search" class="inner_l">
        <h2 class="ttl_home">
            <span class="en">SEARCH</span>
            イベント検索
        </h2>
        <!-- とりあえずイベント一覧へ飛ばします！！ -->
        <form method="" action="/custom/app/Controllers/event/event_controller.php" id="search_cont" class="whitebox">
            <input type="hidden" name="action" value="index">
            <div class="inner_s">
                <ul class="search_list">
                    <li>
                        <p class="term">開催ステータス</p>
                        <div class="field f_check">
                            <?php foreach (DISPLAY_EVENT_STATUS_LIST as $key => $name): ?>
                                <label><input type="checkbox" id="event_status" name="event_status[]" value="<?= $key ?>" <?php if (isset($old_input['event_status'])) echo in_array($key, $old_input['event_status']) ? 'checked' : ''; ?> /><?= $name ?></label>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    <li>
                        <p class="term">
                            申し込み<br />
                            ステータス
                        </p>
                        <div class="field f_check">
                            <?php foreach (DISPLAY_DEADLINE_LIST as $key => $name): ?>
                                <label><input type="checkbox" id="deadline_status" name="deadline_status[]" value="<?= $key ?>" <?php if (isset($old_input['deadline_status'])) echo in_array($key, $old_input['deadline_status']) ? 'checked' : ''; ?> /><?= $name ?></label>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    <li>
                        <p class="term">イベント形式</p>
                        <div class="field f_check">
                            <?php foreach ($lectureFormats as $lectureFormat): ?>
                                <label><input type="checkbox" id="lecture_format_id" name="lecture_format_id[]" value="<?= htmlspecialchars($lectureFormat['id']) ?>" <?php if (isset($old_input['lecture_format_id'])) echo in_array($lectureFormat['id'], $old_input['lecture_format_id']) ? 'checked' : ''; ?> /><?= htmlspecialchars($lectureFormat['name']) ?></label>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    <li>
                        <p class="term">対象</p>
                        <div class="field f_select select">
                            <select name="target">
                                <option value="">選択してください</option>
                                <?php foreach ($targets as $target): ?>
                                    <option value="<?= htmlspecialchars($target['id']) ?>"
                                        <?= isSelected($target['id'], $eventData['target'] ?? null, $old_input['target'] ?? null) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($target['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </li>
                    <li>
                        <p class="term">キーワード</p>
                        <div class="field f_txt">
                            <input type="text" name="keyword" value="<?php if (isset($old_input['keyword'])) echo $old_input['keyword']; ?>" placeholder="検索するキーワードを入力" />
                        </div>
                    </li>
                    <li>
                        <p class="term">開催日時</p>
                        <div class="field f_date">
                            <p>
                                <input type="date" name="event_start_date" value="<?php if (isset($old_input['event_start_date'])) echo $old_input['event_start_date']; ?>" placeholder="年/月/日" />
                            </p>
                            <span>～</span>
                            <p>
                                <input type="date" name="event_end_date" value="<?php if (isset($old_input['event_end_date'])) echo $old_input['event_end_date']; ?>" placeholder="年/月/日" />
                            </p>
                        </div>
                    </li>
                    <li>
                        <p class="term">カテゴリー</p>
                        <div class="field" id="category">
                            <?php foreach ($categorys as $row): ?>
                                <div class="cat_item category0<?= htmlspecialchars($row['id']) ?>">
                                    <input type="checkbox" id="cat0<?= htmlspecialchars($row['id']) ?>" name="category[]" value="<?= htmlspecialchars($row['id']) ?>" <?php if (isset($old_input['category'])) echo in_array($row['id'], $old_input['category']) ? 'checked' : ''; ?> />
                                    <label for="cat0<?= htmlspecialchars($row['id']) ?>" class="cat_btn <?= empty($row['path']) ? 'justify_center' : ''; ?>">
                                        <?php if (!empty($row['path'])) { ?>
                                            <object
                                                type="image/svg+xml"
                                                data="<?= htmlspecialchars($row['path']) ?>"
                                                class="obj"></object>
                                        <?php } ?>
                                        <p class="txt"><?= htmlspecialchars($row['name']) ?></p>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </li>
                </ul>
                <div class="search_btn">
                    <button type="button" class="btn btn_clear" id="clear_button">クリア</button>
                    <button type="submit" class="btn btn_red">検索する</button>
                </div>
            </div>
        </form>
    </section>
    <!-- search -->

    <section id="juku">
        <div class="juku_cont inner_l">
            <div class="img"><img src="/custom/public/assets/img/home/juku.png" alt="" /></div>
            <div class="desc">
                <h2 class="ttl_home">
                    <span class="en">ABOUT TEKIJUKU</span>
                    適塾記念会について
                </h2>
                <p class="sent">
                    適塾記念会では、設立以来現在に至るまで、 市民の皆様とともに適塾と緒方洪庵の事績を研究・顕彰し、
                    その成果を会誌『適塾』や、適塾特別展示、適塾講座などの形で一般に公開しています。<br>
                    適塾記念会に入会いただくと適塾に何度でも参観できたり、会員のみが参加できるイベントに参加できたり等の特典があります。
                </p>
                <a href="/custom/app/Views/tekijuku/index.php" class="btn btn_blue arrow">詳しくはこちら</a>
            </div>
        </div>
    </section>
    <!-- juku -->
</main>

<?php if (empty($login_id)): ?>
    <a href="/custom/app/Views/user/index.php" id="mascot"><img src="/custom/public/assets/img/home/mascot.png" alt="" /></a>
<?php endif; ?>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php') ?>
<script src="/custom/public/assets/js/home.js"></script>
<script src="/custom/public/assets/js/search_input_reset.js"></script>
</body>

</html>