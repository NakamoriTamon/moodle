<?php
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/event/event_controller.php');
?>

<body id="upload" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<a class="sidebar-toggle js-sidebar-toggle">
					<i class="hamburger align-self-center"></i>
				</a>
				<div class="navbar-collapse collapse">
					<p class="header-title title ms-4 fs-4 fw-bold mb-0">講義動画アップロード</p>
					<ul class="navbar-nav navbar-align">
						<li class="nav-item dropdown">
							<a class="nav-icon pe-md-0 dropdown-toggle" href="#" data-bs-toggle="dropdown">
								<div class="fs-5 me-4 text-decoration-underline">システム管理者</div>
							</a>
							<div class="dropdown-menu dropdown-menu-end">
								<a class="dropdown-item" href="/custom/admin/app/Views/login/login.php">Log out</a>
							</div>
						</li>
					</ul>
				</div>
			</nav>

			<main class="content">
				<div class="col-12 col-lg-12">
					<div class="card">
						<div class="card-body p-0">
							<div class="card">
								<div class="card-body p-055 p-025 sp-block d-flex align-items-bottom">
									<div class="sp-w-100 w-50 me-4 sp-mb-3">
										<label class="form-label" for="notyf-message">イベント名</label>
										<select name="category_id" class="form-control w-100">
											<option value=1>未選択</option>
											<option value=1>タンパク質の精製技術の基礎</option>
											<option value=2>AIと機械学習の基礎講座</option>
											<option value=3>量子コンピュータ入門: 次世代計算技術の扉を開く</option>
											<option value=4>気候変動と持続可能なエネルギーソリューション</option>
											<option value=5>心理学で学ぶ意思決定と行動経済学</option>
										</select>
									</div>
									<div class="sp-w-100 sp-mb-4 w-25">
										<label class="form-label" for="notyf-message">回数</label>
										<div class="d-flex align-items-center">
											<!-- <span class="pe-2">第</span> -->
											<select name="category_id" class="form-control w-100">
												<option value=1>未選択</option>
												<option value=1>第1回</option>
												<option value=2>第2回</option>
												<option value=3>第3回</option>
												<option value=4>第4回</option>
												<option value=5>第5回</option>
												<option value=6>第6回</option>
												<option value=7>第7回</option>
												<option value=8>第8回</option>
												<option value=9>第9回</option>
											</select>
											<!-- <span class="ps-2 pe-2">回</span> -->
										</div>
									</div>
									<div class="d-flex align-items-end ms-auto">
										<button class="search-button btn btn-primary me-0 ms-auto search-button">検索</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="search-area col-12 col-lg-12">
					<div class="card">
						<div class="card-body p-0 p-055">
							<div class="d-flex justify-content-end mt-3">
								<button class="mt-3 mb-4 btn btn-primary mr-025" id="submit">アップロード</button>
							</div>
							<div class="card m-auto mb-5 mt-2 overflow-auto w-95">
								<div class="">
									<div class="mb-3">
										<label for="fileUpload" class="form-label">アップロードするファイルを選択</label>
										<div class="add_field mb-3 d-flex align-items-center">
											<input type="file" class="form-control" id="fileUpload" multiple accept="application/pdf">
											<a class="trash"><i class="ms-2 align-middle" data-feather="trash"></i></a>
										</div>
									</div>
									<div class="d-flex justify-content-end">
										<button class="btn btn-primary" id="add-btn">項目追加</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</main>
		</div>
	</div>

	<script src="/custom/admin/public/js/app.js"></script>
	<script>
		$('#add-btn').on('click', function() {
			const element = $(this).closest('.card').find('.add_field').first().clone(false);
			element.find('input').val('');
			$(this).parent().before(element);
		});
		$('.search-button').on('click', function(event) {
			$('.search-area').css('display', 'block');
		});
		$(document).on('click', '.trash', function() {
			const element = $(this).closest('.add_field');
			if ($('.add_field').length > 1) {
				element.remove();
			} else {
				element.find('input').val('');
			}
		});

		// モック用アラート　本番時は消してください
		$('#submit').on('click', function(event) {
			sessionStorage.setItem('alert', 'aaasss');
			setTimeout(() => {
				location.reload();
			}, 50);
		});
	</script>
</body>

</html>