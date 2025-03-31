<?php include('/var/www/html/moodle/custom/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/app/Controllers/home/home_controller.php');
$now = new DateTime();
$now = $now->format('Ymd');
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/home.css" />
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css">
<style>
  @media only screen and (max-width: 959px) {
    #footer {
      padding-bottom: 0px;
    }
    .inner_s,
    .inner_m,
    .inner_l .inner_m,
    .inner_l .inner_s,
    .inner_l .inner_m .inner_s {
      width: 80%;
      line-height: 1.7;
    }
  }
</style>
<main id="subpage">
  <section id="heading" class="inner_l">
    <h2 class="head_ttl" data-en="PAGE NOT FOUND">
      ご指定のページが見つかりません。
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
              お探しのページは存在しないか、削除された可能性があります。<br />
              URLが正しいかご確認の上、トップページへお戻りください。
            </p>
          </div>
          <div class="form_btn">
            <a href="/custom/app/Views/index.php" class="btn btn_gray"
              >戻る</a
            >
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