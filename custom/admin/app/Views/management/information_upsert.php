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
$data_item = $result['data_item'] ?? [];
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
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
					<p class="title header-title ms-4 fs-4 fw-bold mb-0" id="heder">お知らせ登録</p>
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
							<p class="content_title p-3" id="title">お知らせ登録</p>
							<div class="form-wrapper">
								<form method="POST" enctype="multipart/form-data" action="/custom/admin/app/Controllers/management/information_upsert_controller.php" id="form">
									<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
									<input type="hidden" id="event_id" name="id" value="<?= $id ?? '' ?>">
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">件名</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input type="text" name="title" class="form-control" placeholder=""
											value="<?= htmlspecialchars(isSetValue($data_item['title'] ?? '', ($old_input['title'] ?? ''))) ?>" maxlength="225"/>
										<?php if (!empty($errors['title'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['title']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">本文</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<textarea name="body" id="editor" class="form-control" rows="7" max="10000"><?= htmlspecialchars(isSetValue($data_item['body'] ?? '', ($old_input['body'] ?? ''))) ?></textarea>
										<?php if (!empty($errors['body'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['body']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex">
											<label class="me-2">掲載開始日時　※未入力の場合、即時掲載されます。</label>
										</div>
										<div class="d-flex align-items-center">
											<input name="publish_start_date" class="w-50 me-3 form-control" type="date" value="<?= htmlspecialchars(isSetValue($data_item['publish_start_date'] ?? '', ($old_input['publish_start_date'] ?? ''))) ?>"/>
											<input name="publish_start_hour" class="w-25 me-2 form-control" type="number" min=1 max=24 value="<?= htmlspecialchars(isSetValue($data_item['publish_start_hour'] ?? '', ($old_input['publish_start_hour'] ?? ''))) ?>" />時
											<input type="hidden" name="publish_start_at" id="publish_start_at" value="" />
										</div>
										<?php if (!empty($errors['scheduled_publish_start_at'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['scheduled_publish_start_at']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex">
											<label class="me-2">掲載終了日時　未入力の場合、該当のお知らせは継続して表示されます。</label>
										</div>
										<div class="d-flex align-items-center">
											<input name="publish_end_date" class="w-50 me-3 form-control" type="date" <?= $is_immediate ? 'disabled' : ''; ?> value="<?= htmlspecialchars(isSetValue($data_item['publish_end_date'] ?? '', ($old_input['publish_end_date'] ?? ''))) ?>"/>
											<input name="publish_end_hour" class="w-25 me-2 form-control" type="number" <?= $is_immediate ? 'disabled' : ''; ?> min=1 max=24 value="<?= htmlspecialchars(isSetValue($data_item['publish_end_hour'] ?? '', ($old_input['publish_end_hour'] ?? ''))) ?>" />時
											<input type="hidden" name="publish_end_at" id="publish_end_at" value="" />
										</div>
										<?php if (!empty($errors['scheduled_publish_end_at'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['scheduled_publish_end_at']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<?php if (!$start_event_flg): ?>
											<input type="submit" id="upsert_button" class="btn btn-primary" value="登録">
										<?php endif ?>
									</div>
									<!-- 即時公開モーダル -->
									<div class="modal fade" id="confirmInformationModal" tabindex="-1" aria-hidden="true">
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
													<?php if (!$start_event_flg): ?>
														<input type="submit" id="modal_upsert_button" class="btn btn-primary" value="はい">
													<?php endif ?>
												</div>
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</main>
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
		// 登録処理
   		let modalSubmit = false;
		$("#form").on("submit", function(e) {
			if (!modalSubmit) {
				// 日付と時を結合してhiddenにセット
				const startDate = $('[name="publish_start_date"]').val();
				let startHour = $('[name="publish_start_hour"]').val();
				startHour = startHour ? startHour.padStart(2, '0') : '00';
				const endDate = $('[name="publish_end_date"]').val();
				let endHour = $('[name="publish_end_hour"]').val();
				endHour = endHour ? endHour.padStart(2, '0') : '00';
				let startTimestamp = '';
				let endTimestamp = '';
				if (startDate) {
					startTimestamp = `${startDate} ${startHour}:00:00`;
				}
				if (endDate) {
					endTimestamp = `${endDate} ${endHour}:00:00`;
				}
				$('#publish_start_at').val(startTimestamp);
				$('#publish_end_at').val(endTimestamp);
				console.log('Start Timestamp:', startTimestamp);
				// 日付と時刻の両方が入力されていない場合、モーダルを表示
				if(!startDate && startHour === '00') {
					e.preventDefault();
					$('#confirmInformationModal').modal('show');
					return false;
				}
			}
			modalSubmit = false; // リセット
		});
		// モーダルの登録ボタンでフォーム送信
		$('#modal_upsert_button').on('click', function() {
			modalSubmit = true;
			$('#confirmInformationModal').modal('hide');
			$('#form').submit();
		});
		// 既存の情報がある場合、タイトルとボタンのテキストを変更	
		if(<?=json_encode($result['is_edit'])?>){
			$('#heder').text('お知らせ編集');
			$('#title').text('お知らせ編集');
			$('#upsert_button').val('更新');
		} 
	});
</script>