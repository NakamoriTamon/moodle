<?php
include('/var/www/html/moodle/custom/app/Views/common/header.php');
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Controllers/event/event_controller.php');

$event_statuses = EVENT_STATUS_LIST;
$old_input = $_SESSION['old_input'] ?? [];
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
            <form method="" action="" id="search_cont" class="whitebox">
                <div class="inner_s">
                    <ul class="search_list">
                        <li>
                            <p class="term">開催ステータス</p>
                            <div class="field f_check">
                                <label><input type="checkbox" id="" />開催前</label>
                                <label><input type="checkbox" id="" />開催中</label>
                                <label><input type="checkbox" id="" />開催終了</label>
                            </div>
                        </li>
                        <li>
                            <p class="term">
                                申し込み<br />
                                ステータス
                            </p>
                            <div class="field f_check">
                                <label><input type="checkbox" id="" />受付前</label>
                                <label><input type="checkbox" id="" />受付中</label>
                                <label><input type="checkbox" id="" />受付終了</label>
                                <label><input type="checkbox" id="" />申し込み不要</label>
                            </div>
                        </li>
                        <li>
                            <p class="term">イベント形式</p>
                            <div class="field f_check">
                                <label><input type="checkbox" id="" />会場（対面）</label>
                                <label><input type="checkbox" id="" />会場（オンデマンドあり）</label>
                                <label><input type="checkbox" id="" />オンライン</label>
                                <label><input type="checkbox" id="" />ハイブリッド</label>
                            </div>
                        </li>
                        <li>
                            <p class="term">対象</p>
                            <div class="field f_select select">
                                <select>
                                    <option value="" disabled selected>選択してください</option>
                                    <option></option>
                                </select>
                            </div>
                        </li>
                        <li>
                            <p class="term">キーワード</p>
                            <div class="field f_txt">
                                <input type="text" placeholder="検索するキーワードを入力" />
                            </div>
                        </li>
                        <li>
                            <p class="term">開催日時</p>
                            <div class="field f_date">
                                <p class="date_wrap">
                                    <input type="text" placeholder="年/月/日" />
                                </p>
                                <span>～</span>
                                <p class="date_wrap">
                                    <input type="text" placeholder="年/月/日" />
                                </p>
                            </div>
                        </li>
                        <li>
                            <p class="term">カテゴリー</p>
                            <div class="field" id="category">
                                <div class="cat_item category01">
                                    <input type="checkbox" id="cat01" name="" />
                                    <label for="cat01" class="cat_btn">
                                        <object
                                            type="image/svg+xml"
                                            data="/custom/public/assets/common/img/icon_cat01.svg"
                                            class="obj"></object>
                                        <p class="txt">医療・健康</p>
                                    </label>
                                </div>
                                <div class="cat_item category02">
                                    <input type="checkbox" id="cat02" name="" />
                                    <label for="cat02" class="cat_btn">
                                        <object
                                            type="image/svg+xml"
                                            data="/custom/public/assets/common/img/icon_cat02.svg"
                                            class="obj"></object>
                                        <p class="txt">科学・技術</p>
                                    </label>
                                </div>
                                <div class="cat_item category03">
                                    <input type="checkbox" id="cat03" name="" />
                                    <label for="cat03" class="cat_btn">
                                        <object
                                            type="image/svg+xml"
                                            data="/custom/public/assets/common/img/icon_cat03.svg"
                                            class="obj"></object>
                                        <p class="txt">生活・福祉</p>
                                    </label>
                                </div>
                                <div class="cat_item category04">
                                    <input type="checkbox" id="cat04" name="" />
                                    <label for="cat04" class="cat_btn">
                                        <object
                                            type="image/svg+xml"
                                            data="/custom/public/assets/common/img/icon_cat04.svg"
                                            class="obj"></object>
                                        <p class="txt">文化・芸術</p>
                                    </label>
                                </div>
                                <div class="cat_item category05">
                                    <input type="checkbox" id="cat05" name="" />
                                    <label for="cat05" class="cat_btn">
                                        <object
                                            type="image/svg+xml"
                                            data="/custom/public/assets/common/img/icon_cat05.svg"
                                            class="obj"></object>
                                        <p class="txt">社会・経済</p>
                                    </label>
                                </div>
                                <div class="cat_item category06">
                                    <input type="checkbox" id="cat06" name="" />
                                    <label for="cat06" class="cat_btn">
                                        <object
                                            type="image/svg+xml"
                                            data="/custom/public/assets/common/img/icon_cat06.svg"
                                            class="obj"></object>
                                        <p class="txt">自然・環境</p>
                                    </label>
                                </div>
                                <div class="cat_item category07">
                                    <input type="checkbox" id="cat07" name="" />
                                    <label for="cat07" class="cat_btn">
                                        <object
                                            type="image/svg+xml"
                                            data="/custom/public/assets/common/img/icon_cat07.svg"
                                            class="obj"></object>
                                        <p class="txt">子ども・教育</p>
                                    </label>
                                </div>
                                <div class="cat_item category08">
                                    <input type="checkbox" id="cat08" name="" />
                                    <label for="cat08" class="cat_btn">
                                        <object
                                            type="image/svg+xml"
                                            data="/custom/public/assets/common/img/icon_cat08.svg"
                                            class="obj"></object>
                                        <p class="txt">国際・言語</p>
                                    </label>
                                </div>
                                <div class="cat_item category09">
                                    <input type="checkbox" id="cat09" name="" />
                                    <label for="cat09" class="cat_btn">
                                        <p class="txt">その他</p>
                                    </label>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="search_btn">
                        <button type="button" class="btn btn_clear">クリア</button>
                        <button type="submit" class="btn btn_red">検索する</button>
                    </div>
                </div>
            </form>
        </section>
        <!-- search -->

        <section id="result">
            <h3 class="ttl_event">検索結果 <?= htmlspecialchars($totalCount) ?>件</h3>
            <ul class="result_list" id="event">
                <?php if(isset($events) && !empty($events)): ?>
                    <?php foreach($events as $row): ?>
                        <li class="event_item">
                            <a href="detail.php?id=<?= $row['id'] ?>">
                                <figure class="img"><img src="<?= htmlspecialchars($row['thumbnail_img']); ?>" alt="" /></figure>
                                <div class="event_info">
                                    <ul class="event_status">
                                        <li class="no"><?= htmlspecialchars($event_statuses[$row['event_status']]); ?></li>
                                        <li class="no"><?= htmlspecialchars(DEADLINE_LIST[$row['deadline_status']]); ?></li>
                                    </ul>
                                    <p class="event_ttl"><?= htmlspecialchars($row['name']); ?></p>
                                    <div class="event_sched">
                                        <p class="term">開催日</p>
                                        <div class="date">
                                            <?php foreach($row['select_course'] as $no => $course): ?>
                                                <p class="dt01"><?= $no ?>回目：<?= (new DateTime($course['course_date']))->format('Y年m月d日'); ?></p>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <ul class="event_category">
                                        <?php foreach($row['select_categorys'] as $select_category ): ?>
                                            <li><?php if(in_array($select_category ,array_column($categorys, 'id'))) ?><?= $categorys[array_search($select_category ,array_column($categorys, 'id'))]['name'] ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <ul class="result_pg">
                <?php if ($currentPage >= 1 && $totalCount > 10): ?>
                    <li><a href="?page=<?= intval($currentPage)-1 ?>" class="prev"></a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= ceil($totalCount/10); $i++): ?>
                    <li><a href="?page=<?= $i ?>" class="num <?= $i == $currentPage ? 'active' : '' ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <?php if ($currentPage >= 0 && $totalCount > 10): ?>
                    <li><a href="?page=<?= intval($currentPage)+1 ?>" class="next"></a></li>
                <?php endif; ?>
            </ul>
        </section>
        <!-- result -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>イベント一覧</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php') ?>