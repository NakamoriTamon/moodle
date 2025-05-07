<?php
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/config.php');

$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);
$id = $_GET['id'];

global $DB;
try {
	$tutor = $DB->get_record('tutor', ['id' => $id]);
} catch (dml_exception $e) {
	$_SESSION['message_error'] = 'エラーが発生しました';
}
?>

<body id="event" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<a class="sidebar-toggle js-sidebar-toggle">
					<i class="hamburger align-self-center"></i>
				</a>
				<div class="navbar-collapse collapse">
					<p class="title header-title ms-4 fs-4 fw-bold mb-0">講師登録</p>
					<ul class="navbar-nav navbar-align">
						<li class="nav-item dropdown">
							<a class="nav-icon pe-md-0 dropdown-toggle" href="#" data-bs-toggle="dropdown">
								<div class="fs-5 me-4 text-decoration-underline"><?= htmlspecialchars($USER->name) ?></div>
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
							<p class="content_title p-3">講師登録</p>
							<div class="form-wrapper">
								<form method="POST" action="/custom/admin/app/Controllers/master/tutor/tutor_upsert_controller.php" enctype="multipart/form-data">
									<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
									<input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
									<input type="hidden" name="existing_path" value="<?= htmlspecialchars($tutor->path ?? '', ENT_QUOTES, 'UTF-8'); ?>">

									<div class=" mb-3">
										<label class="form-label">講師名</label>
										<span class="badge bg-danger">必須</span>
										<input name="name" class="form-control" type="text"
											value="<?= htmlspecialchars($old_input['name'] ?? $tutor->name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
										<?php if (!empty($errors['name'])): ?>
											<div class="text-danger mt-2">
												<?= htmlspecialchars($errors['name']); ?>
											</div>
										<?php endif; ?>
									</div>
									<div class="mb-3 uploadRow">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">講師画像</label>
										</div>
										<input type="file" class="form-control fileUpload" id="preview_img" name="path" multiple accept=".png,.jpeg,.jpg">
									</div>
									<div id="tutor-image-preview" class="mb-3">
										<!-- プレビュー画像がここに表示されます -->
									</div>
									<?php if (isset($tutor->path) && !empty($tutor->path)): ?>
										<div class="mb-3 tutor-fit-picture-area">
											<img id="tutor-fit-picture" class="fit-picture" src="<?= '/uploads/tutor/' . htmlspecialchars($tutor->path) ?>" />
										</div>
									<?php endif; ?>
									<button type="button" id="tutor-delete-btn" class="mb-3 delete-link delete_btn btn btn-danger ms-auto me-0">削除</button>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">講師概要</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<textarea name="overview" class="form-control" rows="5"><?php echo htmlspecialchars($old_input['overview'] ?? $tutor->overview ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
										<?php if (!empty($errors['overview'])): ?>
											<div class="text-danger mt-2">
												<?= htmlspecialchars($errors['overview']); ?>
											</div>
										<?php endif; ?>
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
<script>
	$(document).ready(function() {
		const path = <?= json_encode($tutor->path) ?>;
		if (path) {
			$('.delete_btn').css('display', 'inline-block');
		}
		$('#preview_img').on('change', function(event) {
			$('#tutor-image-preview').html('');
			$('.tutor-fit-picture-area').css('display', 'none');
			$('input[name="existing_path"]').val('');
			const file = event.target.files[0];

			// ファイルが画像であるか確認
			if (file && file.type.match('image.*')) {
				const reader = new FileReader(); // FileReader のインスタンスを作成

				// ファイルの読み込みが完了したらプレビューを表示
				reader.onload = function(e) {
					$('#tutor-image-preview').html(
						`<img src="${e.target.result}" alt="プレビュー" class="preview">`
					);
				};

				reader.readAsDataURL(file); // ファイルを読み込む
			} else {
				alert('画像ファイルを選択してください。');
				$('#tutor-image-preview').html(''); // プレビューをクリア
			}
		});
		$('.delete_btn').on('click', function(event) {
			$('#tutor-image-preview').html('');
			$('.tutor-fit-picture-area').css('display', 'none');
			$('input[name="existing_path"]').val('');
			$('input[name="path"]').val('');
			$('.delete_btn').css('display', 'none');
		});
	});
</script>