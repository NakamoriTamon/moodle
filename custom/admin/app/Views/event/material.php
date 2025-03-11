<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/material/material_controller.php');
include($CFG->dirroot . '/custom/admin/app/Views/common/header.php');

$material_controller = new MaterialController();
$result_list = $material_controller->index();

$errors    = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

$category_list = $result_list['category_list'] ?? [];
$event_list    = $result_list['event_list'] ?? [];
$material_list = $result_list['material'] ?? [];
$course_number = $result_list['course_number'] ?? [];
$course_id = $result_list['course_info'] ?? [];
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
									<form id="form" method="POST" action="/custom/admin/app/Views/event/material.php" class="w-100">
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
													<?php foreach ($event_status_list as $key => $event_status) { ?>
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
											<div class="material-container mb-4" data-material-id="<?= htmlspecialchars($material->id, ENT_QUOTES, 'UTF-8') ?>">
												<input type="hidden" name="ids[<?= $material->id ?>]" value="<?= (int)$material->id ?>">
												<div class="fields-container">
													<div class="uploadRow">
														<div class="add_field mb-3 d-flex align-items-center">
															<input type="hidden" class="hiddenField" name="course_info_id" value="<?= $material->course_info_id ?>">
															<input type="hidden" class="hiddenFileField" name="files[<?= $material->id ?>]" value="">
															<input type="file" class="form-control fileUpload" name="files[<?= $material->id ?>]" multiple accept="application/pdf">
															<td class="text-center ps-4 pe-4 text-nowrap">
																<a type="button" class="trash ms-2 btn btn-sm delete-link"
																	data-id="<?= htmlspecialchars($material->id, ENT_QUOTES, 'UTF-8') ?>"
																	data-name="<?= htmlspecialchars($material->file_name, ENT_QUOTES, 'UTF-8') ?>"
																	data-has-file="<?= !empty($material->file_name) ? '1' : '0' ?>">
																	<i data-feather="trash"></i>
																</a>
															</td>
														</div>
														<div class="fileInfo mt-2 d-none"></div>
													</div>
												</div>
											</div>
										<?php endforeach; ?>
									<?php else: ?>
										<div class="material-container mb-4">
											<input type="hidden" name="ids[0]" value="0">
											<div class="fields-container">
												<div class="uploadRow">
													<div class="add_field mb-3 d-flex align-items-center">
														<input type="hidden" class="hiddenFileField" name="files[<?= $material->id ?? 0 ?>]" value="">
														<input type="file" class="form-control fileUpload" name="files[<?= $material->id ?? 0 ?>]" multiple accept="application/pdf">
														<td class="text-center ps-4 pe-4 text-nowrap">
															<a type="button" class="trash ms-2 btn btn-sm delete-link">
																<i data-feather="trash"></i>
															</a>
														</td>
													</div>
													<div class="fileInfo mt-2 d-none"></div>
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
					<button type="button" class="trash ms-2 btn btn-sm delete-link">
						<i data-feather="trash"></i>
					</button>
				</div>
				<div class="fileInfo mt-2 d-none"></div>
			</div>
		</div>
	</template>

	<script src="/custom/admin/public/js/app.js"></script>
	<script>
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

			$(document).on('click', '.delete-link', function(event) {
				event.preventDefault();
				var $container = $(this).closest('.material-container');
				// まず、現在のコンテナ内に複数の .uploadRow があるか判定
				if ($container.find('.uploadRow').length > 1) {
					// 複数あるなら、クリックされた行だけ削除する
					$(this).closest('.uploadRow').remove();
				} else {
					// コンテナ内が1行のみの場合
					// さらに、ページ全体で複数の material-container が存在するか判定
					if ($('.material-container').length > 1) {
						// 複数存在すれば、このコンテナ自体を削除する
						$container.remove();
					} else {
						// 唯一の場合は、行は残して値のみクリアする
						var $uploadRow = $(this).closest('.uploadRow');
						$uploadRow.find('input[type="file"]').val('');
						$uploadRow.find('.hiddenFileField').val('delete');
						$uploadRow.find('.fileInfo').html('').addClass('d-none');
					}
				}
			});



			$(document).on('change', '.fileUpload', function(e) {
				var file = this.files[0];
				if (!file) return;
				var fileName = file.name;
				var fileURL = URL.createObjectURL(file);
				var linkElem = create_file_link(fileURL, fileName);
				$(this).closest('.uploadRow').find('.fileInfo').html(linkElem).removeClass('d-none');
				feather.replace();
			});

			$('#upload_button').on('click', function(e) {
				e.preventDefault();
				let filesToUpload = [];
				$('.fileUpload').each(function() {
					let fileInput = this;
					if (fileInput.files && fileInput.files.length > 0) {
						let container = $(this).closest('.material-container');
						let hiddenId = container.find('input[name^="ids"]').val() || '';
						for (let i = 0; i < fileInput.files.length; i++) {
							filesToUpload.push({
								file: fileInput.files[i],
								id: hiddenId,
								container: container
							});
						}
					}
				});

				if (filesToUpload.length === 0) {
					var form_data = new FormData($('#upsert_form')[0]);
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
						error: function(jqXHR, textStatus, errorThrown) {
							location.href = "/custom/admin/app/Views/event/material.php";
						}
					});
					return;
				}

				function uploadFile(fileObj, callback) {
					let file = fileObj.file;
					const chunk_size = 10 * 1024 * 1024;
					const total_chunks = Math.ceil(file.size / chunk_size);

					if (total_chunks === 1) {
						const form_data = new FormData();
						form_data.append('id', fileObj.id);
						form_data.append('course_no', $('#upsert_form').find('[name="course_no"]').val());
						form_data.append('course_info_id', $('#upsert_form').find('[name="course_info_id"]').val());
						form_data.append('csrf_token', $('#upsert_form').find('[name="csrf_token"]').val());
						form_data.append('event_id', $('#upsert_form').find('[name="event_id"]').val());
						form_data.append('category_id', $('#upsert_form').find('[name="category_id"]').val());
						form_data.append('material_id', $('#upsert_form').find('[name="material_id"]').val());
						form_data.append('file', file);
						form_data.append('file_name', file.name);
						form_data.append('total_file_size', file.size);
						form_data.append('chunk_index', 0);
						form_data.append('total_chunks', 1);
						$('#upsert_form').find('input[name^="ids"]').each(function() {
							form_data.append($(this).attr('name'), $(this).val());
						});
						$('#upsert_form').find('input[name^="files"]').each(function() {
							form_data.append($(this).attr('name'), $(this).val());
						});


						$.ajax({
							url: '/custom/admin/app/Controllers/material/material_upsert_controller.php',
							type: 'POST',
							data: form_data,
							processData: false,
							contentType: false,
							dataType: 'json',
							success: function(response) {
								callback();
							},
							error: function(jqXHR, textStatus, errorThrown) {
								callback();
							}
						});
					} else {
						let current_chunk = 0;

						function upload_chunk() {
							if (current_chunk >= total_chunks) {
								$.ajax({
									url: '/custom/admin/app/Controllers/material/material_upsert_controller.php',
									type: 'POST',
									data: {
										file_name: file.name,
										csrf_token: $('#upsert_form').find('[name="csrf_token"]').val(),
										id: fileObj.id,
										course_no: $('#upsert_form').find('[name="course_no"]').val()
									},
									dataType: 'json',
									success: function(response) {
										callback();
									},
									error: function(jqXHR, textStatus, errorThrown) {
										var response;
										try {
											response = JSON.parse(jqXHR.responseText);
										} catch (e) {
											response = {
												error: jqXHR.responseText
											};
										}
										var errText = (typeof response.error === 'object') ? (response.error.message || JSON.stringify(response.error)) : response.error;
										$("#ajax-error-message-global").html('<div class="text-danger">' + (errText || 'アップロードに失敗しました') + '</div>').show();
									}
								});
								return;
							}

							const start = current_chunk * chunk_size;
							const end = Math.min(start + chunk_size, file.size);
							const chunk = file.slice(start, end);
							const form_data = new FormData();
							form_data.append('id', fileObj.id);
							form_data.append('course_no', $('#upsert_form').find('[name="course_no"]').val());
							form_data.append('course_info_id', $('#upsert_form').find('[name="course_info_id"]').val());
							form_data.append('csrf_token', $('#upsert_form').find('[name="csrf_token"]').val());
							form_data.append('event_id', $('#upsert_form').find('[name="event_id"]').val());
							form_data.append('category_id', $('#upsert_form').find('[name="category_id"]').val());
							form_data.append('material_id', $('#upsert_form').find('[name="material_id"]').val());
							form_data.append('file', chunk);
							form_data.append('chunk_index', current_chunk);
							form_data.append('total_chunks', total_chunks);
							form_data.append('file_name', file.name);
							form_data.append('total_file_size', file.size);

							$('#upsert_form').find('input[name^="ids"]').each(function() {
								form_data.append($(this).attr('name'), $(this).val());
							});
							$('#upsert_form').find('input[name^="files"]').each(function() {
								form_data.append($(this).attr('name'), $(this).val());
							});


							$.ajax({
								url: '/custom/admin/app/Controllers/material/material_upsert_controller.php',
								type: 'POST',
								data: form_data,
								processData: false,
								contentType: false,
								dataType: 'json',
								success: function(response) {
									if (response.status === 'error') {
										callback();
										return;
									}
									const percentage = Math.round(((current_chunk + 1) / total_chunks) * 100);
									$('#percent').text(`${percentage}%`);
									current_chunk++;
									setTimeout(upload_chunk, 1000);
								},
								error: function(jqXHR, textStatus, errorThrown) {
									var response;
									try {
										response = JSON.parse(jqXHR.responseText);
									} catch (e) {
										response = {
											error: jqXHR.responseText
										};
									}
									var errText = (typeof response.error === 'object') ? (response.error.message || JSON.stringify(response.error)) : response.error;
									$("#ajax-error-message-global").html('<div class="text-danger">' + (errText || 'アップロードに失敗しました') + '</div>').show();
								}

							});
						}
						upload_chunk();
					}
				}

				function uploadNextFile(index) {
					if (index < filesToUpload.length) {
						uploadFile(filesToUpload[index], function() {
							uploadNextFile(index + 1);
						});
					} else {
						location.href = "/custom/admin/app/Views/event/material.php";
					}
				}
				uploadNextFile(0);
			});

			function create_file_link(url, file_name) {
				const file_link_container = document.createElement('div');
				file_link_container.classList.add('fileInfoItem', 'd-flex', 'align-items-center', 'mb-2');

				const link = document.createElement('a');
				link.href = url;
				link.target = '_blank';
				link.classList.add('fileLink', 'd-flex', 'align-items-center', 'text-decoration-none');

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

			(function init_existing_files() {
				const existing_materials = <?= json_encode($material_list, JSON_UNESCAPED_UNICODE) ?>;
				for (const key in existing_materials) {
					if (existing_materials.hasOwnProperty(key)) {
						const material = existing_materials[key];
						const material_id = material.id;
						const file_name = material.file_name;
						if (file_name !== "") {
							const container = document.querySelector(`.material-container[data-material-id="${material_id}"]`);
							if (!container) continue;
							const row = container.querySelector('.uploadRow');
							if (!row) continue;
							const file_info = row.querySelector('.fileInfo');
							if (!file_info) continue;
							const link_elem = create_file_link('/uploads/material/' + file_name, file_name);
							file_info.appendChild(link_elem);
							file_info.classList.remove('d-none');
						}
					}
				}
				feather.replace();
			})();

			$('#add-btn').on('click', function(e) {
				e.preventDefault();
				const template = document.getElementById('uploadRowTemplate');
				if (!template) {
					return;
				}
				let materialContainer = $('.material-container').last();
				if (materialContainer.length === 0) {
					materialContainer = $('<div class="material-container mb-4"></div>');
					const fieldsContainer = $('<div class="fields-container"></div>');
					materialContainer.append(fieldsContainer);
					$('#add-btn').closest('.d-flex').before(materialContainer);
				}
				let fieldsContainer = materialContainer.find('.fields-container').first();
				if (!fieldsContainer.length) {
					fieldsContainer = $('<div class="fields-container"></div>');
					materialContainer.append(fieldsContainer);
				}
				let indices = [];
				fieldsContainer.find('.fileUpload').each(function() {
					const nameAttr = $(this).attr('name');
					const match = nameAttr.match(/^files\[(\d+)\]$/);
					if (match) {
						indices.push(parseInt(match[1], 10));
					}
				});
				let index = 0;
				if (indices.length > 0) {
					index = Math.max(...indices) + 1;
				}
				const clone = document.importNode(template.content, true);
				let $newRow = $(clone).find('.uploadRow');
				$newRow.find('.fileUpload').attr('name', 'files[' + index + ']');
				$newRow.find('.hiddenField').attr('name', 'files[' + index + ']');
				fieldsContainer.append($newRow);
				feather.replace();
			});
		});
	</script>

</body>

</html>