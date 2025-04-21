<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/material/material_controller.php');
include($CFG->dirroot . '/custom/admin/app/Views/common/header.php');

$material_controller = new MaterialController();
$result_list = $material_controller->index();

$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

$category_list  = $result_list['category_list'] ?? [];
$event_list     = $result_list['event_list'] ?? [];
$material_list  = $result_list['material'] ?? [];
$course_number  = $result_list['course_number'] ?? [];
$course_id      = $result_list['course_info'] ?? [];
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
					<p class="header-title title ms-4 fs-4 fw-bold mb-0">講義資料アップロード</p>
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
									<form id="form" method="POST" action="/custom/admin/app/Views/event/material.php" class="w-100">
										<div class="sp-block d-flex justify-content-between">
											<div class="mb-3 w-100">
												<label class="form-label" for="category_id">カテゴリー</label>
												<select name="category_id" class="form-control">
													<option value="">すべて</option>
													<?php foreach ($category_list as $category) { ?>
														<option value="<?= $category['id'] ?>"
															<?= isSelected($category['id'], $old_input['category_id'] ?? null, null) ? 'selected' : '' ?>>
															<?= htmlspecialchars($category['name']) ?>
														</option>
													<?php } ?>
												</select>
											</div>
											<div class="sp-ms-0 ms-3 mb-3 w-100">
												<label class="form-label" for="event_status_id">開催ステータス</label>
												<select name="event_status_id" class="form-control">
													<option value="">すべて</option>
													<?php foreach ($display_status_list as $key => $event_status) { ?>
														<option value="<?= $key ?>"
															<?= isSelected($key, $old_input['event_status_id'] ?? null, null) ? 'selected' : '' ?>>
															<?= htmlspecialchars($event_status) ?>
														</option>
													<?php } ?>
												</select>
											</div>
										</div>
										<div class="sp-block d-flex justify-content-between">
											<div class="mb-3 w-100">
												<label class="form-label" for="event_id">イベント名</label>
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
													<select id="course_no_select" class="form-control w-100" <?= $result_list['is_single'] ? 'disabled' : '' ?>>
														<option value="">未選択</option>
														<?php foreach ($course_number as $course_no) { ?>
															<option value="<?= $course_no ?>"
																<?= isSelected($course_no, $old_input['course_no'] ?? null, null) ? 'selected' : '' ?>>
																<?= "第" . htmlspecialchars($course_no) . "回" ?>
															</option>
														<?php } ?>
													</select>
													<input type="hidden" id="course_no" name="course_no"
														value="<?= htmlspecialchars($old_input['course_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
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

				<div id="ajax-error-message-global" style="display:none;"></div>

				<?php if ($result_list['is_display']): ?>
					<div class="col-12 col-lg-12">
						<div class="card">
							<div class="card-body">
								<form method="POST" enctype="multipart/form-data" id="upsert_form">
									<div class="d-flex justify-content-end">
										<button type="button" id="upload_button" class="btn btn-primary mb-4">アップロード</button>
									</div>

									<input type="hidden" name="category_id" value="<?= htmlspecialchars($old_input['category_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<input type="hidden" name="event_status_id" value="<?= htmlspecialchars($old_input['event_status_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<input type="hidden" name="event_id" value="<?= htmlspecialchars($old_input['event_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<input type="hidden" name="course_no" value="<?= htmlspecialchars($old_input['course_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<input type="hidden" name="course_info_id" value="<?= htmlspecialchars($result_list['course_info'] ?? $old_input['course_info_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
									<input type="hidden" name="material_id" value="<?= htmlspecialchars($_GET['material_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

									<?php if (!empty($material_list)): ?>
										<?php foreach ($material_list as $material): ?>
											<div class="material-container mb-4" data-material-id="<?= (int)$material->id ?>">
												<input type="hidden" name="ids[<?= (int)$material->id ?>]" value="<?= (int)$material->id ?>">
												<div class="fields-container">
													<div class="uploadRow">
														<div class="add_field mb-3 d-flex align-items-center">
															<input type="hidden" class="hiddenFileField" name="files[<?= (int)$material->id ?>]" value="">
															<input type="file" class="form-control fileUpload" name="files[<?= (int)$material->id ?>]" multiple accept="application/pdf">
															<td class="text-center ps-4 pe-4 text-nowrap">
																<button type="button" class="trash ms-2 btn-icon delete-link">
																	<i data-feather="trash"></i>
																</button>
															</td>
														</div>
														<div class="fileInfo mt-2 d-none"></div>
														<div class="fileError mt-2 d-none"></div>
													</div>
												</div>
											</div>
										<?php endforeach; ?>
									<?php else: ?>
										<div class="material-container mb-4" data-material-id="0">
											<input type="hidden" name="ids[0]" value="0">
											<div class="fields-container">
												<div class="uploadRow">
													<div class="add_field mb-3 d-flex align-items-center">
														<input type="hidden" class="hiddenFileField" name="files[0]" value="">
														<input type="file" class="form-control fileUpload" name="files[0]" multiple accept="application/pdf">
														<td class="text-center ps-4 pe-4 text-nowrap">
															<button type="button" class="trash ms-2 btn-icon delete-link">
																<i data-feather="trash"></i>
															</button>
														</td>
													</div>
													<div class="fileInfo mt-2 d-none"></div>
													<div class="fileError mt-2 d-none"></div>
												</div>
											</div>
										</div>
									<?php endif; ?>

									<div class="d-flex justify-content-end">
										<button class="btn btn-primary" id="add-btn">項目追加</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</main>
		</div>
	</div>

	<template id="uploadRowTemplate">
		<div class="fields-container">
			<div class="uploadRow">
				<div class="add_field mb-3 d-flex align-items-center">
					<input type="hidden" class="hiddenFileField" value="">
					<input type="file" class="form-control fileUpload" multiple accept="application/pdf">
					<button type="button" class="trash ms-2 btn-icon delete-link">
						<i data-feather="trash"></i>
					</button>
				</div>
				<div class="fileInfo mt-2 d-none"></div>
				<div class="fileError mt-2 d-none"></div>
			</div>
		</div>
	</template>

	<style>
		.btn-icon {
			background: none;
			border: none;
			cursor: pointer;
			padding: 0.25rem;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.btn-icon:focus {
			outline: none;
			box-shadow: none;
		}
	</style>

	<script src="/custom/admin/public/js/app.js"></script>
	<script>
		const existingMaterials = <?= json_encode($material_list, JSON_UNESCAPED_UNICODE) ?>;
		let existingMaterialMap = {};
		let occupiedFileNames = new Set();
		for (const key in existingMaterials) {
			if (Object.hasOwnProperty.call(existingMaterials, key)) {
				const mat = existingMaterials[key];
				let fileName = mat.file_name ? mat.file_name.trim() : "";
				if (fileName) {
					existingMaterialMap[mat.id] = fileName;
					occupiedFileNames.add(fileName);
					console.log(occupiedFileNames);
				}
			}
		}

		$(document).ready(function() {
			$('select[name="category_id"], select[name="event_status_id"], select[name="event_id"]').change(function() {
				$("#form").submit();
			});
			$('#course_no_select').change(function() {
				$("#form").submit();
			});
			$('#form').on('submit', function() {
				$('#course_no').val($('#course_no_select').val());
			});

			$(document).on('change', '.fileUpload', function(e) {
				var file = this.files[0];
				if (!file) return;

				let $fileError = $(this).closest('.uploadRow').find('.fileError');
				$fileError.text('').removeClass('duplicate-error').addClass('d-none');
				if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
					$fileError.text('PDFファイルのみアップロード可能です。').removeClass('d-none');
					$(this).val('');
					return;
				}

				var fileName = file.name;
				var fileURL = URL.createObjectURL(file);
				var linkElem = createFileLink(fileURL, fileName);
				$(this).closest('.uploadRow').find('.fileInfo').html(linkElem).removeClass('d-none');
				feather.replace();
			});

			$(document).on('click', '.delete-link', function(event) {
				event.preventDefault();
				var $row = $(this).closest('.uploadRow');
				var $container = $(this).closest('.material-container');
				let materialId = $container.data('material-id');
				if (materialId && existingMaterialMap.hasOwnProperty(materialId)) {
					let oldFileName = existingMaterialMap[materialId];
					if (oldFileName && occupiedFileNames.has(oldFileName)) {
						occupiedFileNames.delete(oldFileName);
					}
				}
				if ($row.find('.fileError').hasClass('duplicate-error')) {
					if ($container.find('.uploadRow').length > 1) {
						$row.remove();
					} else {
						$container.find('input[name^="ids"]').remove();
						$row.find('input[type="file"]').val('');
						$row.find('.hiddenFileField').val('delete');
						$row.find('.fileInfo').html('').addClass('d-none');
						$row.find('.fileError').html('').addClass('d-none');
					}
					return;
				}
				if ($container.find('.uploadRow').length > 1) {
					$container.find('input[name^="ids"]').remove();
					$row.remove();
				} else {
					if ($('.material-container').length > 1) {
						$container.remove();
					} else {
						$container.find('input[name^="ids"]').remove();
						$row.find('input[type="file"]').val('');
						$row.find('.hiddenFileField').val('delete');
						$row.find('.fileInfo').html('').addClass('d-none');
						$row.find('.fileError').html('').addClass('d-none');
					}
				}
			});


			$('#upload_button').on('click', function(e) {
				e.preventDefault();
				let fileInfoObjects = [];
				let filesToUpload = [];
				let currentFileNames = new Set();

				$('.material-container').each(function() {
					let $container = $(this);
					let materialId = $container.data('material-id') || 0;
					let uploadRows = $container.find('.fileUpload');

					uploadRows.each(function() {
						let rowElm = $(this).closest('.uploadRow');
						let files = this.files;
						if (files && files.length > 0) {
							for (let i = 0; i < files.length; i++) {
								let f = files[i];
								if (f.type !== 'application/pdf' && !f.name.toLowerCase().endsWith('.pdf')) {
									rowElm.find('.fileError')
										.removeClass('d-none')
										.html('<div class="error-message text-danger">PDFファイルのみアップロード可能です。</div>');
									return;
								}

								currentFileNames.add(f.name);
								fileInfoObjects.push({
									name: f.name,
									row: rowElm,
									materialId: materialId
								});
								filesToUpload.push({
									file: f,
									id: materialId
								});
							}
						}
					});
				});
				$('.fileError').text('').addClass('d-none').removeClass('duplicate-error');

				let duplicateErrors = [];
				const fileNameCounts = new Map();
				fileInfoObjects.forEach(info => {
					if (!fileNameCounts.has(info.name)) {
						fileNameCounts.set(info.name, []);
					}
					fileNameCounts.get(info.name).push(info);
				});

				fileNameCounts.forEach((infoArray, fileName) => {
					if (infoArray.length > 1) {
						infoArray.forEach(info => {
							duplicateErrors.push({
								fileInfo: info,
								message: '複数の行で同じファイル名が選択されています: ' + fileName
							});
						});
					}
				});

				fileInfoObjects.forEach(info => {
					const fileName = info.name;
					const materialId = info.materialId;

					if (occupiedFileNames.has(fileName) &&
						(!existingMaterialMap[materialId] || existingMaterialMap[materialId] !== fileName)) {
						duplicateErrors.push({
							fileInfo: info,
							message: '既存のファイルと同じ名前です: ' + fileName
						});
					}
				});

				if (duplicateErrors.length > 0) {
					duplicateErrors.forEach(error => {
						error.fileInfo.row.find('.fileError')
							.removeClass('d-none')
							.addClass('duplicate-error')
							.html('<div class="error-message text-danger">' + error.message + '</div>');
					});
					return;
				}

				if (filesToUpload.length === 0) {
					let form_data = new FormData($('#upsert_form')[0]);
					$.ajax({
						url: '/custom/admin/app/Controllers/material/material_upsert_controller.php',
						type: 'POST',
						data: form_data,
						processData: false,
						contentType: false,
						dataType: 'json',
						success: function(response) {
							location.href = "/custom/admin/app/Views/event/material.php";
						},
						error: function() {
							location.href = "/custom/admin/app/Views/event/material.php";
						}
					});
					return;
				}
				const CHUNK_SIZE = 10 * 1024 * 1024;

				function uploadFile(fileObj, callback) {
					let file = fileObj.file;
					let fileId = fileObj.id;
					const totalSize = file.size;
					const totalChunks = Math.ceil(totalSize / CHUNK_SIZE);
					if (totalChunks <= 1) {
						let form_data = new FormData();
						form_data.append('id', fileId);
						form_data.append('file', file);
						form_data.append('file_name', file.name);
						form_data.append('total_file_size', totalSize);
						form_data.append('chunk_index', 0);
						form_data.append('total_chunks', 1);
						appendCommonFormData(form_data);
						$.ajax({
							url: '/custom/admin/app/Controllers/material/material_upsert_controller.php',
							type: 'POST',
							data: form_data,
							processData: false,
							contentType: false,
							dataType: 'json',
							success: function() {
								callback();
							},
							error: function() {
								callback();
							}
						});
					} else {
						let currentChunk = 0;

						function uploadChunk() {
							if (currentChunk >= totalChunks) {
								$.ajax({
									url: '/custom/admin/app/Controllers/material/material_upsert_controller.php',
									type: 'POST',
									data: {
										finalize: true,
										file_name: file.name,
										id: fileId,
										csrf_token: $('#upsert_form').find('[name="csrf_token"]').val(),
										course_no: $('#upsert_form').find('[name="course_no"]').val()
									},
									dataType: 'json',
									success: function() {
										callback();
									},
									error: function() {
										$("#ajax-error-message-global")
											.html('<div class="text-danger">アップロード統合処理に失敗しました</div>')
											.show();
									}
								});
								return;
							}
							const start = currentChunk * CHUNK_SIZE;
							const end = Math.min(start + CHUNK_SIZE, totalSize);
							const chunkBlob = file.slice(start, end);
							let form_data = new FormData();
							form_data.append('id', fileId);
							form_data.append('file', chunkBlob);
							form_data.append('file_name', file.name);
							form_data.append('total_file_size', totalSize);
							form_data.append('chunk_index', currentChunk);
							form_data.append('total_chunks', totalChunks);
							appendCommonFormData(form_data);
							$.ajax({
								url: '/custom/admin/app/Controllers/material/material_upsert_controller.php',
								type: 'POST',
								data: form_data,
								processData: false,
								contentType: false,
								dataType: 'json',
								success: function(response) {
									if (response.status === 'error') {
										$("#ajax-error-message-global")
											.html('<div class="text-danger">' + response.error + '</div>')
											.show();
										return;
									}
									currentChunk++;
									uploadChunk();
								},
								error: function() {
									$("#ajax-error-message-global")
										.html('<div class="text-danger">アップロードに失敗しました</div>')
										.show();
								}
							});
						}
						uploadChunk();
					}
				}

				function appendCommonFormData(fd) {
					fd.append('course_no', $('#upsert_form').find('[name="course_no"]').val());
					fd.append('course_info_id', $('#upsert_form').find('[name="course_info_id"]').val());
					fd.append('csrf_token', $('#upsert_form').find('[name="csrf_token"]').val());
					fd.append('event_id', $('#upsert_form').find('[name="event_id"]').val());
					fd.append('category_id', $('#upsert_form').find('[name="category_id"]').val());
					fd.append('material_id', $('#upsert_form').find('[name="material_id"]').val());
					$('#upsert_form').find('input[name^="ids"]').each(function() {
						fd.append($(this).attr('name'), $(this).val());
					});
					$('#upsert_form').find('input[name^="files"]').each(function() {
						fd.append($(this).attr('name'), $(this).val());
					});
				}

				let currentIndex = 0;

				function uploadNextFile() {
					if (currentIndex < filesToUpload.length) {
						uploadFile(filesToUpload[currentIndex], function() {
							currentIndex++;
							uploadNextFile();
						});
					} else {
						location.href = "/custom/admin/app/Views/event/material.php";
					}
				}
				uploadNextFile();
			});

			(function init_existing_files() {
				for (const key in existingMaterials) {
					if (!Object.hasOwnProperty.call(existingMaterials, key)) continue;
					const material = existingMaterials[key];
					const material_id = material.id;
					const file_name = material.file_name;
					if (file_name !== "") {
						const container = document.querySelector(`.material-container[data-material-id="${material_id}"]`);
						if (!container) continue;
						const row = container.querySelector('.uploadRow');
						if (!row) continue;
						const file_info = row.querySelector('.fileInfo');
						if (!file_info) continue;
						var courseNo = $('#course_no').val() || '1';
						const link_elem = create_pdf_link(material.course_info_id, courseNo, file_name);
						file_info.appendChild(link_elem);
						file_info.classList.remove('d-none');
					}
				}
				feather.replace();
			})();

			$(document).on("click", ".open-pdf", function(e) {
				e.preventDefault();
				let fileUrl = $(this).data("file_url");
				if (fileUrl) {
					window.open(fileUrl, "_blank");
					return;
				}
				var materialCourseNo = $(this).data("course_no");
				var materialCourseId = $(this).data("course_info");
				var materialFileName = $(this).data("file_name");
				const pdfUrl = "/uploads/material/" + materialCourseId + '/' + materialCourseNo + '/' + materialFileName;
				window.open(`/custom/app/Views/event/pdf.php?file=${encodeURIComponent(pdfUrl)}`, "_blank");
			});

			$('#add-btn').on('click', function(e) {
				e.preventDefault();
				const template = document.getElementById('uploadRowTemplate');
				if (!template) return;

				let newMaterialId = Date.now();
				let $materialContainer = $('<div class="material-container mb-4" data-material-id="new_' + newMaterialId + '"></div>');

				const fieldsContainer = $('<div class="fields-container"></div>');
				$materialContainer.append(fieldsContainer);

				$materialContainer.append('<input type="hidden" name="ids[new_' + newMaterialId + ']" value="0">');

				const clone = document.importNode(template.content, true);
				let $newRow = $(clone).find('.uploadRow');

				$newRow.find('.fileUpload').attr('name', 'files[new_' + newMaterialId + ']');
				$newRow.find('.hiddenFileField').attr('name', 'files[new_' + newMaterialId + ']');

				fieldsContainer.append($newRow);

				$('#add-btn').closest('.d-flex').before($materialContainer);

				feather.replace();
			});

			function findDuplicates(occupiedArrOrSet, newList) {
				let occupiedSet = (occupiedArrOrSet instanceof Set) ? occupiedArrOrSet : new Set(occupiedArrOrSet);
				let conflict = [];
				for (let name of newList) {
					if (occupiedSet.has(name)) {
						conflict.push(name);
					}
				}
				let newSet = new Set();
				for (let name of newList) {
					if (newSet.has(name) && !conflict.includes(name)) {
						conflict.push(name);
					}
					newSet.add(name);
				}
				return conflict;
			}

			function createFileLink(fileURL, fileName) {
				const container = document.createElement('div');
				container.classList.add('fileInfoItem', 'd-flex', 'align-items-center', 'mb-2');
				const link = document.createElement('a');
				link.classList.add('fileLink', 'd-flex', 'align-items-center', 'text-decoration-none', 'open-pdf');
				link.setAttribute('href', fileURL);
				link.setAttribute('data-file_url', fileURL);
				link.setAttribute('data-file_name', fileName);
				const icon = document.createElement('i');
				icon.setAttribute('data-feather', 'file-text');
				icon.classList.add('me-2');
				link.appendChild(icon);
				const span = document.createElement('span');
				span.classList.add('fileName', 'text-primary');
				span.textContent = fileName;
				link.appendChild(span);
				container.appendChild(link);
				return container;
			}

			function create_pdf_link(courseInfo, courseNo, file_name) {
				const file_link_container = document.createElement('div');
				file_link_container.classList.add('fileInfoItem', 'd-flex', 'align-items-center', 'mb-2');
				const link = document.createElement('a');
				link.classList.add('fileLink', 'd-flex', 'align-items-center', 'text-decoration-none', 'open-pdf');
				link.setAttribute('data-course_info', courseInfo);
				link.setAttribute('data-course_no', courseNo);
				link.setAttribute('data-file_name', file_name);
				const icon = document.createElement('i');
				icon.setAttribute('data-feather', 'file-text');
				icon.classList.add('me-2');
				link.appendChild(icon);
				const span = document.createElement('span');
				span.classList.add('fileName', 'text-primary');
				span.textContent = file_name;
				link.appendChild(span);
				file_link_container.appendChild(link);
				return file_link_container;
			}
		});
	</script>
</body>

</html>