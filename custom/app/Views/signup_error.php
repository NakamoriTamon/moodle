<?php include('/var/www/html/moodle/custom/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/app/Controllers/home/home_controller.php');
$now = new DateTime();
$now = $now->format('Ymd');
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/home.css" />
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css">
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/error.css">

<main id="subpage">
  <section id="heading" class="inner_l">
    <h2 class="head_ttl" data-en="PAGE NOT FOUND">
      既に本登録が完了しております。
    </h2>
  </section>
  <!-- heading -->
  <div class="inner_l">
    <section id="form" class="event entry">
      <form method="POST" action="confirm.php" class="whitebox form_cont">
        <div class="inner_m">
          <div class="form_btn">
            <p class="list_label">
              申し訳ございません。<br />
              既に本登録が完了しております。<br />
              ログインページへお戻りください。
            </p>
          </div>
          <div class="form_btn">
            <a href="/custom/app/Views/login/index.php" class="btn btn_gray">戻る</a>
          </div>
        </div>
      </form>
    </section>
  </div>
</main>

<ul id="pankuzu" class="inner_l">
  <li><a href="/custom/app/Views/index.php">トップページ</a></li>
</ul>

<?php if (empty($login_id)): ?>
  <a href="/custom/app/Views/user/index.php" id="mascot"><img src="/custom/public/assets/img/home/mascot.png" alt="" /></a>
<?php endif; ?>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php') ?>
</body>

</html>