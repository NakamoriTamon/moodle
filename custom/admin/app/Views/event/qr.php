<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/qr/qr_controller.php');
include($CFG->dirroot . '/custom/admin/app/Views/common/header.php');

$qr_conroller = new QrController();
$result_list = $qr_conroller->index();

var_dump($_POST);
// バリデーションエラー
$errors   = $_SESSION['errors']   ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

// 情報取得
$category_list = $result_list['category_list'] ?? [];
$event_list = $result_list['event_list']  ?? [];
?>

<body id="qr" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative show">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<a class="sidebar-toggle js-sidebar-toggle">
					<i class="hamburger align-self-center"></i>
				</a>
				<div class="navbar-collapse collapse">
					<p class="header-title title ms-4 fs-4 fw-bold mb-0">QR読取</p>
					<ul class="navbar-nav navbar-align">
						<li class="nav-item dropdown">
							<a class="nav-icon pe-md-0 dropdown-toggle" href="#" data-bs-toggle="dropdown">
								<div class="fs-5 me-4">システム管理者</div>
							</a>
							<div class="dropdown-menu dropdown-menu-end">
								<a class="dropdown-item" href="/custom/admin/app/Views/login/login.php">Log out</a>
							</div>
						</li>
					</ul>
				</div>
			</nav>

			<main class="content">
				<div class="col-12 col-lg-12" id="search_card">
					<div class="card">
						<div class="card-body p-055 p-025 sp-block d-flex align-items-bottom">
							<form id="form" method="POST" action="/custom/admin/app/Views/event/qr.php" class="w-100">
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
											<?php foreach ($display_status_list as $key => $event_status) { ?>
												<option value="<?= $key ?>" <?= isSelected($key, $old_input['event_status_id'] ?? null, null) ? 'selected' : '' ?>>
													<?= htmlspecialchars($event_status) ?>
												</option>
											<?php } ?>
										</select>
									</div>
								</div>
								<div class="sp-block d-flex justify-content-between">
									<div class="mb-3 w-100">
										<label class="form-label" for="notyf-message">イベント名</label>
										<select name="event_id" class="form-control">
											<option value="" selected>未選択</option>
											<?php foreach ($event_list as $event): ?>
												<option value="<?= htmlspecialchars($event['id'], ENT_QUOTES, 'UTF-8') ?>"
													<?= isSelected($event['id'], $old_input['event_id'] ?? null, null) ? 'selected' : '' ?>>
													<?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="sp-ms-0 ms-3 mb-3 w-100">
										<label class="form-label" for="course_no_select">回数</label>
										<div class="d-flex align-items-center">
											<select id="course_no_select" class="form-control w-100" <?= $result_list['is_simple'] ? 'disabled' : '' ?>>
												<?php foreach ($course_number as $course_no) { ?>
													<option value="<?= $course_no ?>" <?= isSelected($course_no, $old_input['course_no'] ?? null, null) ? 'selected' : '' ?>>
														<?= "第" . htmlspecialchars($course_no) . "回" ?>
													</option>
												<?php } ?>
											</select>
											<input type="hidden" id="course_no" name="course_no" value="<?= htmlspecialchars($old_input['course_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
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
				<div class="col-12 col-lg-12" id="qr_card">
					<div class="card">
						<div class="card-body p-0 d-flex flex-column justify-content-center align-items-center" style="height: 100%;">
							<div class="qr-frame">
								<div class="video-container d-flex justify-content-center align-items-center">
									<video id="qr-video" autoplay loop>
										<source src="qr-code-video.mp4" type="video/mp4">
										Your browser does not support the video tag.
									</video>
								</div>
								<div class="top-left"></div>
								<div class="top-right"></div>
								<div class="bottom-left"></div>
								<div class="bottom-right"></div>
							</div>
							<p class="scan-text text-center mb-0 fs-3">Scanning...</p>
						</div>
					</div>
				</div>

				<!-- 登録完了モーダル -->
				<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="qrModalLabel">参加登録完了</h5>
							</div>
							<div class="modal-body">
								<p class="mt-2 mb-1 fw-bold">イベント名</p>
								<p>中之島芸術センター 演劇公演 「中の島デリバティブIII」</p>
								<p class="mb-1 fw-bold">ユーザー名</p>
								<p>高橋 望</p>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
							</div>
						</div>
					</div>
				</div>
			</main>
		</div>
	</div>

	<script src="/custom/admin/public/js/app.js"></script>
	<script>
		$(document).ready(function() {
			let selectedId;
			// 削除リンクがクリックされたとき
			$('.delete-link').on('click', function(event) {
				event.preventDefault();
				selectedId = $(this).data('id');
				$('#confirmDeleteModal').modal('show');
			});
			// モーダル内の削除ボタンがクリックされたとき
			$('#confirmDeleteButton').on('click', function() {
				$('#confirmDeleteModal').modal('hide');
				$(`.delete-link[data-id="${selectedId}"]`).closest('li').remove();
			});
		});
	</script>

	<script type="module">
		import QrScanner from "https://unpkg.com/qr-scanner@1.4.2/qr-scanner.min.js";

		const videoElem = document.getElementById('qr-video');
		let qrScanner = new QrScanner(videoElem, (result) => {
			console.log(result);
			videoElem.pause();
			$('.scan-text').text('Success');
			$('.scan-text').css('color', '#249f2a');
			$('.qr-frame .top-left, .qr-frame .top-right, .qr-frame .bottom-left, .qr-frame .bottom-right').css('border-color', '#249f2a');
			var modal = new bootstrap.Modal(document.getElementById('qrModal'));
			modal.show();
		});
		// qrScanner.start();

		$('#qrModal').on('hidden.bs.modal', function() {
			videoElem.play();
			$('.scan-text').text('Scannning...');
			$('.scan-text').css('color', '#00bcd4');
			$('.qr-frame .top-left, .qr-frame .top-right, .qr-frame .bottom-left, .qr-frame .bottom-right').css('border-color', '#00bcd4');
		});

		$(document).ready(function() {
			// 1. カテゴリー選択時のイベント
			$('select[name="category_id"]').change(function() {
				const categoryId = $(this).val();
				// イベント選択をリセット
				$('select[name="event_id"]').html('<option value="" selected>未選択</option>');
				$('#course_no_select').prop('disabled', true);
				$("#course_no").val('');

				// QRスキャナーを非表示
				$("#qr_card").hide();

				if (categoryId) {
					// APIを使用してイベントリストを取得
					$.ajax({
						url: '/custom/admin/app/Controllers/qr/qr_controller.php',
						type: 'POST',
						data: {
							category_id: categoryId,
							post_kbn: 'get_events_by_category'
						},
						dataType: 'json',
						success: function(response) {
							if (response.status === 'success' && response.events.length > 0) {
								// イベントオプションを追加
								let eventSelect = $('select[name="event_id"]');
								$.each(response.events, function(index, event) {
									eventSelect.append($('<option>', {
										value: event.id,
										text: event.name
									}));
								});
								// イベント選択を有効化
								eventSelect.prop('disabled', false);
							}
						},
						error: function() {
							alert('イベントデータの取得に失敗しました');
						}
					});
				}
			});

			// 2. イベント選択時のイベント
			$('select[name="event_id"]').change(function() {
				const eventId = $(this).val();
				// 回数選択をリセット
				$('#course_no_select').html('<option value="" selected>回数を選択</option>');
				$("#course_no").val('');

				// QRスキャナーを非表示
				$("#qr_card").hide();

				if (eventId) {
					// APIを使用して回数リストを取得
					$.ajax({
						url: '/custom/admin/app/Controllers/qr/qr_controller.php',
						type: 'POST',
						data: {
							event_id: eventId,
							post_kbn: 'get_course_numbers'
						},
						dataType: 'json',
						success: function(response) {
							if (response.status === 'success' && response.course_numbers.length > 0) {
								// 回数オプションを追加
								let courseSelect = $('#course_no_select');
								$.each(response.course_numbers, function(index, course) {
									courseSelect.append($('<option>', {
										value: course,
										text: "第" + course + "回"
									}));
								});
								// 回数選択を有効化
								courseSelect.prop('disabled', false);
							}
						},
						error: function() {
							alert('回数データの取得に失敗しました');
						}
					});
				}
			});

			// 3. 回数選択時のイベント
			$('#course_no_select').change(function() {
				const courseNo = $(this).val();
				$("#course_no").val(courseNo);

				if (courseNo) {
					// QRスキャナーを表示して起動
					$("#qr_card").show();
					startQrScanner();
				} else {
					// QRスキャナーを非表示
					$("#qr_card").hide();
				}
			});

			// 4. QRスキャナー起動関数
			function startQrScanner() {
				console.log('ここまでOK');
				// 念のため既存のスキャナーを停止
				if (window.qrScanner && typeof window.qrScanner.stop === 'function') {
					window.qrScanner.stop();
				}

				// QRスキャナーの初期化と起動
				const videoElem = document.getElementById('qr-video');

				// モジュールがすでに読み込まれている場合
				if (typeof QrScanner !== 'undefined') {
					initializeScanner();
				} else {
					// モジュールを動的に読み込む
					import("https://unpkg.com/qr-scanner@1.4.2/qr-scanner.min.js").then(module => {
						window.QrScanner = module.default;
						initializeScanner();
					});
				}

				function initializeScanner() {
					window.qrScanner = new QrScanner(videoElem, (result) => {
						console.log(result);
						videoElem.pause();
						$('.scan-text').text('Success');
						$('.scan-text').css('color', '#249f2a');
						$('.qr-frame .top-left, .qr-frame .top-right, .qr-frame .bottom-left, .qr-frame .bottom-right').css('border-color', '#249f2a');

						// QRコード読み取り結果を処理
						processQrResult(result);
					});

					window.qrScanner.start();
				}
			}

			// 5. QRコード読み取り結果の処理
			function processQrResult(result) {
				const qrData = result.data;
				const eventId = $('select[name="event_id"]').val();
				const courseNo = $("#course_no").val();

				// APIを使用して参加登録処理
				$.ajax({
					url: '/custom/admin/app/Controllers/qr/qr_controller.php',
					type: 'POST',
					data: {
						qr_data: qrData,
						event_id: eventId,
						course_no: courseNo,
						post_kbn: 'process_qr'
					},
					dataType: 'json',
					success: function(response) {
						if (response.status === 'success') {
							// モーダルにデータを設定
							$('#qrModalLabel').text('参加登録完了');
							$('#qrModal .modal-body').html(`
						<p class="mt-2 mb-1 fw-bold">イベント名</p>
						<p>${response.event_name}</p>
						<p class="mb-1 fw-bold">ユーザー名</p>
						<p>${response.user_name}</p>
					`);
						} else {
							// エラーメッセージを表示
							$('#qrModalLabel').text('エラー');
							$('#qrModal .modal-body').html(`
						<p class="mt-2 mb-1 text-danger">${response.message || 'QRコードの処理に失敗しました'}</p>
					`);
						}

						// モーダルを表示
						var modal = new bootstrap.Modal(document.getElementById('qrModal'));
						modal.show();
					},
					error: function() {
						// エラーメッセージを表示
						$('#qrModalLabel').text('エラー');
						$('#qrModal .modal-body').html(`
					<p class="mt-2 mb-1 text-danger">サーバーとの通信に失敗しました</p>
				`);

						// モーダルを表示
						var modal = new bootstrap.Modal(document.getElementById('qrModal'));
						modal.show();
					}
				});
			}

			// 6. モーダルを閉じた時の処理
			$('#qrModal').on('hidden.bs.modal', function() {
				const videoElem = document.getElementById('qr-video');
				videoElem.play();
				$('.scan-text').text('Scannning...');
				$('.scan-text').css('color', '#00bcd4');
				$('.qr-frame .top-left, .qr-frame .top-right, .qr-frame .bottom-left, .qr-frame .bottom-right').css('border-color', '#00bcd4');
			});

			// 7. 初期状態設定
			// 開催ステータス選択を非表示
			$('select[name="event_status_id"]').closest('div').hide();

			// 回数選択を無効化
			$('#course_no_select').prop('disabled', true);

			// QRスキャナーを非表示
			$("#qr_card").hide();

			// フォーム送信時のデフォルト動作を変更
			$('#form').on('submit', function(e) {
				e.preventDefault();
				$('#course_no').val($('#course_no_select').val());
				// 検索ボタンがクリックされた場合のみフォーム送信
				if (e.originalEvent && e.originalEvent.submitter && e.originalEvent.submitter.name === 'search') {
					this.submit();
				}
			});
		});
	</script>
</body>

</html>