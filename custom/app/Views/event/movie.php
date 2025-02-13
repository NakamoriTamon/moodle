<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/event.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="EVENT MOVIE">イベント動画</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="movie">
            <div class="movie_wrap">
                <video src="/custom/public/assets/movie/dummy.mov" controls loop playsinline muted></video>
            </div>
            <a href="register.php" class="btn btn_blue arrow box_bottom_btn">前へ戻る</a>
        </section>
        <!-- result -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li><a href="register.php">申し込みイベント</a></li>
    <li>イベント動画</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>