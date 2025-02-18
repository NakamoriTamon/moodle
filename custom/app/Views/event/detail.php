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
                        <?php foreach($event['select_course'] as $no => $course): ?>
                            <p class="dt01"><?= $no ?>回目：<?= (new DateTime($course['course_date']))->format('Y年m月d日'); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="category" id="category">
                    <?php foreach($select_categorys as $select_category ): ?>
                        <div class="cat_item category01 active">
                            <div class="cat_btn">
                                <object
                                    type="image/svg+xml"
                                    data="/custom/public/assets/common/img/icon_cat0<?= htmlspecialchars($select_category['id']) ?>.svg"
                                    class="obj"></object>
                                <p class="txt"><?php if(in_array($select_category ,array_column($categorys, 'id'))) ?><?= $categorys[array_search($select_category ,array_column($categorys, 'id'))]['name'] ?></p>
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
                            <?= htmlspecialchars($event['program']); ?>
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
                                    <?php foreach($select_lecture_formats as $lecture_format): ?>
                                        <?= htmlspecialchars($lecture_format['name']) ?><br />
                                    <?php endforeach; ?>
                                    </p>
                                </li>
                            </ul>
                            <ul class="summary_list">
                                <li>
                                    <p class="term">参加費</p>
                                    <p class="desc">1回 <?= htmlspecialchars(number_format($event['participation_fee'])) ?>円
                                    <?php if(count($event['select_course']) > 1): ?>、全て受講の場合<?= htmlspecialchars(number_format($event['participation_fee'] * count($event['select_course']))) ?>円<?php endif; ?>
                                    </p>
                                </li>
                                <li>
                                    <p class="term">申込締切</p>
                                    <p class="desc">
                                    <?php if(count($event['select_course']) > 1): ?>＜全受講＞<?php endif; ?><?= (new DateTime($event['deadline']))->format('Y年m月d日'); ?>まで<br />
                                        ＜各回受講＞開催日の〇日前
                                    </p>
                                </li>
                                <!-- <li>
                                    <p class="term">アーカイブ<br />配信</p>
                                    <p class="desc">
                                        下記URLよりご覧いただけます。<br />https://www....................................
                                    </p>
                                </li> -->
                            </ul>
                        </div>
                        <div class="access">
                            <h4 class="sub_ttl">アクセス</h4>
                            <div class="access_item01">
                                <div class="map">
                                    <iframe
                                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3275.361043285226!2d135.52189267574983!3d34.822013872876795!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6000fb60db96a653%3A0xf584717b6ac7c9ef!2z5aSn6Ziq5aSn5a2m!5e0!3m2!1sja!2sjp!4v1736923427647!5m2!1sja!2sjp"
                                        width="400"
                                        height="300"
                                        style="border: 0"
                                        allowfullscreen=""
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                                </div>
                                <div class="sent">
                                    <p>
                                        【会場】大阪大学適塾記念センター 講堂<br />
                                        〒000-0000　○○○○○○○○○○
                                    </p>
                                    <p>
                                        【交通アクセス】<br />
                                        ○○○駅下車徒歩○○分<br />○○○駅下車徒歩○○分
                                    </p>
                                </div>
                            </div>
                            <div class="access_item02">
                                <ul class="info inner_s sent">
                                    <li>【主催】大阪大学適塾記念センター</li>
                                    <li>【協力】株式会社PHP研究所　大阪大学生活協同組合</li>
                                    <li>【共催】</li>
                                    <li>【企画】○○○○</li>
                                    <li>【後援】○○○○</li>
                                    <li>【お問い合わせ窓口】○○○○</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <a href="apply.php" class="btn btn_red arrow btn_entry">全日程を一括で申し込む</a>
                    <p class="detail_txt">
                        ※単発でお申込みされる場合は開催日程の各講義内容下のボタンよりお申し込みください。
                    </p>
                    <div class="detail_item">
                        <h2 class="block_ttl">プログラム</h2>
                        <div class="program">
                            <h4 class="sub_ttl">【第1講座】10月21日（月）18：30～20：00</h4>
                            <p class="sent">
                                池田 光穂（大阪大学名誉教授）「子どもたちに『感染症と人類の歴史』を伝える」<br />
                                いまからもう３年も前になりますが、『感染症と人類の歴史』（文：おおつかのりこ、絵：合田洋介）という三冊本（『移動と広がり』『治療と医療』『公衆衛生』）を文研出版（2021）が出版されました。ぼくは監修者として、この本の編集過程で原稿をチェックしたり、執筆者や編集者からの問い合わせに応じたり、情報を追加検索したりと、出版を見守ってきました。そしてまた同時に、まえがきやあとがきで、子どもたちにメッセージを発信してきました。みんなの努力もあって、これらの本は、幸い学校図書推薦書にも認定されました。感染症の対策は現場での対応のほかに、パニックや予防接種拒否をおこさないために大人たちへの教育も必要ですが、理性的行動ができ、患者である市民の権利を理解できる次世代の子ども育成も課題になるでしょう。ぼくが監修のプロセスで学んだ「子どもたちに感染症の歴史を伝える」ことの意味について考えます。
                            </p>
                            <a href="apply.php" class="btn btn_red arrow">この日程で申し込む</a>
                        </div>
                        <div class="program">
                            <h4 class="sub_ttl">【第2講座】10月24日（木）18：30～20：00</h4>
                            <p class="sent">
                                祖父江　友孝（内閣府食品安全委員会委員）「食品から摂取されるカドミウムの健康影響評価」<br />
                                カドミウムは、土壌中、水中及び大気中の自然界に広く分布し、環境由来のカドミウムは穀類、野菜類、海産物などの食品中に様々な濃度で蓄積します。特に、我が国では全国各地に鉱床や鉱山が多く存在していたことから、カドミウムばく露レベルは海外に比べて高い傾向にあります。内閣府食品安全委員会は、食品の安全性を確保するために科学的にリスク評価を行う機関です。今回の適塾講座では、2024年に改訂された健康影響評価書の内容を概説します。
                            </p>
                            <a href="apply.php" class="btn btn_red arrow">この日程で申し込む</a>
                        </div>
                        <div class="program">
                            <h4 class="sub_ttl">【第3講座】11月27日（水）18：30～20：00</h4>
                            <p class="sent">
                                住村　欣範（大阪大学グローバルイニシアティブ機構教授）「人間と動物がつくりだす環境と健康」<br />
                                人間と動物の関係は、人類が生まれて以来ずっと変わってきました。それは、人間が動物を捕食するという関係だけでなく、人間にとって、感染症をもたらす主要な「環境」でもありました。新型コロナウイルスのパンデミックで経験したように、動物由来感染症は、新興再興感染症の主要な部分として拡大しています。人間と動物の存在と活動が相互につくり出す「環境」と、それがもたらす健康への影響について、東南アジアや日本の事例から考えてみたいと思います。
                            </p>
                            <a href="apply.php" class="btn btn_red arrow">この日程で申し込む</a>
                        </div>
                    </div>
                    <div class="detail_item">
                        <h2 class="block_ttl">登壇者</h2>
                        <div class="speaker">
                            <div class="speaker_img"><img src="" alt="" /></div>
                            <div class="speaker_desc">
                                <h4 class="sub_ttl">海堂　尊（医師・作家）</h4>
                                <p class="sent">
                                    1961年、千葉県生まれ。千葉大学<br />
                                    医学部大学院修了。医学博士。福井県立大学客員教授。2006年、『チーム・バチスタの栄光』で第4回「このミステリーがすごい！」大賞を
                                    受賞し作家デビュー。同作は映画化・ドラマ化され、大ヒットとなる。「桜宮サーガ」と呼ばれる作品群は累計1700万部を超え、映像化作品も多数。Ai（オートプシー・イメージング＝死亡時画像診断）の概念の提唱者で、死因究明問題にコミットし続けている。
                                </p>
                            </div>
                        </div>
                        <div class="speaker">
                            <div class="speaker_img"><img src="" alt="" /></div>
                            <div class="speaker_desc">
                                <h4 class="sub_ttl">川上　潤（緒方洪庵記念財団 専務理事・事務長）</h4>
                                <p class="sent">
                                    1957年、熊本県生まれ。桃山学院大学卒業。緒方洪庵記念<br />
                                    財団専務理事・事務長。緒方洪庵記念財団除痘館記念資料室学芸員。適塾記念会顧
                                    問。共著に緒方洪庵記念財団除痘館記念資料<br />
                                    室編『緒方洪庵の「除痘館記録」を読み解く』<br />
                                    （思文閣出版、2015年）、同『財団創設70周年記念誌　白神』（2024年）がある。
                                </p>
                            </div>
                        </div>
                    </div>
                    <a href="" class="btn btn_contact btn_navy">このイベントを問い合わせる</a>
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
    <li><a href="/custom/public/index.php">トップページ</a></li>
    <li><a href="index.html">イベント一覧</a></li>
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