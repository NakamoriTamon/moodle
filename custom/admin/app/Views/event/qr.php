<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/qr/qr_controller.php');
include($CFG->dirroot . '/custom/admin/app/Views/common/header.php');

$qr_conroller = new QrController();
$result_list = $qr_conroller->index();

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
				<!-- 通知表示コンテナ -->
				<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

				<!-- 検索フォーム -->
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
												<option value="" selected>回数を選択</option>
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
								<div class="text-center mb-2 mt-2" id="guidance_message">
									<p class="text-muted">イベント名と回数を選択するとQRカメラが起動します</p>
								</div>
							</form>
						</div>
					</div>
				</div>

				<!-- QRスキャナーエリア -->
				<div class="col-12 col-lg-12" id="qr_card" style="display: none;">
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
							<!-- 結果表示エリア -->
							<div id="scan-result" class="text-center mt-3 mb-2" style="display: none;"></div>
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
		$(document).ready(function() {
			// グローバル変数としてスキャナーインスタンスを保持
			let qrScannerInstance = null;
			let qrModuleLoaded = false;
			let videoElem = null;
			let cameraInitializing = false;

			// 1. カテゴリー選択時のイベント
			$('select[name="category_id"]').change(function() {
				const categoryId = $(this).val();
				// イベント選択をリセット
				$('select[name="event_id"]').html('<option value="" selected>未選択</option>');
				$('#course_no_select').prop('disabled', true).html('<option value="" selected>回数を選択</option>');
				$("#course_no").val('');

				// QRスキャナーを非表示・停止
				$("#qr_card").hide();
				stopQrScanner();

				// 修正: categoryIdが空（すべて選択）の場合も含めてイベントを取得
				// APIを使用してイベントリストを取得
				$.ajax({
					url: '/custom/admin/app/Controllers/qr/qr_controller.php',
					type: 'POST',
					data: {
						category_id: categoryId, // 空文字列の場合も送信
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
			});

			// 2. イベント選択時のイベント
			$('select[name="event_id"]').change(function() {
				const eventId = $(this).val();
				// 回数選択をリセット
				$('#course_no_select').html('<option value="" selected>回数を選択</option>');
				$("#course_no").val('');

				// QRスキャナーを非表示・停止
				$("#qr_card").hide();
				stopQrScanner();

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
			$('#course_no_select').on('change', function() {
				const courseNo = $(this).val();
				$("#course_no").val(courseNo);

				// まずQRスキャナーを停止
				stopQrScanner();

				if (courseNo) {
					// QRスキャナーを表示
					$("#qr_card").show();

					// QrScannerモジュールを先に読み込む
					loadQrScannerModule().then(() => {
						// 少し遅延させてからスキャナーを起動（DOMが完全に表示された後）
						setTimeout(() => {
							startQrScanner();
						}, 500);
					});
				} else {
					// QRスキャナーを非表示
					$("#qr_card").hide();
				}
			});

			// トースト通知を表示する関数
			function showToast(message, type = 'success') {
				// 既存のトーストを削除
				$('#toast-container').empty();

				// 色を設定
				let bgColor = type === 'success' ? '#d4edda' : '#f8d7da';
				let textColor = type === 'success' ? '#155724' : '#721c24';
				let borderColor = type === 'success' ? '#c3e6cb' : '#f5c6cb';

				// トースト要素を作成
				const toast = $(`
					<div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 300px; opacity: 1;">
						<div class="toast-body p-3" style="background-color: ${bgColor}; color: ${textColor}; border: 1px solid ${borderColor}; border-radius: 4px;">
							${message}
						</div>
					</div>
				`);

				// トーストをコンテナに追加
				$('#toast-container').append(toast);

				// スキャン結果エリアも更新
				showScanResult(message, type);

				// 2秒後に自動的に次のスキャンに移行
				setTimeout(() => {
					// トーストを削除
					toast.remove();

					// 結果表示を隠す
					$('#scan-result').hide();

					// スキャン表示を元に戻す
					$('.scan-text').text('Scanning...');
					$('.scan-text').css('color', '#00bcd4');
					$('.qr-frame .top-left, .qr-frame .top-right, .qr-frame .bottom-left, .qr-frame .bottom-right').css('border-color', '#00bcd4');

					// スキャナーを再開
					if (qrScannerInstance) {
						qrScannerInstance.start();
					}
				}, 2000);
			}

			// スキャン結果をQRカード内に表示
			function showScanResult(message, type = 'success') {
				const resultDiv = $('#scan-result');
				resultDiv.removeClass('text-success text-danger')
					.addClass(type === 'success' ? 'text-success' : 'text-danger')
					.html(`<strong>${message}</strong>`)
					.show();
			}

			// QrScannerモジュールを読み込む関数
			function loadQrScannerModule() {
				return new Promise((resolve) => {
					if (qrModuleLoaded) {
						resolve();
						return;
					}

					import("https://unpkg.com/qr-scanner@1.4.2/qr-scanner.min.js").then(module => {
						window.QrScanner = module.default;
						qrModuleLoaded = true;
						resolve();
					}).catch(error => {
						console.error('QrScannerモジュールの読み込みに失敗:', error);
						// エラーが発生しても解決
						resolve();
					});
				});
			}

			// QRスキャナーを停止する関数
			function stopQrScanner() {
				if (qrScannerInstance && typeof qrScannerInstance.stop === 'function') {
					try {
						qrScannerInstance.stop();
					} catch (error) {
						console.error('QRスキャナーの停止に失敗:', error);
					}
					qrScannerInstance = null;
				}

				// カメラ初期化中フラグをリセット
				cameraInitializing = false;
			}

			// 4. QRスキャナー起動関数
			function startQrScanner() {
				// 既に初期化中なら処理しない
				if (cameraInitializing) {
					return;
				}

				// カメラ初期化中フラグを設定
				cameraInitializing = true;

				// 既存のスキャナーを停止
				stopQrScanner();

				// ビデオ要素を取得
				videoElem = document.getElementById('qr-video');
				if (!videoElem) {
					console.error('qr-video要素が見つかりません');
					cameraInitializing = false;
					return;
				}

				// スキャナーが未定義の場合はエラー
				if (typeof QrScanner === 'undefined') {
					console.error('QrScannerモジュールが読み込まれていません');
					cameraInitializing = false;
					return;
				}

				try {
					// QRスキャナーの初期化
					qrScannerInstance = new QrScanner(
						videoElem,
						(result) => {
							if (!result || !result.data) {
								console.error('QRスキャン結果が無効です');
								return;
							}

							// スキャナーを一時停止
							qrScannerInstance.pause();

							// スキャン成功表示
							$('.scan-text').text('Success');
							$('.scan-text').css('color', '#249f2a');
							$('.qr-frame .top-left, .qr-frame .top-right, .qr-frame .bottom-left, .qr-frame .bottom-right').css('border-color', '#249f2a');

							// QRコード読み取り結果を処理
							processQrResult(result);
						}, {
							// スキャナーオプション
							highlightScanRegion: true,
							highlightCodeOutline: true,
							preferredCamera: 'environment', // 背面カメラを優先
						}
					);

					// カメラアクセス許可を取得
					qrScannerInstance.start().then(() => {
						cameraInitializing = false;
					}).catch(error => {
						console.error('QRスキャナーの起動に失敗:', error);
						alert('カメラへのアクセスができませんでした。\nブラウザの設定からカメラへのアクセスを許可してください。');
						cameraInitializing = false;
					});
				} catch (error) {
					console.error('QRスキャナーの初期化に失敗:', error);
					cameraInitializing = false;
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
							// 成功メッセージを表示
							showToast(response.message || '参加登録が完了しました', 'success');
						} else {
							// エラーメッセージを表示
							showToast(response.message || 'QRコードの処理に失敗しました', 'error');

							// スキャンエラー表示
							$('.scan-text').text('Error');
							$('.scan-text').css('color', '#dc3545');
							$('.qr-frame .top-left, .qr-frame .top-right, .qr-frame .bottom-left, .qr-frame .bottom-right').css('border-color', '#dc3545');
						}
					},
					error: function() {
						// エラーメッセージを表示
						showToast('サーバーとの通信に失敗しました', 'error');

						// スキャンエラー表示
						$('.scan-text').text('Error');
						$('.scan-text').css('color', '#dc3545');
						$('.qr-frame .top-left, .qr-frame .top-right, .qr-frame .bottom-left, .qr-frame .bottom-right').css('border-color', '#dc3545');
					}
				});
			}

			// 初期状態設定
			// QRスキャナーを非表示に
			$("#qr_card").hide();

			// 初期表示時は回数選択を非活性化（イベント名が選択されていない場合）
			if (!$('select[name="event_id"]').val()) {
				$('#course_no_select').prop('disabled', true);
			}

			// フォーム送信時のデフォルト動作を変更
			$('#form').on('submit', function(e) {
				e.preventDefault();
				$('#course_no').val($('#course_no_select').val());
			});

			// ブラウザのビューポート変更（回転など）時にスキャナーを再調整
			window.addEventListener('resize', () => {
				if (qrScannerInstance && $("#qr_card").is(":visible")) {
					// リサイズ中に再初期化は行わず、必要に応じてサイズを調整
					setTimeout(() => {
						qrScannerInstance.setInversionMode('original');
					}, 300);
				}
			});

			// QrScannerモジュールを事前にロードしておく
			loadQrScannerModule();
		});
	</script>
</body>

</html>