<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/movie/movie_controller.php');
include($CFG->dirroot . '/custom/admin/app/Views/common/header.php');

$movie_conroller = new MovieController();
$result_list = $movie_conroller->index();

// バリデーションエラー
$errors   = $_SESSION['errors']   ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

// 情報取得
$category_list = $result_list['category_list'] ?? [];
$event_list = $result_list['event_list']  ?? [];
$movie = $result_list['movie'] ?? [];

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
									<form id="form" method="POST" action="/custom/admin/app/Views/event/movie.php" class="w-100">
										<div class="sp-block d-flex justify-content-between">
											<div class="mb-3 w-100">
												<label class="form-label" for="notyf-message">カテゴリー</label>
												<select name="category_id" class="form-control">
													<option value="">すべて</option>
													<?php foreach ($category_list as $category) { ?>
														<option value="<?= $category['id'] ?>" <?= isSelected($category['id'], $old_input['category_id'] ?? null, null) ? 'selected' : '' ?>>
															<?= htmlspecialchars($category['name']) ?>
														</option>
													<?php } ?>
												</select>
											</div>
											<div class="sp-ms-0 ms-3 mb-3 w-100">
												<label class="form-label" for="notyf-message">開催ステータス</label>
												<select name="event_status_id" class="form-control">
													<option value="">すべて</option>
													<?php foreach ($event_status_list as $key => $event_status) { ?>
														<option value=<?= $key ?> <?= isSelected($key, $old_input['event_status_id'] ?? null, null) ? 'selected' : '' ?>>
															<?= htmlspecialchars($event_status) ?></option>
													<?php } ?>
												</select>
											</div>
										</div>
										<div class="sp-block d-flex justify-content-between">
											<div class="mb-3 w-100">
												<label class="form-label" for="notyf-message">イベント名</label>
												<select name="event_id" class="form-control">
													<option value="" selected disabled>未選択</option>
													<?php foreach ($event_list as $event): ?>
														<option value="<?= htmlspecialchars($event['id'], ENT_QUOTES, 'UTF-8') ?>"
															<?= isSelected($event['id'], $old_input['event_id'] ?? null, null) ? 'selected' : '' ?>>
															<?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
														</option>
													<?php endforeach; ?>
												</select>
											</div>
											<div class="sp-ms-0 ms-3 mb-3 w-100">
												<label class="form-label" for="notyf-message">回数</label>
												<div class="d-flex align-items-center">
													<select name="course_no" class="form-control w-100" <?= $result_list['is_display'] ? 'disabled' : '' ?>>
														<option value="" selected disabled>未選択</option>
														<?php for ($i = 1; $i < 10; $i++) { ?>
															<option value=<?= $i ?>
																<?= isSelected($i, $old_input['course_no'] ?? null, null) ? 'selected' : '' ?>>
																<?= "第" . $i . "回" ?>
															</option>
														<? } ?>
													</select>
												</div>
											</div>
										</div>
										<div class="d-flex justify-content-end ms-auto">
											<button class="btn btn-primary me-0 search-button" type="submit" name="search" value="1">検索</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>

				<?php if ($result_list['is_display']): ?>
					<div class="col-12 col-lg-12">
						<div class="card">
							<div class="card-body">
								<form method="POST" action="/custom/admin/app/Controllers/movie/movie_upsert_controller.php" enctype="multipart/form-data">
									<div class="d-flex justify-content-end">
										<button type="submit" class="btn btn-primary">アップロード</button>
									</div>
									<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<input type="hidden" name="movie_id" value="<?= htmlspecialchars($_GET['movie_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<div class="movie-container mb-4">
										<input type="hidden" name="id" value="<?= !empty($movie->id) ? (int)$movie->id : 0 ?>">
										<h5><?= htmlspecialchars($movie->name, ENT_QUOTES, 'UTF-8') ?></h5>
										<div class="fields-container">
											<div>
												<div class="add_field mb-3 d-flex align-items-center">
													<input type="file" class="form-control" name="file" id="videoInput" accept="video/*">
												</div>
											</div>
											<img id="movie_img" src="" alt="サムネイル">
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</main>
		</div>
	</div>

	<script src="/custom/admin/public/js/app.js"></script>
	<script>
		$(document).ready(function() {
			$('select[name="category_id"]').change(function() {
				$("#form").submit();
			});
			$('select[name="event_status_id"]').change(function() {
				$("#form").submit();
			});
			$('select[name="event_id"]').change(function() {
				$("#form").submit();
			});
			$('select[name="course_no"]').change(function() {
				$("#form").submit();
			});

			// 動画の冒頭を画像で表示
			$('#videoInput').on('change', function(event) {
				const file = event.target.files[0];
				if (!file) return;

				if (!file.type.startsWith('video/')) {
					alert('動画ファイルを選択してください');
					$(this).val('');
					return;
				}

				const video = document.createElement('video');
				const fileURL = URL.createObjectURL(file);
				video.src = fileURL;
				video.muted = true;
				video.playsInline = true;
				video.preload = "metadata"; // 最小限のデータ取得

				$(video).on('loadeddata', function() {
					video.currentTime = 0; // 最初のフレームへ
				});

				$(video).on('seeked', function() {
					const canvas = document.createElement('canvas');
					const ctx = canvas.getContext('2d');

					canvas.width = video.videoWidth / 2; // 解像度を半分にして負荷軽減
					canvas.height = video.videoHeight / 2;

					ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

					$('#movie_img').attr('src', canvas.toDataURL('image/png')).show(); // サムネイル表示
					URL.revokeObjectURL(fileURL); // メモリ解放
				});
			});
		});
	</script>
</body>

</html>