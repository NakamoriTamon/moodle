<?php
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/movie/movie_controller.php');
include($CFG->dirroot . '/custom/admin/app/Views/common/header.php');

use Dotenv\Dotenv;

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
$file_name = !empty($movie['file_name']) ? $movie['file_name'] : null;
$course_list = $result_list['course_list'] ?? [];

// 講義動画取得
$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();

$cloud_front_domain =  $_ENV['CLOUD_FRONT_DOMAIN'];
$expires = time() + 3600;
$key_pair_id = $_ENV['KEY_PAIR_ID'];
$private_key_path = $_ENV['PRIVATE_KEY_PATH'];

// カスタムポリシーJSON
$policy = json_encode([
	"Statement" => [[
		"Resource" => "$cloud_front_domain/*",
		"Condition" => [
			"DateLessThan" => ["AWS:EpochTime" => $expires]
		]
	]]
]);

// Base64-URLエンコード関数
function base64url_encode($input)
{
	return strtr(rtrim(base64_encode($input), '='), '+/', '-_');
}

// 秘密鍵読み込み
$privateKey = file_get_contents($private_key_path);

// 署名生成
openssl_sign($policy, $signature, $privateKey, OPENSSL_ALGO_SHA1);

// Cookie用の値にエンコード
$encodedPolicy = base64url_encode($policy);
$encodedSignature = base64url_encode($signature);

// Cookieを発行
setcookie('CloudFront-Policy', $encodedPolicy, [
	'expires' => $expires,
	'path' => '/',
	'secure' => true,
	'httponly' => true,
	'samesite' => 'None'
]);

setcookie('CloudFront-Signature', $encodedSignature, [
	'expires' => $expires,
	'path' => '/',
	'secure' => true,
	'httponly' => true,
	'samesite' => 'None'
]);

setcookie('CloudFront-Key-Pair-Id', $key_pair_id, [
	'expires' => $expires,
	'path' => '/',
	'secure' => true,
	'httponly' => true,
	'samesite' => 'None'
]);
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
							<div class="card">
								<div class="card-body p-055 p-025 sp-block d-flex align-items-bottom">
									<form id="form" method="POST" action="/custom/admin/app/Views/event/movie.php" class="w-100">
										<input type="hidden" name="id" value="<?= htmlspecialchars(isset($movie['id']) ? $movie['id'] : '') ?>">
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
													<select name="course_no" class="form-control w-100" <?= $result_list['is_single'] ? 'disabled' : '' ?>>
														<option value="" selected disabled>未選択</option>
														<?php foreach ($course_list as $course) { ?>
															<option value=<?= $course['no'] ?>
																<?= isSelected($course['no'], $old_input['course_no'] ?? null, null) ? 'selected' : '' ?>>
																<?= "第" . $course['no'] . "回" ?>
															</option>
														<?php } ?>
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
								<form id="upsert_form" method="POST" enctype="multipart/form-data">
									<div class="d-flex justify-content-end">
										<button type="button" id="upload_button" class="btn mb-2 btn-primary">アップロード</button>
									</div>
									<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<input type="hidden" name="id" value="<?= htmlspecialchars($movie['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<input type="hidden" name="s3_file_name" value="<?= htmlspecialchars($file_name ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<input type="hidden" name="course_info_id" value="<?= htmlspecialchars($result_list['course_info_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<input type="hidden" name="course_no" value="<?= htmlspecialchars($result_list['course_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<div class="movie-container mb-4">
										<input type="hidden" name="id" value="<?= !empty($movie->id) ? (int)$movie->id : 0 ?>">
										<h5><?= htmlspecialchars(!empty($movie->name) ? $movie->name : '', ENT_QUOTES, 'UTF-8') ?></h5>
										<div class="fields-container">
											<div>
												<div class="add_field mb-3 d-flex align-items-center">
													<input type="file" class="form-control" name="file" id="video_input" accept="video/*">
												</div>
											</div>
											<div class="d-flex flex-wrap align-items-end gap-3 w-100">
												<!-- サムネイル用画像 -->
												<div class="w-100">
													<img id="movie_img" src="" alt="サムネイル">
												</div>

												<!-- 動画タグ -->
												<video id="movie_video"
													controls
													oncontextmenu="return false;"
													disablePictureInPicture
													<?= $result_list["is_double_speed"] != 1 ? 'controlsList="nodownload, noplaybackrate"' : 'controlsList="nodownload"'; ?>
													style="width: 100%; max-width: 800px;">
													<p>動画再生をサポートしていないブラウザです。</p>
												</video>
												<?php if (!empty($file_name)) { ?>
													<button type="button" id="delete_video_btn" class="btn btn-danger mt-2" data-bs-toggle="modal" data-bs-target="#delete_confirm_modal">
														削除
													</button>
												<?php } ?>
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<!-- モーダルの構造 -->
				<div class="modal fade" id="upload_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
					<div class="modal-dialog modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-body text-center p-5 fs-4">
								<div class="d-flex flex-column align-items-center">
									<div>講義動画をアップロード中です</div>
									<div class="spinner-border text-primary mt-4 mb-3 me-1" role="status" style="width: 3rem; height: 3rem;">
										<span class="visually-hidden">Loading...</span>
									</div>
									<p id="percent" class="mt-2 fs-4 fw-bold">0%</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- 削除モーダル -->
				<div class="modal fade" id="delete_confirm_modal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="deleteConfirmModalLabel">削除確認</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<form method="POST" action="/custom/admin/app/Controllers/movie/movie_delete_controller.php">
								<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
								<input type="hidden" name="id" value="<?= htmlspecialchars($movie['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
								<input type="hidden" name="course_info_id" value="<?= htmlspecialchars($movie['course_info_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
								<input type="hidden" name="course_no" value="<?= htmlspecialchars($result_list['course_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
								<div class="modal-body">
									本当にこの動画を削除しますか？
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
									<button type="submit" id="confirm_delete" class="btn btn-danger">削除</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</main>
		</div>
	</div>
</body>

<script src="/custom/admin/public/js/app.js"></script>
<script>
	$(document).ready(function() {
		// PHPから動画ファイル名を取得
		const s3_file_name = $('input[name="s3_file_name"]').val();
		const is_double_speed = $('#movie-wrapper').data('is-double-speed');
		const video = document.getElementById('movie_video');
		const controls_area = document.getElementById('controls_area');
		if (s3_file_name) {
			const m3u8Url = "https://d1q5pewnweivby.cloudfront.net/" + s3_file_name;
			if (Hls.isSupported()) {
				const hls = new Hls({
					xhrSetup: function(xhr) {
						xhr.withCredentials = true;
					}
				});
				hls.loadSource(m3u8Url);
				hls.attachMedia(video);
				hls.on(Hls.Events.MANIFEST_PARSED, function() {
					$('#movie_video').css('display', 'block');
					// 倍速再生ボタン
					if (is_double_speed == 1) {
						const speedBtn = document.createElement('button');
						speedBtn.textContent = "1x";
						let speeds = [1, 1.25, 1.5, 2];
						let index = 0;
						speedBtn.addEventListener('click', () => {
							index = (index + 1) % speeds.length;
							video.playbackRate = speeds[index];
							speedBtn.textContent = speeds[index] + 'x';
						});
						speedBtn.style.marginLeft = '10px';
						controls_area.appendChild(speedBtn);
					}

				});
			} else if (video.canPlayType('application/vnd.apple.mpegurl')) {
				video.src = m3u8Url;
				if (is_double_speed == 1) {
					const speedBtn = document.createElement('button');
					speedBtn.textContent = "1x";
					let speeds = [1, 1.25, 1.5, 2];
					let index = 0;
					speedBtn.addEventListener('click', () => {
						index = (index + 1) % speeds.length;
						video.playbackRate = speeds[index];
						speedBtn.textContent = speeds[index] + 'x';
					});
					document.getElementById('controls_area').appendChild(speedBtn);
				}
				$('#movie_video').css('display', 'block');
			}
		}
		$('#movie_video').on('contextmenu', function(event) {
			event.preventDefault();
		});

		// 動画ソースへのクリックを無効化
		$('#movie_video_source').on('click', function(event) {
			event.preventDefault();
		});

		// 動画の右クリックメニューを無効化
		$('#movie_video').on('contextmenu', function(event) {
			event.preventDefault();
		});

		// マウスが動画に乗ったときの設定
		$('#movie_video').on('mouseenter', function() {
			// ダウンロードボタンを非表示にする
			$(this).prop('controlsList', 'nodownload');
			//倍速の設定
			if (is_double_speed != 1) {
				$(this).prop('controlsList', 'nodownload noplaybackrate'); // 倍速無効化
			} else {
				$(this).prop('controlsList', 'nodownload'); // ダウンロードだけ無効化
			}
		});

		// 再生時の設定
		$('#movie_video').on('play', function() {
			// 再生時に倍速の設定を変更
			if (is_double_speed != 1) {
				$(this).prop('controlsList', 'nodownload noplaybackrate'); // 倍速無効化
			} else {
				$(this).prop('controlsList', 'nodownload'); // ダウンロードだけ無効化
			}
		});

		// ファイル選択時の処理
		$('#video_input').on('change', function(event) {
			const file = event.target.files[0];
			$('#delete_video_btn').hide();
			if (!file) {
				$('#movie_video').hide();
				$('#movie_img').hide();
				return
			}

			$('#movie_video').hide();
			$('#movie_img').hide();

			// 動画ファイルでない場合はエラーメッセージ
			if (!file.type.startsWith('video/')) {
				alert('動画ファイルを選択してください');
				$(this).val('');
				return;
			}

			const video = document.createElement('video');
			const file_url = URL.createObjectURL(file);
			video.src = file_url;
			video.muted = true;
			video.playsInline = true;
			video.preload = "metadata"; // 最小限のデータ取得

			$(video).on('loadeddata', function() {
				// 最初のフレームへ
				video.currentTime = 0;
			});

			$(video).on('seeked', function() {
				const canvas = document.createElement('canvas');
				const ctx = canvas.getContext('2d');

				// 解像度を半分にして負荷軽減
				canvas.width = video.videoWidth / 2;
				canvas.height = video.videoHeight / 2;

				ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

				// サムネイル表示
				$('#movie_img').attr('src', canvas.toDataURL('image/png')).show();
				URL.revokeObjectURL(file_url);
			});
		});
		// 検索
		$('select[name="category_id"], select[name="event_status_id"], select[name="event_id"], select[name="course_no"]').change(function() {
			$("#form").submit();
		});

		// ▼ S3へ講義動画をアップロード
		$('#upload_button').on('click', async function() {
			const file_input = $('#video_input')[0];
			if (!file_input.files.length) {
				alert('動画を選択してください');
				return;
			}

			const modal = new bootstrap.Modal(document.getElementById('upload_modal'));
			modal.show();

			const file = file_input.files[0];
			const chunk_size = 50 * 1024 * 1024; // 50MBでチャンク
			const total_chunks = Math.ceil(file.size / chunk_size);

			// フォーム情報取得
			const course_info_id = $('#upsert_form').find('[name="course_info_id"]').val();
			const course_no = $('#upsert_form').find('[name="course_no"]').val();
			const csrf_token = $('#upsert_form').find('[name="csrf_token"]').val();
			const id = $('#upsert_form').find('[name="id"]').val();

			// アップロード初期化
			const initRes = await $.ajax({
				url: '/custom/admin/app/Controllers/movie/movie_upsert_controller.php',
				method: 'POST',
				data: {
					mode: 'init',
					file_name: file.name,
					csrf_token: csrf_token,
					course_info_id: course_info_id,
					course_no: course_no,
				},
				dataType: 'json'
			});
			if (initRes.status == 'error') {
				location.href = "/custom/admin/app/Views/event/movie.php";
				return;
			}

			// 2つのプロパティを、別々の変数として一度に取り出す
			const {
				uploadId,
				key
			} = initRes;
			const parts = [];
			for (let i = 0; i < total_chunks; i++) {
				const start = i * chunk_size;
				const end = Math.min(start + chunk_size, file.size);
				const chunk = file.slice(start, end);
				// Presigned URL取得
				const presignRes = await $.post('/custom/admin/app/Controllers/movie/movie_upsert_controller.php', {
					mode: 'presign',
					uploadId,
					key,
					partNumber: i + 1
				});
				if (presignRes.status == 'error') {
					location.href = "/custom/admin/app/Views/event/movie.php";
					return;
				}

				// S3が発行した署名付きURLに対して、チャンクデータをPUT
				const res = await fetch(presignRes.url, {
					method: 'PUT',
					headers: {
						'Content-Type': 'application/octet-stream' // 明示的に付与
					},
					body: chunk
				});
				const eTag = res.headers.get('ETag');

				// エラー処理
				if (!res.ok) {
					location.href = "/custom/admin/app/Views/event/movie.php";
					return;
				}

				// 正常なら配列に追加
				parts.push({
					PartNumber: i + 1,
					ETag: eTag
				});

				// プログレスバー更新
				const percentage = Math.round(((i + 1) / total_chunks) * 100);
				$('#percent').text(`${percentage}%`);

			}

			// 完了通知
			const completeRes = await $.ajax({
				url: '/custom/admin/app/Controllers/movie/movie_upsert_controller.php',
				method: 'POST',
				data: {
					mode: 'complete',
					uploadId,
					key,
					parts: JSON.stringify(parts),
					csrf_token,
					course_info_id,
					course_no,
					id,
					file_name: file.name.replace(/\.[^/.]+$/, ''),
				},
				dataType: 'json'
			});

			location.href = '/custom/admin/app/Views/event/movie.php';
		});
	});
</script>

</html>