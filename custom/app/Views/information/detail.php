<?php
include('/var/www/html/moodle/custom/app/Views/common/header.php');
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Controllers/information/information_controller.php');

$id = $_GET['id'] ?? 1; // ダミー
$information_controller = new InformationController();
$result = $information_controller->detail($id);
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/information.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="INFORMATION DETAIL">お知らせ詳細</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="information">
            <section id="information_list">
                <div class="information_list_block event">
                    <div class="information_list">
                        <div class="information_list-cont">
                            <h3 class="subttl"><?= htmlspecialchars($result['title']) ?></h3>
                            <p class="sent">
                                <?= $result['body'] ?>
                            </p>
                        </div>
                    </div>
            </section>
        </section>
        <!-- faq -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>お知らせ詳細</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>
</body>

</html>