<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');

// 都道府県リスト　将来的にconstにまとめます( モックなので )
$prefectures = [
	'北海道',
	'青森県',
	'岩手県',
	'宮城県',
	'秋田県',
	'山形県',
	'福島県',
	'茨城県',
	'栃木県',
	'群馬県',
	'埼玉県',
	'千葉県',
	'東京都',
	'神奈川県',
	'新潟県',
	'富山県',
	'石川県',
	'福井県',
	'山梨県',
	'長野県',
	'岐阜県',
	'静岡県',
	'愛知県',
	'三重県',
	'滋賀県',
	'京都府',
	'大阪府',
	'兵庫県',
	'奈良県',
	'和歌山県',
	'鳥取県',
	'島根県',
	'岡山県',
	'広島県',
	'山口県',
	'徳島県',
	'香川県',
	'愛媛県',
	'高知県',
	'福岡県',
	'佐賀県',
	'長崎県',
	'熊本県',
	'大分県',
	'宮崎県',
	'鹿児島県',
	'沖縄県'
];
?>

<body id="event" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<div class="navbar-collapse collapse">
					<p class="title ms-4 fs-4 fw-bold mb-0">管理者申込 ( 現金ユーザー登録 )</p>
					<ul class="navbar-nav navbar-align">
						<li class="nav-item dropdown">
							<a class="nav-icon pe-md-0 dropdown-toggle" href="#" data-bs-toggle="dropdown">
								<div class="fs-5 me-4">システム管理者</div>
							</a>
							<div class="dropdown-menu dropdown-menu-end">
								<a class="dropdown-item" href="login.php">Log out</a>
							</div>
						</li>
					</ul>
				</div>
			</nav>

			<main class="content">
				<div class="col-12 col-lg-12">
					<div class="card">
						<div class="card-body p-0">
							<p class="content_title p-3">管理者申込 ( 現金ユーザー登録 )</p>
							<div class="form-wrapper">
								<form method="POST" action="/custom/admin/app/Controllers/EventUpsertController.php">
									<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
									<input type="hidden" name="id" value="<?php echo $_GET['id'] ?>">
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">氏名</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input type="text" name="name" class="form-control" placeholder="">
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">フリガナ</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input type="text" name="kana" class="form-control" placeholder="">
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">お住まいの都道府県</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<select name="category_id" class="form-control mb-3">
											<option value=1 selected="">選択してください</option>
											<?php foreach ($prefectures as $prefecture): ?>
												<option value="<?= htmlspecialchars($prefecture, ENT_QUOTES, 'UTF-8') ?>">
													<?= htmlspecialchars($prefecture, ENT_QUOTES, 'UTF-8') ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">メールアドレス</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input type="email" name="email" class="form-control" placeholder="">
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">メールアドレス( 確認 )</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input type="email" name="confirm-email" class="form-control" placeholder="" onpaste="return false" oncopy="return false">
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">パスワード</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input type="password" name="password" class="form-control" placeholder="">
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">パスワード( 確認 )</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input type="password" name="confirm-email" class="form-control" placeholder="" onpaste="return false" oncopy="return false">
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">電話番号( 携帯もしくは自宅 )</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input type="tel" name="tel_number" class="form-control" placeholder="" onpaste="return false" oncopy="return false">
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">生年月日</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input name="birth_day" class="form-control" type="date">
									</div>
									<div class="mb-3">
										<label class="form-label">備考</label>
										<textarea name="note" class=" form-control" rows="5"></textarea>
									</div>
									<button type="submit" class="btn btn-primary">登録</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</main>
		</div>
	</div>
	<script src="/custom/admin/public/js/app.js"></script>
</body>

</html>