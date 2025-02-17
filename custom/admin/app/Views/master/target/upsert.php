<?php
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/target/target_controller.php');

$target_controller = new TargetController();
$target = $target_controller->edit($_GET['id']);

// バリデーションエラー
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);
?>

<body id="management" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<div class="navbar-collapse collapse">
					<a class="sidebar-toggle js-sidebar-toggle">
						<i class="hamburger align-self-center"></i>
					</a>
					<p class="title header-title ms-4 fs-4 fw-bold mb-0">対象者一覧</p>
					<p class="title mb-0"></p>
					<ul class="navbar-nav navbar-align">
						<li class="nav-item dropdown">
							<a class="nav-icon pe-md-0 dropdown-toggle d-flex" href="#" data-bs-toggle="dropdown">
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
							<p class="content_title p-3">対象者登録</p>
							<div class="form-wrapper">
								<form method="POST" action="/custom/admin/app/Controllers/target/target_update_controller.php" enctype="multipart/form-data">
									<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
									<input type="hidden" name="id" value="<?php echo htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8'); ?>">

									<div class="mb-3">
										<label class="form-label me-2">対象者名</label>
										<span class="badge bg-danger">必須</span>
										<input name="name" class="form-control" type="text"
											value="<?php echo htmlspecialchars($old_input['name'] ?? $target['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
										<?php if (!empty($errors['name'])): ?>
											<div class="text-danger mt-2">
												<?= htmlspecialchars($errors['name']); ?>
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