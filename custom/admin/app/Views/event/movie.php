<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/event_controller.php');
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
session_start();

// バリデーションエラー
$errors   = $_SESSION['errors']   ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

$eventController = new EventController();
$events = $eventController->index();

global $DB;
$movies = [];

if (isset($_GET['search'])) {
	try {
		$sql = "SELECT * FROM {course_movie} WHERE is_delete = :is_delete";
		$movies = $DB->get_records_sql($sql, ['is_delete' => 0]);
		$movies = array_values($movies);
	} catch (Exception $e) {
		$_SESSION['message_error'] = 'エラーが発生しました';
	}
	if (empty($movies)) {
		$movies[] = (object)[
			'id'        => 0,
			'name'      => '',
			'file_name' => '',
			'file_path' => '',
		];
	}
}
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
							<div class="card">
								<div class="card-body p-055 p-025 sp-block d-flex align-items-bottom">
									<form method="GET" action="" class="d-flex w-100">
										<div class="sp-w-100 w-50 me-4 sp-mb-3">
											<label class="form-label" for="notyf-message">イベント名</label>
											<select name="movie_id" class="form-control w-100">
												<?php foreach ($events as $event): ?>
													<option value="<?= htmlspecialchars($event['id'], ENT_QUOTES, 'UTF-8') ?>">
														<?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
													</option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="sp-w-100 sp-mb-4 w-25">
											<label class="form-label" for="notyf-message">回数</label>
											<div class="d-flex align-items-center">
												<select name="round" class="form-control w-100">
													<option value="0">未選択</option>
													<option value="1">第1回</option>
													<option value="2">第2回</option>
													<option value="3">第3回</option>
													<option value="4">第4回</option>
													<option value="5">第5回</option>
													<option value="6">第6回</option>
													<option value="7">第7回</option>
													<option value="8">第8回</option>
													<option value="9">第9回</option>
												</select>
											</div>
										</div>
										<div class="d-flex align-items-end ms-auto">
											<button class="btn btn-primary me-0 search-button" type="submit" name="search" value="1">検索</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>

				<?php if (!empty($movies)): ?>
					<div class="search-area col-12 col-lg-12">
						<div class="card">
							<div class="card-body">
								<form method="POST" action="/custom/admin/app/Controllers/movie/movie_upsert_controller.php" enctype="multipart/form-data">
									<div class="d-flex justify-content-end">
										<button type="submit" class="btn btn-primary">アップロード</button>
									</div>
									<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<input type="hidden" name="movie_id" value="<?= htmlspecialchars($_GET['movie_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<input type="hidden" name="round" value="<?= htmlspecialchars($_GET['round'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<?php foreach ($movies as $index => $movie): ?>
										<div class="movie-container mb-4" data-movie-id="<?= htmlspecialchars($movie->id, ENT_QUOTES, 'UTF-8') ?>">
											<input type="hidden" name="ids[<?= $index ?>]" value="<?= !empty($movie->id) ? (int)$movie->id : 0 ?>">
											<h5><?= htmlspecialchars($movie->name, ENT_QUOTES, 'UTF-8') ?></h5>
											<div class="fields-container">
												<div class="uploadRow">
													<div class="add_field mb-3 d-flex align-items-center">
														<input type="hidden" class="hiddenField"
															name="video_files[<?= $index ?>][]"
															value="">
														<input type="file" class="form-control fileUpload"
															name="video_files[<?= $index ?>][]"
															multiple accept="video/mp4,video/x-msvideo,video/quicktime">
														<a type="button" class="trash ms-2 btn btn-danger btn-sm delete-link"
															data-id="<?= htmlspecialchars($movie->id, ENT_QUOTES, 'UTF-8') ?>"
															data-name="<?= htmlspecialchars(!empty($movie->file_name) ? $movie->file_name : $movie->name, ENT_QUOTES, 'UTF-8') ?>"
															data-has-file="<?= !empty($movie->file_name) ? '1' : '0' ?>">
															<i data-feather="trash"></i>
														</a>
													</div>
													<div class="fileInfo mt-2 d-none"></div>
												</div>
											</div>
										</div>
										<?php if (!empty($errors['video_files'][$movie->id])): ?>
											<div class="text-danger">
												<?= htmlspecialchars($errors['video_files'][$movie->id], ENT_QUOTES, 'UTF-8') ?>
											</div>
										<?php endif; ?>
									<?php endforeach; ?>

									<div class="d-flex justify-content-end">
										<button class="btn btn-primary" id="add-btn">項目追加</button>
									</div>
								</form>
							</div>

							<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
								<div class="modal-dialog modal-dialog-centered">
									<div class="modal-content">
										<form id="deleteForm" action="/custom/admin/app/Controllers/movie/movie_delete_controller.php" method="POST">
											<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
											<input type="hidden" name="id" value="">

											<div class="modal-header">
												<h5 class="modal-title">削除確認</h5>
												<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
											</div>

											<div class="modal-body">
												<p class="mt-3"><span id="deleteMovieName"></span> を削除します。本当によろしいですか？</p>
											</div>

											<div class="modal-footer">
												<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
												<button type="button" class="btn btn-danger" id="confirmDeleteButton">削除</button>
											</div>
										</form>
									</div>
								</div>
							</div>

						</div>
					</div>
				<?php endif; ?>
			</main>
		</div>
	</div>

	<template id="uploadRowTemplate">
		<div class="uploadRow">
			<div class="add_field mb-3 d-flex align-items-center">
				<input type="hidden" class="hiddenField" name="" value="">
				<input type="file" class="form-control fileUpload" name="" multiple accept="video/mp4,video/x-msvideo,video/quicktime">
				<button type="button" class="trash ms-2 btn btn-danger btn-sm deleteFile">
					<i data-feather="trash"></i>
				</button>
			</div>
			<div class="fileInfo mt-2 d-none"></div>
		</div>
	</template>

	<script src="/custom/admin/public/js/app.js"></script>
	<script>
		$(document).on('click', '.delete-link, .deleteFile', function(event) {
			event.preventDefault();

			let hasFile = $(this).data('has-file');
			if (typeof hasFile === 'undefined') {
				hasFile = 0;
			}

			if (hasFile == 1) {
				const selectedId = $(this).data('id');
				const selectedName = $(this).data('name');
				$('#deleteForm').find('input[name="id"]').val(selectedId);
				$('#deleteMovieName').text(selectedName);
				$('#confirmDeleteModal').modal('show');
			} else {
				$(this).closest('.uploadRow').remove();
			}
		});

		$('#confirmDeleteButton').on('click', function() {
			$('#confirmDeleteModal').modal('hide');
			$('#deleteForm').submit();
		});

		function createFileLink(fileName, fileUrl) {
			const fileLinkContainer = document.createElement('div');
			fileLinkContainer.classList.add('fileInfoItem', 'd-flex', 'align-items-center', 'mb-2');

			const link = document.createElement('a');
			if (fileUrl.startsWith('blob:') || fileUrl.startsWith('http://') || fileUrl.startsWith('https://')) {
				link.href = fileUrl;
			} else if (fileUrl.charAt(0) === '/') {
				link.href = fileUrl;
			} else {
				link.href = '/uploads/movie/' + fileUrl;
			}
			link.target = '_blank';
			link.classList.add('fileLink', 'd-flex', 'align-items-center', 'text-decoration-none');
			link.innerHTML = `
        <i data-feather="file-text" class="me-2"></i>
        <span class="fileName text-primary">${fileName}</span>
    `;
			fileLinkContainer.appendChild(link);

			feather.replace();
			return fileLinkContainer;
		}

		// 動画ファイル変更時の処理
		function handleFileChange(e) {
			const files = e.target.files;
			const row = e.target.closest('.uploadRow');
			const fileInfo = row.querySelector('.fileInfo');
			fileInfo.innerHTML = '';

			Array.from(files).forEach(file => {
				// 許可するMIMEタイプをチェック
				const allowedMimeTypes = ['video/mp4', 'video/x-msvideo', 'video/quicktime'];
				if (allowedMimeTypes.indexOf(file.type) !== -1) {
					const objectURL = URL.createObjectURL(file);
					const linkElement = createFileLink(file.name, objectURL);
					fileInfo.appendChild(linkElement);
				} else {
					alert('MP4, AVI, MOV形式の動画ファイルのみアップロードできます。');
				}
			});

			fileInfo.classList.toggle('d-none', files.length === 0);
			feather.replace();
		}
		document.querySelectorAll('.fileUpload').forEach(input => {
			input.addEventListener('change', handleFileChange);
		});

		<?php if (isset($_GET['search'])): ?>
				(function initExistingFiles() {
					const existingmovies = <?= json_encode($movies, JSON_UNESCAPED_UNICODE) ?>;
					existingmovies.forEach(movie => {
						const movieId = movie.id;
						const fileName = movie.file_name;
						const fileUrl = movie.file_path;

						if (fileUrl) {
							const container = document.querySelector(`.movie-container[data-movie-id="${movieId}"]`);
							if (!container) return;

							const row = container.querySelector('.uploadRow');
							if (!row) return;

							const fileInfo = row.querySelector('.fileInfo');
							const linkElem = createFileLink(fileName, fileUrl);
							fileInfo.appendChild(linkElem);
							fileInfo.classList.remove('d-none');
						}
					});
				})();
		<?php endif; ?>

		let inputCount = 1;
		const addButton = document.getElementById('add-btn');
		if (addButton) {
			addButton.addEventListener('click', function(e) {
				e.preventDefault();
				const template = document.getElementById('uploadRowTemplate');
				if (!template) {
					alert('テンプレートが見つかりません。');
					return;
				}
				const movieContainers = document.querySelectorAll('.movie-container');
				if (movieContainers.length === 0) {
					alert('コースコンテナが見つかりません。');
					return;
				}
				const movieContainer = movieContainers[movieContainers.length - 1];
				const fieldsContainer = movieContainer.querySelector('.fields-container');
				if (!fieldsContainer) return;

				let index = 0;
				const existingInput = movieContainer.querySelector('.fileUpload');
				if (existingInput && existingInput.name) {
					const match = existingInput.name.match(/^video_files\[(\d+)\]\[\]$/);
					if (match) {
						index = match[1];
					}
				}

				const clone = template.content.cloneNode(true);
				const fileInput = clone.querySelector('.fileUpload');
				const hiddenField = clone.querySelector('.hiddenField');
				if (fileInput && hiddenField) {
					fileInput.name = `video_files[${index}][]`;
					hiddenField.name = `video_files[${index}][]`;
				}
				if (fileInput) {
					fileInput.addEventListener('change', handleFileChange);
				}
				fieldsContainer.appendChild(clone);
				feather.replace();
			});
		}
		feather.replace();
	</script>

	<?php if (!empty($errors) || isset($_GET['search'])): ?>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const searchArea = document.querySelector('.search-area');
				if (searchArea) {
					searchArea.style.display = 'block';
				}
			});
		</script>
	<?php endif; ?>
</body>

</html>