<?php
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/config.php');

// バリデーションエラー
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);
$id = $_GET['id'];

global $DB;
try {
	$categories = $DB->get_record('category', ['id' => $id]);
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
								<form method="POST" action="/custom/admin/app/Controllers/category/category_update_controller.php" enctype="multipart/form-data">
									<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
									<input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">

									<div class="mb-3">
										<label class="form-label me-2">カテゴリー名</label>
										<span class="badge bg-danger">必須</span>
										<input name="name" class="form-control" type="text"
											value="<?php echo htmlspecialchars($old_input['name'] ?? $categories->name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
										<?php if (!empty($errors['name'])): ?>
											<div class="text-danger mt-2">
												<?= htmlspecialchars($errors['name']); ?>
											</div>
										<?php endif; ?>
									</div>
									<div class="mb-3 uploadRow">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">講師画像</label>
											<span class="badge bg-danger">必須</span>
										</div>

										<input type="hidden" class="hiddenField" name="image_file" value="">
										<input type="file" class="form-control fileUpload" name="image_file" accept=".png,.jpeg,.jpg">
										<div class="fileInfo mt-2 d-none"></div>

										<input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($categories->path ?? '', ENT_QUOTES, 'UTF-8'); ?>">
										<?php if (!empty($errors['imagefile'])): ?>
											<div class="text-danger mt-2">
												<?= htmlspecialchars($errors['imagefile']); ?>
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
		function createFileLink(fileName, fileUrl) {
			const fileLinkContainer = document.createElement('div');
			fileLinkContainer.classList.add('fileInfoItem', 'd-flex', 'align-items-center', 'mb-2');

			const link = document.createElement('a');
			if (fileUrl.startsWith('blob:') || fileUrl.startsWith('http://') || fileUrl.startsWith('https://')) {
				link.href = fileUrl;
			} else if (fileUrl.charAt(0) === '/') {
				link.href = fileUrl;
			} else {
				link.href = '/uploads/category/' + fileUrl;
			}
			link.target = '_blank';
			link.classList.add('fileLink', 'd-flex', 'align-items-center', 'text-decoration-none');
			link.innerHTML = `
        <i data-feather="file-text" class="me-2"></i>
        <span class="fileName text-primary">${fileName}</span>
    `;
			fileLinkContainer.appendChild(link);
			// アイコンの置換はリンク生成後にまとめて実施
			feather.replace();
			return fileLinkContainer;
		}


		// 既存のファイルがあれば初期表示する
		(function initExistingFiles() {
			const existingCategory = <?= json_encode($categories, JSON_UNESCAPED_UNICODE) ?>;
			if (existingCategory.path) {
				const row = document.querySelector('.uploadRow');
				if (!row) return;
				const fileInfo = row.querySelector('.fileInfo');
				if (!fileInfo) return;
				// ファイルパスからファイル名のみを抽出
				const fileName = existingCategory.path.split('/').pop() || 'ファイル';
				const fileUrl = existingCategory.path;
				const linkElem = createFileLink(fileName, fileUrl);
				fileInfo.appendChild(linkElem);
				fileInfo.classList.remove('d-none');
				// すべてのリンク生成が完了した後にアイコンを置換
				feather.replace();
			}
		})();

		function handleFileChange(e) {
			const files = e.target.files;
			const row = e.target.closest('.uploadRow');
			const fileInfo = row.querySelector('.fileInfo');
			fileInfo.innerHTML = '';

			Array.from(files).forEach(file => {
				const allowedExtensions = ['.png', '.jpeg', '.jpg'];
				const fileExtension = file.name.slice(file.name.lastIndexOf('.')).toLowerCase();
				if (allowedExtensions.includes(fileExtension)) {
					const objectURL = URL.createObjectURL(file);
					const linkElement = createFileLink(file.name, objectURL);
					fileInfo.appendChild(linkElement);
				} else {
					alert('jpg, jpeg, pngファイルのみアップロードできます。');
				}
			});

			fileInfo.classList.toggle('d-none', files.length === 0);
			feather.replace();
		}

		$('.fileUpload').on('change', handleFileChange);
	});
</script>