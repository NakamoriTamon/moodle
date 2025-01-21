<?php
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/event_controller.php');
$eventController = new EventController();
$events = $eventController->index();
?>

<body id="upload" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<div class="navbar-collapse collapse">
					<p class="title ms-4 fs-4 fw-bold mb-0">講義動画アップロード</p>
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
								<div class="card-body p-025 d-flex align-items-bottom">
									<div class="w-50 me-4">
										<label class="form-label" for="notyf-message">イベント名</label>
										<select name="category_id" class="form-control w-100">
											<option value=1>イベントA</option>
											<option value=2>イベントB</option>
											<option value=3>イベントC</option>
											<option value=4>イベントD</option>
											<option value=5>イベントE</option>
										</select>
									</div>
									<div class="w-25">
										<label class="form-label" for="notyf-message">回数</label>
										<div class="d-flex align-items-center">
											<!-- <span class="pe-2">第</span> -->
											<select name="category_id" class="form-control w-100">
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
										<button class="btn btn-primary me-0 ms-auto search-button">検索</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-12 col-lg-12">
					<div class="card">
						<div class="card-body p-0">
							<div class="">
								<div class="card">
									<div class="card-body p-025">
										<div class="d-flex justify-content-end">
											<button class="btn btn-primary" id="uploadBtn">アップロード</button>
										</div>
										<div class="mb-3">
											<label for="fileUpload" class="form-label">アップロードするファイルを選択</label>
											<input type="file" class="form-control" id="fileUpload" multiple accept="application/pdf">
										</div>
										<div class="d-flex justify-content-end">
											<button class="btn btn-primary" id="add-btn">追加</button>
										</div>
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
			const element = '<div class="mb-3"><input type="file" class="form-control" id = "fileUpload" multiple accept = "application/pdf" > </div>';
			$(this).parent().before(element);
		});
	</script>
</body>

</html>