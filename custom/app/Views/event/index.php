<?php
include('/var/www/html/moodle/custom/app/Views/common/header.php');
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Controllers/event/event_controller.php');


$now = new DateTime();
$now = $now->format('Ymd');

$event_statuses = EVENT_STATUS_LIST;
$old_input = $_SESSION['old_input'] ?? [];
unset($SESSION->formdata);
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/event.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="EVENT LIST">イベント一覧</h2>
    </section>
    <!-- heading -->
    <div class="inner_l">
        <section id="search">
            <h3 class="ttl_event">絞り込み検索</h3>
            <form method="" action="/custom/app/Controllers/event/event_controller.php" id="search_cont" class="whitebox">
                <input type="hidden" name="action" value="index">
                <div class="inner_s">
                    <ul class="search_list">
                        <li>
                            <p class="term">開催ステータス</p>
                            <div class="field f_check">
                                <?php foreach (EVENT_STATUS_SELECTS as $key => $name): ?>
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
                                <?php foreach (DEADLINE_SELECTS as $key => $name): ?>
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
                                    <option value="" disabled selected>選択してください</option>
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

        <section id="result">
            <h3 class="ttl_event">検索結果 <?= htmlspecialchars($totalCount) ?>件</h3>
            <ul class="result_list" id="event">
                <?php if (isset($events) && !empty($events)): ?>
                    <?php foreach ($events as $row): ?>
                        <li class="<?php echo ($row['is_top'] === 1) ? 'rec ' : ''; ?>event_item">
                            <a href="/custom/app/Views/event/detail.php?id=<?= $row['id'] ?>">
                                <figure class="img"><img src="<?= htmlspecialchars(empty($row['thumbnail_img']) ? DEFAULT_THUMBNAIL : $row['thumbnail_img']); ?>" alt="" /></figure>
                                <div class="event_info">
                                    <ul class="event_status">
                                        <li class="<?php if($row['event_status'] <= 2): ?>active<?php else: ?>no<?php endif ?>"><?= htmlspecialchars(EVENT_STATUS_LIST[$event['event_status']]); ?></li>
                                        <?php foreach (DEADLINE_LIST as $key => $status): ?>
                                            <?php if ($key != DEADLINE_END && $key == $row['deadline_status']): ?>
                                                <li class="active"><?= DEADLINE_LIST[$row['deadline_status']] ?></li>
                                            <?php elseif ($key == DEADLINE_END && $key == $row['deadline_status']): ?>
                                                <li class="end"><?= DEADLINE_LIST[$row['deadline_status']] ?></li>
                                            <?php endif ?>
                                        <?php endforeach; ?>
                                    </ul>
                                    <p class="event_ttl"><?= htmlspecialchars($row['name']); ?></p>
                                    <div class="event_sched">
                                        <?php if ($row['event_status'] <= 2): ?>
                                            <p class="term">開催日</p>
                                            <div class="date">
                                                <?php if ($row['event_kbn'] != 3): ?>
                                                    <?php foreach ($row['select_course'] as $no => $course): ?>
                                                        <p class="dt01"><?php if (count($row['select_course']) > 1): ?><?= $no ?>回目：<?php endif ?><?= (new DateTime($course['course_date']))->format('Y年m月d日'); ?></p>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <?= (new DateTime($row['start_event_date']))->format('Y年m月d日'); ?>～<?= (new DateTime($row['end_event_date']))->format('Y年m月d日'); ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="date">
                                                <p class="dt01">全日程終了</p>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                    <ul class="event_category">
                                        <?php foreach ($row['select_categorys'] as $select_category): ?>
                                            <li><?php if (in_array($select_category, array_column($categorys, 'id'))) ?><?= $categorys[array_search($select_category, array_column($categorys, 'id'))]['name'] ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <ul class="result_pg">
                <?php if ($currentPage >= 1 && $totalCount > $perPage): ?>
                    <li><a href="?page=<?= intval($currentPage) - 1 ?>&<?= $queryString ?>" class="prev"></a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= ceil($totalCount / $perPage); $i++): ?>
                    <li><a href="?page=<?= $i ?>&<?= $queryString ?>" class="num <?= $i == $currentPage ? 'active' : '' ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <?php if ($currentPage >= 0 && $totalCount > $perPage): ?>
                    <li><a href="?page=<?= intval($currentPage) + 1 ?>&<?= $queryString ?>" class="next"></a></li>
                <?php endif; ?>
            </ul>
        </section>
        <!-- result -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="/custom/app/Views/index.php">トップページ</a></li>
    <li>イベント一覧</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php') ?>
<script src="/custom/public/assets/js/search_input_reset.js"></script>