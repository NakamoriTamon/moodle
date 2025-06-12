<?php
include('/var/www/html/moodle/custom/app/Views/common/header.php');
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Controllers/information/information_controller.php');


$information_controller = new InformationController();
$result = $information_controller->index();

$information_list = $result['information_list'] ?? [];
$currentPage = $result['information_list'];
$totalCount = $result['totalCount'];
$perPage = $result['perPage'];
$queryString = $result['queryString'];


?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/event.css" />
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/home.css" />
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/information.css" />

<main id="subpage">
    <section id="information-heading" class="inner_l">
        <h2 class="head_ttl" data-en="INFORMATION LIST">お知らせ一覧</h2>
    </section>
    <!-- heading -->
    <div class="inner_l">
        <section id="information" class="info-section information_list">
            <div class="new_head inner_l">
                <div class="info-right">
                    <ul class="info-list">
                        <?php foreach ($information_list as $information) { ?>
                            <li>
                                <span class="info-date">
                                    <?= htmlspecialchars(
                                        (new DateTime($information['publish_start_at'] ?? $information['updated_at']))->format('Y/m/d')
                                    ) ?>
                                </span>
                                <a href="/custom/app/Views/information/detail.php?id=<?= htmlspecialchars($information['id']) ?>" class="info-title"><?= htmlspecialchars($information['title']) ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </section>
        <!-- search -->
        <section id="result">
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
    <li>お知らせ一覧</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php') ?>
<script src="/custom/public/assets/js/search_input_reset.js"></script>
<script src="./../../../public/assets/js/datepicker.js"></script>


</body>

</html>