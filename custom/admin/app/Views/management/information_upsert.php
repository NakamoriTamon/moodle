<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/management/information_controller.php');
include($CFG->dirroot . '/custom/admin/app/Views/common/header.php');

// id を取得
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// コントローラに id を渡す
$information_controller = new InformationController();
$result = $information_controller->edit($id);

unset($_SESSION['errors'], $_SESSION['old_input']);
?>

<body id="management" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<a class="sidebar-toggle js-sidebar-toggle">
					<i class="hamburger align-self-center"></i>
				</a>
				<div class="navbar-collapse collapse">
					<p class="title header-title ms-4 fs-4 fw-bold mb-0">お知らせ登録</p>
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
							<p class="content_title p-3">お知らせ登録</p>
							<div class="form-wrapper">
								<form method="POST" enctype="multipart/form-data">
									<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
									<input type="hidden" id="event_id" name="id" value="<?= $id ?? '' ?>">
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">件名</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input type="name" name="name" class="form-control" placeholder=""
											value="" />
										<?php if (!empty($errors['name'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['name']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">本文</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<textarea name="description" id="editor" class=" form-control" rows="7"><?= htmlspecialchars(isSetValue($eventData['description'] ?? '', ($old_input['description'] ?? ''))) ?></textarea>
										<?php if (!empty($errors['description'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['description']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex">
											<label class="me-2">掲載開始日時　※未入力の場合、即時掲載されます。</label>
										</div>
										<div class="d-flex align-items-center">
											<input name="sample" class="w-50 me-3 form-control" type="date" value="" />
											<input name="" class="w-25 me-2 form-control" type="number" min=1 max=24 value="" />時
										</div>
										<?php if (!empty($errors['scheduled_publish_at'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['scheduled_publish_at']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex">
											<label class="me-2">掲載終了日時　未入力の場合、該当のお知らせは継続して表示されます。</label>
										</div>
										<div class="d-flex align-items-center">
											<input name="" class="w-50 me-3 form-control" type="date" <?= $is_immediate ? 'disabled' : ''; ?> value="" />
											<input name="" class="w-25 me-2 form-control" type="number" <?= $is_immediate ? 'disabled' : ''; ?> min=1 max=24 value="" />時
										</div>
										<?php if (!empty($errors['scheduled_publish_at'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['scheduled_publish_at']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<?php if (!$start_event_flg): ?>
											<input type="button" id="upsert_button" class="btn btn-primary" value="登録">
										<?php endif ?>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</main>
		</div>
	</div>
	<!-- 即時公開モーダル -->
	<div class="modal fade" id="upsert_confirm_modal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="deleteConfirmModalLabel">即時公開</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body fw-bold fs-4 mt-3 mb-3">
					本お知らせは即時公開となります。<br class="pc-none">本当によろしいですか？
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
					<button type="button" id="confirm_upsert" class="btn btn-danger">はい</button>
				</div>
			</div>
		</div>
	</div>
</body>

</html>

<!-- リッチエディタ読み込み -->
<script src="/custom/admin/public/js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>

<script>
	$(document).ready(function() {
		$('#editor').summernote({
			height: 300,
			tabsize: 2,
			placeholder: 'ここに本文を入力してください…',
			toolbar: [
				['style', ['style']],
				['font', ['bold', 'underline', 'clear']],
				['color', ['color']],
				['para', ['ul', 'ol', 'paragraph']],
				// ['insert', ['link', 'video']], 無しの仕様で。必要ならYoutube + Google Driveの2つを許可か。iframeはやや危険
				['view', ['codeview']]
			]
		});
		$('#upsert_button').on('click', function() {
			<?php $_SESSION['message_success'] = '登録が完了しました'; ?>
			const val = $('input[name="sample"]').val();
			if (!val) {
				$('#upsert_confirm_modal').modal('show');
			} else {
				window.location.href = '/custom/admin/app/Views/management/information.php';
				// 実際はコントローラーで渡してください。
			}
		});
		$('#confirm_upsert').on('click', function() {
			window.location.href = '/custom/admin/app/Views/management/information.php';
		});
	});
</script>