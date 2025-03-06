<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/event.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="REGISTERED EVENT">申し込みイベント</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="result" class="register">
            <p class="sent">
                お申込みされたイベントの資料DLと動画の閲覧が一定期間可能です。<br />
                各項目よりご利用いただけます。
            </p>
            <ul class="result_list" id="event">
                <li class="event_item">
                    <figure class="img"><img src="/custom/public/assets/img/event/event01.jpg" alt="" /></figure>
                    <div class="event_info">
                        <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                        <div class="event_btns">
                            <a href="" class="btn_pdf">PDF資料</a>
                            <a href="movie.php" class="btn_movie">イベント動画</a>
                            <a href="../survey/index.php" class="btn_answer">アンケートに回答する</a>
                        </div>
                        <p class="event_term">閲覧期間<span>～2000年00月00日まで</span></p>
                    </div>
                </li>
                <li class="event_item">
                    <figure class="img"><img src="/custom/public/assets/img/event/event02.jpg" alt="" /></figure>
                    <div class="event_info">
                        <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                        <div class="event_btns">
                            <a href="" class="btn_pdf">PDF資料</a>
                            <a href="movie.php" class="btn_movie">イベント動画</a>
                            <a href="../survey/index.php" class="btn_answer">アンケートに回答する</a>
                        </div>
                        <p class="event_term">閲覧期間<span>～2000年00月00日まで</span></p>
                    </div>
                </li>
                <li class="event_item">
                    <figure class="img"><img src="/custom/public/assets/img/event/event03.jpg" alt="" /></figure>
                    <div class="event_info">
                        <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                        <div class="event_btns">
                            <a href="" class="btn_pdf">PDF資料</a>
                            <a href="movie.php" class="btn_movie">イベント動画</a>
                            <a href="../survey/index.php" class="btn_answer">アンケートに回答する</a>
                        </div>
                        <p class="event_term">閲覧期間<span>～2000年00月00日まで</span></p>
                    </div>
                </li>
            </ul>
            <a href="/custom/app/Views/mypage/index.php" class="btn btn_blue arrow box_bottom_btn">前へ戻る</a>
            <!-- 多い場合ページネーション必要ですか？必要であれば復活してください -->
            <ul class="result_pg">
                <li><a href="" class="prev"></a></li>
                <li><a href="" class="num active">1</a></li>
                <li><a href="" class="num">2</a></li>
                <li><a href="" class="num">3</a></li>
                <li><a href="" class="num">4</a></li>
                <li><a href="" class="num">5</a></li>
                <li><a href="" class="next"></a></li>
            </ul>
        </section>
        <!-- result -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>申し込みイベント</li>
</ul>

<div id="modal" class="modal_pdf">
    <div class="modal_bg js_close"></div>
    <div class="modal_cont inner_m">
        <span class="cross js_close"></span>
        <ul class="pdf_list">
            <li>
                <p class="name">PDF1</p>
                <a id="open-pdf" href="" class="btn btn_navy pdf">PDF資料</a>
            </li>
            <li>
                <p class="name">PDF1</p>
                <a id="open-pdf" href="" class="btn btn_navy pdf">PDF資料</a>
            </li>
            <li>
                <p class="name">PDF1</p>
                <a id="open-pdf" href="" class="btn btn_navy pdf">PDF資料</a>
            </li>
            <li>
                <p class="name">PDF1</p>
                <a id="open-pdf" href="" class="btn btn_navy pdf">PDF資料</a>
            </li>
            <li>
                <p class="name">PDF1</p>
                <a id="open-pdf" href="" class="btn btn_navy pdf">PDF資料</a>
            </li>
            <li>
                <p class="name">PDF1</p>
                <a id="open-pdf" href="" class="btn btn_navy pdf">PDF資料</a>
            </li>
            <li>
                <p class="name">PDF1</p>
                <a id="open-pdf" href="" class="btn btn_navy pdf">PDF資料</a>
            </li>
            <li>
                <p class="name">PDF1</p>
                <a id="open-pdf" href="" class="btn btn_navy pdf">PDF資料</a>
            </li>
            <li>
                <p class="name">PDF1</p>
                <a id="open-pdf" href="" class="btn btn_navy pdf">PDF資料</a>
            </li>
        </ul>
    </div>
</div>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>

<script>
    $(".btn_pdf").on("click", function() {
        srlpos = $(window).scrollTop();
        $("#modal").fadeIn();
        $("body").addClass("modal_fix").css({
            top: -srlpos
        });
        return false;
    });
    $(".js_close").on("click", function() {
        $("#modal").fadeOut();
        $("body").removeClass("modal_fix").css({
            top: 0
        });
        $(window).scrollTop(srlpos);
    });
    $(document).ready(function() {
        $("#open-pdf").on("click", function() {
            const pdfUrl = "/uploads/material/sample.pdf";
            window.open(`/custom/app/Views/event/pdf.php?file=${encodeURIComponent(pdfUrl)}`, "_blank");
        });
    });
</script>