<?php
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');

// バリデーションエラー
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);
$id = $_GET['id'];

// IDでカテゴリ詳細取得
$baseModel = new BaseModel();
$pdo = $baseModel->getPdo();
try {
	$pdo->beginTransaction();
	$stmt = $pdo->prepare("SELECT * FROM mdl_category WHERE id = :id");
	$stmt->execute(['id' => $id]);
	$categories = $stmt->fetch();
	$pdo->commit();
} catch (PDOException $e) {
	$pdo->rollBack();
	var_dump($e->getMessage());
	$_SESSION['message_error'] = 'エラーが発生しました: ' . $e->getMessage();
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
										<label class="form-label">カテゴリー名</label>
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
										<div class="custom-file-input">
											<button type="button" id="customButton" class="btn btn-secondary">ファイルを選択</button>
											<span id="customText">ファイルが選択されていません</span>
											<input id="thumbnailInput" name="imagefile" class="form-control d-none" type="file" accept=".png,.jpeg,.jpg">
										</div>
										<input type="hidden" id="PreviewInput" name="existing_image" value="<?php echo htmlspecialchars($categories['path'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
										<?php if (!empty($errors['imagefile'])): ?>
											<div class="text-danger mt-2">
												<?= htmlspecialchars($errors['imagefile']); ?>
											</div>
										<?php endif; ?>
									</div>
									<?php if (!empty($categories['path']) || !empty($old_input['existing_image'])): ?>
										<div id="thumbnailPreviewContainer" class="position-relative mb-3">
											<?php
											$preview_path = "/uploads/category/" . htmlspecialchars($categories['path'] ?? $old_input['existing_image'], ENT_QUOTES, 'UTF-8');
											?>
											<img
												id="thumbnailPreview"
												src="<?php echo htmlspecialchars($preview_path, ENT_QUOTES, 'UTF-8'); ?>"
												alt="Thumbnail Preview"
												style="width: 100%; max-width:497px; height: auto; object-fit: cover;" />
											<button
												type="button"
												id="removeThumbnailButton"
												class="btn btn-danger position-absolute"
												style="top: 10px; right: 10px;">
												×
											</button>
										</div>
									<?php else: ?>
										<div id="thumbnailPreviewContainer" class="position-relative mb-3 d-none">
											<img
												id="thumbnailPreview"
												src=""
												alt="Thumbnail Preview"
												style="width: 100%; max-width:497px; height: auto; object-fit: cover;" />
											<button
												type="button"
												id="removeThumbnailButton"
												class="btn btn-danger position-absolute"
												style="top: 10px; right: 10px;">
												×
											</button>
										</div>
									<?php endif; ?>
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
		function updateCustomText() {
			const previewSrc = $("#thumbnailPreview").attr("src");
			if (previewSrc && previewSrc !== "") {
				$("#customText").text("選択されています");
			} else {
				$("#customText").text("ファイルが選択されていません");
			}
		}

		updateCustomText();

		$("#customButton").on("click", function() {
			$("#thumbnailInput").click();
		});

		$("#thumbnailInput").on("change", function(event) {
			const file = event.target.files[0];
			if (file) {
				$("#customText").text(file.name);
				const reader = new FileReader();
				reader.onload = function(e) {
					$("#thumbnailPreview").attr("src", e.target.result);
					$("#thumbnailPreviewContainer").removeClass("d-none");
				};
				reader.readAsDataURL(file);
			} else {
				$("#customText").text("ファイルが選択されていません");
				$("#thumbnailPreview").attr("src", "");
				$("#thumbnailPreviewContainer").addClass("d-none");
			}
		});

		$("#removeThumbnailButton").on("click", function(event) {
			event.preventDefault();
			$("#thumbnailPreview").attr("src", "");
			$("#thumbnailPreviewContainer").addClass("d-none");
			$("#thumbnailInput").val("");
			$("#PreviewInput").val("");
			$("#customText").text("ファイルが選択されていません");
		});
	});
</script>