<?php
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
include('/var/www/html/moodle/custom/admin/app/Controllers/category/category_controller.php');

// バリデーションエラー
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

$category_controller = new CategoryController();
$categories = $category_controller->edit($_GET['id']);
?>

<body id="master" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<a class="sidebar-toggle js-sidebar-toggle">
					<i class="hamburger align-self-center"></i>
				</a>
				<div class="navbar-collapse collapse">
					<p class="title header-title ms-4 fs-4 fw-bold mb-0">カテゴリー登録</p>
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
							<p class="content_title p-3">カテゴリー登録</p>
							<div class="form-wrapper">
								<form method="POST" action="/custom/admin/app/Controllers/category/category_upsert_controller.php"
									enctype="multipart/form-data" onkeydown="if(event.key==='Enter') event.preventDefault();">
									<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
									<input type="hidden" name="id" value="<?php echo htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8'); ?>">
									<div class="mb-3">
										<label class="form-label me-2">カテゴリー名</label>
										<span class="badge bg-danger">必須</span>
										<input name="name" class="form-control" type="text"
											value="<?php echo htmlspecialchars($old_input['name'] ?? $categories['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
										<?php if (!empty($errors['name'])): ?>
											<div class="text-danger mt-2">
												<?= htmlspecialchars($errors['name']); ?>
											</div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">カテゴリー画像</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input type="file" class="form-control" name="image_file" accept="image/*">
										<?php if (!empty($errors['image_file'])): ?>
											<div class="text-danger mt-2">
												<?= htmlspecialchars($errors['image_file']); ?>
											</div>
										<?php endif; ?>
									</div>
									<div id="category_preview_container" class="position-relative d-none mb-3">
										<img id="category_preview"
											src="<?php if ($categories['path']) { ?> /uploads/category/<?= htmlspecialchars($categories['path']) ?><?php } ?>"
											alt="カテゴリー画像" />
										<button id="category_img_button" class="btn btn-danger position-absolute">×</button>
									</div>
									<input type="hidden" name="is_deleted">
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
		// 編集時は画像データを表示させる
		const src = $('#category_preview').attr('src');
		if (src && src.trim() !== '') {
			$('#category_preview_container').removeClass('d-none');
		}
		// 入力時画像データを表示させる
		$('input[name="image_file"]').on('change', function(event) {
			const file = event.target.files[0];
			if (file) {
				const reader = new FileReader();
				reader.onload = function(e) {
					$('#category_preview').attr('src', e.target.result);
					$('#category_preview_container').removeClass('d-none');
				};
				reader.readAsDataURL(file);
			}
		});
		// 画像削除ボタン押下
		$('#category_img_button').on('click', function() {
			event.preventDefault();
			$('#category_preview').attr('src', "");
			$('#category_preview_container').addClass('d-none');
			$('input[name="is_deleted"]').val('true');
			$('input[name="image_file"]').val("");
		});
	});
</script>