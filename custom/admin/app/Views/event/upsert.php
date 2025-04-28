<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/event/event_edit_controller.php');
require_once('/var/www/html/moodle/custom/helpers/form_helpers.php');

// id を取得
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// コントローラに id を渡す
$controller = new EventEditController();
$eventData = $controller->getEventData($id);
$select_categorys = isset($eventData['select_categorys']) ? $eventData['select_categorys'] : [];
if (!empty($id)) {
	$tickets = $controller->getTicketCount($id);
} else {
	$tickets = [];
}

$start_event_flg = false;
if (isset($eventData) && $eventData['event_status'] == EVENT_END) {
	$start_event_flg = true;
}

$every_event_flg = false;
if (isset($eventData) && $eventData['event_kbn'] == EVERY_DAY_EVENT) {
	$every_event_flg = true;
}

// セッションからエラーメッセージを取得
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];

$course_array = array();
$courses = array();
for ($i = 1; $i < 10; $i++) {
	if (!empty($old_input)) {
		$j = 0;
		$n = 1;
		while (isset($old_input["tutor_id_{$i}_{$n}"])) {
			$course_info_id = empty($old_input["course_info_id_{$i}_{$n}"]) ? null : $old_input["course_info_id_{$i}_{$n}"];
			$tutor_id = $old_input["tutor_id_{$i}_{$n}"] ?? null;
			$lecture_name = $old_input["lecture_name_{$i}_{$n}"] ?? null;
			$program = $old_input["program_{$i}_{$n}"] ?? null;
			$tutor_name = $old_input["tutor_name_{$i}_{$n}"] ?? null;
			if (
				!is_null($course_info_id)
				|| !is_null($tutor_id)
				|| !is_null($lecture_name)
				|| !is_null($program)
				|| !is_null($tutor_name)
			) {
				$courses[$i][$j] = [
					'id' => $course_info_id,
					'tutor_id' => $tutor_id,
					'name' =>  $lecture_name,
					'program' => $program,
					'tutor_name' => $tutor_name,
					'no' => $i,
				];
			}
			$j++;
			$n++;
		}
	} else {
		if (isset($eventData['select_course'][$i]['details'])) {
			$courses[$i] = $eventData['select_course'][$i]['details'];
		}
	}
}
if (count($courses) < 1) {
	$courses[1] = [[
		'id' => null,
		'tutor_id' => null,
		'name' =>  null,
		'program' => null,
		'tutor_name' => null,
		'no' => $i,
	]];
}
for ($i = 1; $i < 10; $i++) {
	$course_array[$i] = [[
		'id' => null,
		'tutor_id' => null,
		'name' =>  null,
		'program' => null,
		'tutor_name' => null,
		'no' => $i,
	]];
}


$event_kbns = EVENT_KBN_LIST;
unset($_SESSION['errors'], $_SESSION['old_input']); // 一度表示したら削除
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
					<p class="title header-title ms-4 fs-4 fw-bold mb-0">イベント登録</p>
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
							<p class="content_title p-3">イベント登録
								<?php if (count($tickets) > 0): ?><span style="color: red;"> ※すでに申込があるため一部更新ができません。</span><?php endif; ?>
							</p>
							<div class="form-wrapper">
								<?php if (isset($eventData) && !$start_event_flg && count($tickets) == 0): ?>
									<form method="POST" action="/custom/admin/app/Controllers/event/event_delete_controller.php" enctype="multipart/form-data">
										<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
										<input type="hidden" name="del_event_id" value="<?= $id ?? '' ?>">
										<span class="form-label d-flex align-items-center">
											<button type="submit" id="del_submit" class="add_colum_lecture btn btn-danger ms-auto me-0">イベント削除</button>
										</span>
									</form>
								<?php endif ?>
								<form method="POST" action="/custom/admin/app/Controllers/event/event_upsert_controller.php" enctype="multipart/form-data">
									<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
									<input type="hidden" name="action" value="createUpdate">
									<input type="hidden" id="event_id" name="id" value="<?= $id ?? '' ?>">
									<div class=" mb-3">
										<label class="form-label">イベント区分　※一度登録すると変更できません。間違えた場合はイベントを削除してください。</label>
										<select name="event_kbn" class="form-control mb-3" <?php if ($start_event_flg): ?>style="pointer-events: none; background-color: #e6e6e6;" tabindex="-1" <?php endif ?>>
											<?php foreach ($event_kbns as $kbn_id => $name): ?>
												<option value="<?= htmlspecialchars($kbn_id) ?>"
													<?= isSelected($kbn_id, $eventData['event_kbn'] ?? null, $old_input['event_kbn'] ?? null) ? 'selected' : '' ?>>
													<?= htmlspecialchars($name) ?>
												</option>
											<?php endforeach; ?>
										</select>
										<?php if (!empty($errors['event_kbn'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['event_kbn']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">イベントタイトル</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input type="name" name="name" class="form-control" placeholder=""
											value="<?= htmlspecialchars(isSetValue($eventData['name'] ?? '', ($old_input['name'] ?? ''))) ?>" />
										<?php if (!empty($errors['name'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['name']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">説明文</label>
										</div>
										<textarea name="description" class=" form-control" rows="5"><?= htmlspecialchars(isSetValue($eventData['description'] ?? '', ($old_input['description'] ?? ''))) ?></textarea>
										<?php if (!empty($errors['description'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['description']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">カテゴリー</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<select id="category_id" name="category_id[]" class="form-control choices-multiple mb-3" multiple>
											<?php foreach ($categorys as $category): ?>
												<option value="<?= htmlspecialchars($category['id']) ?>"
													<?= isChoicesSelected($category['id'], $select_categorys ?? null, $old_input['category_id'] ?? null) ? 'selected' : '' ?>>
													<?= htmlspecialchars($category['name']) ?>
												</option>
											<?php endforeach; ?>
										</select>
										<?php if (!empty($errors['category_id'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['category_id']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">サムネール画像</label>
										</div>
										<div class="mb-3">
											<input type="file" name="thumbnail_img" id="thumbnail_img" class="form-control" accept=".png,.jpeg,.jpg">
										</div>
										<div id="image-preview" class="mb-3">
											<!-- プレビュー画像がここに表示されます -->
										</div>
										<?php if (isset($eventData['thumbnail_img']) && !empty($eventData['thumbnail_img'])): ?>
											<div class="mb-3">
												<img class="fit-picture"
													id="thumbnail_img_tag"
													src="<?= htmlspecialchars($eventData['thumbnail_img']) ?>"
													width="300" />
												<button type="button" class="delete-link delete_btn btn btn-danger ms-auto me-0" data-id="<?= $id ?>">削除</button>
											</div>
										<?php endif; ?>
										<?php if (!empty($errors['thumbnail_img'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['thumbnail_img']); ?></div>
										<?php endif; ?>
									</div>

									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">講義形式</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<select id="lecture_format_id" name="lecture_format_id[]" class=" form-control choices-multiple mb-3" multiple>
											<optgroup label="">
												<?php foreach ($lectureFormats as $lectureFormat): ?>
													<option value="<?= htmlspecialchars($lectureFormat['id']) ?>"
														<?= isChoicesSelected($lectureFormat['id'], $eventData['select_lecture_formats'] ?? null, $old_input['lecture_format_id'] ?? null) ? 'selected' : '' ?>>
														<?= htmlspecialchars($lectureFormat['name']) ?>
													</option>
												<?php endforeach; ?>
											</optgroup>
										</select>
										<?php if (!empty($errors['lecture_format_id'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['lecture_format_id']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">会場名</label>
										<input name="venue_name" class=" form-control" type="text"
											value="<?= htmlspecialchars(isSetValue($eventData['venue_name'] ?? '', ($old_input['venue_name'] ?? ''))) ?>" />
										<?php if (!empty($errors['venue_name'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['venue_name']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">対象</label>
										<select id="target" class=" form-control mb-3" name="target">
											<?php foreach ($targets as $target): ?>
												<option value="<?= htmlspecialchars($target['id']) ?>"
													<?= isSelected($target['id'], $eventData['target'] ?? null, $old_input['target'] ?? null) ? 'selected' : '' ?>>
													<?= htmlspecialchars($target['name']) ?>
												</option>
											<?php endforeach; ?>
										</select>
										<?php if (!empty($errors['target'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['target']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3 onetime_area">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">開催日</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input type="date" name="event_date" class="form-control"
											value="<?= htmlspecialchars(isSetDate($eventData['event_date'] ?? '', $old_input['event_date'] ?? '')) ?>" />
										<?php if (!empty($errors['event_date'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['event_date']); ?></div>
										<?php endif; ?>
									</div>
									<?php if (!is_mobile_device()): ?>
										<div class="mb-3 term_area sp-none">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催期間</label>
												<span class="badge bg-danger">必須</span><label>　※一度登録すると変更できません。間違えた場合はイベントを削除してください。</label>
											</div>
											<div style="display: flex;">
												<input type="date" name="start_event_date" class="form-control"
													value="<?= htmlspecialchars(isSetDate($eventData['start_event_date'] ?? '', $old_input['start_event_date'] ?? '')) ?>"
													<?php if ($start_event_flg || $every_event_flg): ?>style="background-color: #e6e6e6;" readonly<?php endif ?> /> <span class="ps-2 pe-2">～</span>
												<input type="date" name="end_event_date" class="form-control"
													value="<?= htmlspecialchars(isSetDate($eventData['end_event_date'] ?? '', $old_input['end_event_date'] ?? '')) ?>"
													<?php if ($start_event_flg || $every_event_flg): ?>style="background-color: #e6e6e6;" readonly<?php endif ?> />
											</div>
											<?php if (!empty($errors['start_event_date'])): ?>
												<div class="text-danger mt-2"><?= htmlspecialchars($errors['start_event_date']); ?></div>
											<?php endif; ?>
											<?php if (!empty($errors['end_event_date'])): ?>
												<div class="text-danger mt-2"><?= htmlspecialchars($errors['end_event_date']); ?></div>
											<?php endif; ?>
										</div>
									<?php endif; ?>
									<?php if (is_mobile_device()): ?>
										<div class="mb-3 term_area pc-none">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催期間( 開始日 )</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<div class="form-label d-flex align-items-center"><label>　※一度登録すると変更できません。間違えた場合はイベントを削除してください。</label></div>
											<input type="date" name="start_event_date" class="form-control w-100"
												value="<?= htmlspecialchars(isSetDate($eventData['start_event_date'] ?? '', $old_input['start_event_date'] ?? '')) ?>"
												<?php if ($start_event_flg || $every_event_flg): ?>style="background-color: #e6e6e6;" readonly<?php endif ?> />
										</div>
										<div class="mb-3 term_area pc-none">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催期間( 終了日 )</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input type="date" name="end_event_date" class="form-control w-100"
												value="<?= htmlspecialchars(isSetDate($eventData['end_event_date'] ?? '', $old_input['end_event_date'] ?? '')) ?>"
												<?php if ($start_event_flg || $every_event_flg): ?>style="background-color: #e6e6e6;" readonly<?php endif ?> />
										</div>
										<?php if (!empty($errors['start_event_date'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['start_event_date']); ?></div>
										<?php endif; ?>
										<?php if (!empty($errors['end_event_date'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['end_event_date']); ?></div>
										<?php endif; ?>
									<?php endif; ?>
									<?php if (!is_mobile_device()): ?>
										<div class=" mb-3 sp-none">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">時間</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="start_hour" class="timepicker" type="text"
												value="<?= htmlspecialchars(isSetValue($eventData['start_hour'] ?? '', ($old_input['start_hour'] ?? ''))) ?>" /> <span class="ps-2 pe-2">～</span>
											<input name="end_hour" class="timepicker" type="text"
												value="<?= htmlspecialchars(isSetValue($eventData['end_hour'] ?? '', ($old_input['end_hour'] ?? ''))) ?>" />
											<?php if (!empty($errors['start_hour'])): ?>
												<div class="text-danger mt-2"><?= htmlspecialchars($errors['start_hour']); ?></div>
											<?php endif; ?>
											<?php if (!empty($errors['end_hour'])): ?>
												<div class="text-danger mt-2"><?= htmlspecialchars($errors['end_hour']); ?></div>
											<?php endif; ?>
										</div>
									<?php endif; ?>
									<?php if (is_mobile_device()): ?>
										<div class="mb-3 pc-none">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">時間( 開始時間 )</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="start_hour" class="timepicker w-100" type="text" placeholder="" value="<?= htmlspecialchars(isSetValue($eventData['start_hour'] ?? '', ($old_input['start_hour'] ?? ''))) ?>">
										</div>
										<div class="mb-3 pc-none">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">時間( 終了時間 )</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="end_hour" class="timepicker w-100" type="text" placeholder="" value="<?= htmlspecialchars(isSetValue($eventData['end_hour'] ?? '', ($old_input['end_hour'] ?? ''))) ?>">
										</div>
									<?php endif; ?>
									<div class=" mb-3">
										<label class="form-label">交通アクセス</label>
										<textarea name="access" class=" form-control" rows="5"><?= htmlspecialchars(isSetValue($eventData['access'] ?? '', $old_input['access'] ?? '')) ?></textarea>
										<?php if (!empty($errors['access'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['access']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="form-label">Google Map&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://www.google.co.jp/maps/?hl=ja" target="_blank">Google Mapを開く</a></label>
										</div>
										<div class="mb-3">
											<input name="google_map" class=" form-control" type="text"
												value="<?= htmlspecialchars(isSetValue($eventData['google_map'] ?? '', ($old_input['google_map'] ?? ''))) ?>" />
											<?php if (!empty($errors['google_map'])): ?>
												<div class="text-danger mt-2"><?= $errors['google_map']; ?></div>
											<?php endif; ?>
										</div>
										<div class="mb-3">
											<?php if (!is_null($eventData) && !empty($eventData['google_map'])): ?>
												<?= $eventData['google_map'] ?>
											<?php endif; ?>
										</div>
									</div>
									<div class="mb-3">
										<label class="form-label">
											<input name="is_top" type="checkbox" value="1" <?php if (isset($eventData['is_top']) && !empty($eventData['is_top']) || !empty($old_input['is_top'])): ?>checked<?php endif; ?> class="form-check-input">
											<span class="form-check-label">トップに固定する</span>
										</label>
									</div>
									<div class="mb-3">
										<label class="form-label">
											<input id="is_best" name="is_best" type="checkbox" value="1" <?php if (isset($eventData['is_best']) && !empty($eventData['is_best']) || !empty($old_input['is_best'])): ?>checked<?php endif; ?> class="form-check-input">
											<span class="form-check-label">推しイベントに設定する</span>
										</label>
									</div>
									<div class="mb-3" id="best_event_img_tag" <?php if(empty($eventData['is_best'] ?? null)): ?>style="display: none;"<?php endif; ?>>
										<div class="form-label d-flex align-items-center">
											<label class="me-2">推しイベント画像 パソコン表示用</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<div class="mb-3">
											<input type="file" id="best_event_img" name="best_event_img" class="form-control" accept=".png,.jpeg,.jpg">
										</div>
										<div id="best-image-preview" class="mb-3">
											<!-- プレビュー画像がここに表示されます -->
										</div>
										<?php if (isset($eventData['best_event_img']) && !empty($eventData['best_event_img'])): ?>
											<div class="mb-3">
												<input type="hidden" name="best_event_img_tag" value="1" >
												<img class="fit-picture"
													id="best_event_img_tag"
													src="<?= htmlspecialchars($eventData['best_event_img']) ?>"
													width="300" />
											</div>
										<?php endif; ?>
										<?php if (!empty($errors['best_event_img'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['best_event_img']); ?></div>
										<?php endif; ?>
										<div class="form-label d-flex align-items-center">
											<label class="me-2">推しイベント画像 スマホ表示用</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<div class="mb-3">
											<input type="file" id="best_event_sp_img" name="best_event_sp_img" class="form-control" accept=".png,.jpeg,.jpg">
										</div>
										<div id="best-sp-image-preview" class="mb-3">
											<!-- プレビュー画像がここに表示されます -->
										</div>
										<?php if (isset($eventData['best_event_sp_img']) && !empty($eventData['best_event_sp_img'])): ?>
											<div class="mb-3">
											<input type="hidden" name="best_event_sp_img_tag" value="1" >
												<img class="fit-picture"
													id="best_event_sp_img_tag"
													src="<?= htmlspecialchars($eventData['best_event_sp_img']) ?>"
													width="300" />
											</div>
										<?php endif; ?>
										<?php if (!empty($errors['best_event_sp_img'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['best_event_sp_img']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">
											<input name="is_tekijuku_only" type="checkbox" value="1" <?php if (isset($eventData['is_tekijuku_only']) && !empty($eventData['is_tekijuku_only']) || !empty($old_input['is_tekijuku_only'])): ?>checked<?php endif; ?> class="form-check-input">
											<span class="form-check-label">適塾会員限定イベント</span>
										</label>
									</div>
									<div class="mb-3 one_area">
										<?php foreach ($courses as $no => $details): ?>
											<?php if($no == 1): ?>
												<?php foreach ($details as $key => $detail): ?>
													<?php if($key == 0): ?>
														<input type="hidden" id="course_info_id" name="course_info_id" value="<?= htmlspecialchars($eventData['select_course'][1]['id'] ?? '') ?>">
														<div class="mb-3">
															<div class="form-label d-flex align-items-center">
																<label class="me-2">アーカイブ公開日</label>
															</div>
																<input name="release_date" class="form-control" type="date"
															value="<?= htmlspecialchars(isSetDate ($eventData['select_course'][1]['release_date'] ?? '', $old_input['release_date'] ?? '')) ?>" />
																<?php if (!empty($errors['release_date'])): ?>
																	<div class="text-danger mt-2"><?= htmlspecialchars($errors['release_date']); ?></div>
																<?php endif; ?>
														</div>
														<div class="mb-3">
															<div class="form-label d-flex align-items-center">
																<label class="me-2">講義資料公開日</label>
															</div>
															<input name="material_release_date" class="form-control" type="date"
																value="<?= htmlspecialchars(isSetDate ($eventData['select_course'][1]['material_release_date'] ?? '', $old_input['material_release_date'] ?? '')) ?>" />
															<?php if (!empty($errors['material_release_date'])): ?>
																<div class="text-danger mt-2"><?= htmlspecialchars($errors['material_release_date']); ?></div>
															<?php endif; ?>
														</div>
													<?php endif ?>
													<div class="form-label d-flex align-items-center">
														<label class="me-2">講師</label>
													</div>
													<select id="tutor_id_<?= $key ?>" class=" form-control mb-3" name="tutor_id_<?= $key ?>">
														<optgroup label="">
															<option value="">講師無し</option>
															<?php foreach ($tutors as $tutor): ?>
																<option value="<?= htmlspecialchars($tutor['id']) ?>"
																<?= isSelected($tutor['id'], $detail['tutor_id'] ?? null, $old_input['tutor_id_' . $key] ?? null) ? 'selected' : '' ?>>
																	<?= htmlspecialchars($tutor['name']) ?>
																</option>
															<?php endforeach; ?>
														</optgroup>
													</select>
													<div id="tutor_name_area_<?= $key ?>" class="mb-3" <?php if(!empty($detail['tutor_id'] ?? null)): ?>style="display: none;"<?php endif; ?>>
														<div class="form-label d-flex align-items-center">
															<label class="me-2">講師名</label>
														</div>
														<input type="text" name="tutor_name_<?= $key ?>" class="form-control" placeholder=""
															value="<?= htmlspecialchars(isSetValue($detail['tutor_name'] ?? '', $old_input['tutor_name_' . $key] ?? '')) ?>" />
														<?php if (!empty($errors['tutor_name_' . $key])): ?>
															<div class="text-danger mt-2"><?= htmlspecialchars($errors['tutor_name_' . $key]); ?></div>
														<?php endif; ?>
													</div>
													<?php if (!empty($errors['tutor_id_' . $key])): ?>
														<div class="text-danger mt-2"><?= htmlspecialchars($errors['tutor_id_' . $key]); ?></div>
													<?php endif; ?>
													<div class="mb-3">
														<div class="form-label d-flex align-items-center">
															<label class="me-2">講義名</label>
															<span class="badge bg-danger">必須</span>
														</div>
														<input type="text" name="lecture_name_<?= $key ?>" class="form-control" placeholder=""
															value="<?= htmlspecialchars(isSetValue($detail['name'] ?? '', $old_input['lecture_name_' . $key] ?? '')) ?>" />
														<?php if (!empty($errors['lecture_name_' . $key])): ?>
															<div class="text-danger mt-2"><?= htmlspecialchars($errors['lecture_name_' . $key]); ?></div>
														<?php endif; ?>
													</div>
													<div class="mb-5">
														<div class="form-label d-flex align-items-center">
															<label class="me-2">講義概要</label>
															<span class="badge bg-danger">必須</span>
														</div>
														<textarea name="program_<?= $key ?>" class=" form-control" rows="5"><?= htmlspecialchars(isSetValue($detail['program'] ?? '', $old_input['program_' . $key] ?? '')) ?></textarea>
														<?php if (!empty($errors['program_' . $key])): ?>
															<div class="text-danger mt-2"><?= htmlspecialchars($errors['program_' . $key]); ?></div>
														<?php endif; ?>
													</div>
													<hr>
												<?php endforeach; ?>
											<?php endif ?>
										<?php endforeach; ?>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<button type="button" class="add_colum btn btn-primary ms-auto me-0" data-target="">項目追加</button>
											</div>
										</div>
									</div>

									<div class="repeatedly_area">
										<?php foreach($course_array as $i => $row): ?>
											<input type="hidden" id="course_info_id_<?= $i ?>" name="course_info_id_<?= $i ?>" value="<?= $eventData['select_course'][$i]['id'] ?? '' ?>">
											<div class="mb-3">
												<P class="fs-5 fw-bold">第<?= $i ?>講座</P>
												<div class="form-label d-flex align-items-center">
													<label class="me-2">開催日</label>
													<?php if($i < 3): ?><span class="badge bg-danger">必須</span><?php endif; ?>
												</div>
												<input name="course_date_<?= $i ?>" class="form-control" type="date"
                                            value="<?= htmlspecialchars(isSetDate ($eventData['select_course'][$i]['course_date'] ?? '', $old_input['course_date_' . $i] ?? '')) ?>" />
												<?php if (!empty($errors['course_date_' . $i])): ?>
													<div class="text-danger mt-2"><?= htmlspecialchars($errors['course_date_' . $i]); ?></div>
												<?php endif; ?>
											</div>
											<div class="mb-3">
												<div class="form-label d-flex align-items-center">
													<label class="me-2">アーカイブ公開日</label>
												</div>
												<input name="release_date_<?= $i ?>" class="form-control" type="date"
                                            value="<?= htmlspecialchars(isSetDate ($eventData['select_course'][$i]['release_date'] ?? '', $old_input['release_date_' . $i] ?? '')) ?>" />
												<?php if (!empty($errors['release_date_' . $i])): ?>
													<div class="text-danger mt-2"><?= htmlspecialchars($errors['release_date_' . $i]); ?></div>
												<?php endif; ?>
											</div>
											<div class="mb-3">
												<div class="form-label d-flex align-items-center">
													<label class="me-2">講義資料公開日</label>
												</div>
												<input name="material_release_date_<?= $i ?>" class="form-control" type="date"
													value="<?= htmlspecialchars(isSetDate ($eventData['select_course'][$i]['material_release_date'] ?? '', $old_input['material_release_date_' . $i] ?? '')) ?>" />
												<?php if (!empty($errors['material_release_date'])): ?>
													<div class="text-danger mt-2"><?= htmlspecialchars($errors['material_release_date_' . $i]); ?></div>
												<?php endif; ?>
											</div>
											<?php if (isset($courses[$i])): ?>
												<?php $details = $courses[$i]; ?>
												<?php foreach ($details as $key => $detail): ?>
													<div id="area_<?= $i ?>_<?= $key+1 ?>">
														<div class="mb-3">
															<div class="form-label d-flex align-items-center">
																<label class="me-2">講師</label>
															</div>
															<select id="tutor_id_<?= $i ?>_<?= $key+1 ?>" class="form-control mb-3" name="tutor_id_<?= $i ?>_<?= $key+1 ?>">
																<option value="">講師無し</option>
																<?php foreach ($tutors as $tutor): ?>
																	<option value="<?= htmlspecialchars($tutor['id']) ?>"
																		<?= isSelected($tutor['id'], $detail['tutor_id'] ?? null, null) ? 'selected' : '' ?>>
																		<?= htmlspecialchars($tutor['name']) ?>
																	</option>
																<?php endforeach; ?>
															</select>
															<?php if (!empty($errors['tutor_id_' . $i . '_' . $key+1])): ?>
																<div class="text-danger mt-2"><?= htmlspecialchars($errors['tutor_id_' . $i . '_' . $key+1]); ?></div>
															<?php endif; ?>
														</div>
														<div id="tutor_name_area_<?= $i ?>_<?= $key+1 ?>" class="mb-3" <?php if(!is_null($detail['tutor_id'] ?? null)): ?>style="display: none;"<?php endif; ?>>
															<div class="form-label d-flex align-items-center">
																<label class="me-2">講師名</label>
															</div>
															<input type="text" name="tutor_name_<?= $i ?>_<?= $key+1 ?>" class="form-control"
																value="<?= htmlspecialchars($detail['tutor_name'] ?? '') ?>">
															<?php if (!empty($errors['tutor_name_' . $i . '_' . $key+1])): ?>
																<div class="text-danger mt-2"><?= htmlspecialchars($errors['tutor_name_' . $i . '_' . $key+1]); ?></div>
															<?php endif; ?>
														</div>
														<div class="mb-3">
															<div class="form-label d-flex align-items-center">
																<label class="me-2">講義名</label>
																<?php if($i < 3): ?><span class="badge bg-danger">必須</span><?php endif; ?>
															</div>
															<input type="text" name="lecture_name_<?= $i ?>_<?= $key+1 ?>" class="form-control"
																value="<?= htmlspecialchars($detail['name'] ?? '') ?>">
															<?php if (!empty($errors['lecture_name_' . $i . '_' . $key+1])): ?>
																<div class="text-danger mt-2"><?= htmlspecialchars($errors['lecture_name_' . $i . '_' . $key+1]); ?></div>
															<?php endif; ?>
														</div>
														<div class="mb-3">
															<div class="form-label d-flex align-items-center">
																<label class="me-2">講義概要</label>
																<?php if($i < 3): ?><span class="badge bg-danger">必須</span><?php endif; ?>
															</div>
															<textarea name="program_<?= $i ?>_<?= $key+1 ?>" class="form-control"><?= htmlspecialchars($detail['program'] ?? '') ?></textarea>
															<?php if (!empty($errors['program_' . $i . '_' . $key+1])): ?>
																<div class="text-danger mt-2"><?= htmlspecialchars($errors['program_' . $i . '_' . $key+1]); ?></div>
															<?php endif; ?>
														</div>
													</div>
													<hr>
												<?php endforeach; ?>
											<?php else: ?>
												<div id="area_<?= $i ?>_1">
													<div class="mb-3">
														<div class="form-label d-flex align-items-center">
															<label class="me-2">講師</label>
														</div>
														<select id="tutor_id_<?= $i ?>_1" class="form-control mb-3" name="tutor_id_<?= $i ?>_1">
															<option value="">講師無し</option>
															<?php foreach ($tutors as $tutor): ?>
																<option value="<?= htmlspecialchars($tutor['id']) ?>">
																	<?= htmlspecialchars($tutor['name']) ?>
																</option>
															<?php endforeach; ?>
														</select>
														<?php if (!empty($errors['tutor_id_' . $i . '_1'])): ?>
															<div class="text-danger mt-2"><?= htmlspecialchars($errors['tutor_id_' . $i . '_1']); ?></div>
														<?php endif; ?>
													</div>
													<div id="tutor_name_area_<?= $i ?>_1" class="mb-3">
														<div class="form-label d-flex align-items-center">
															<label class="me-2">講師名</label>
														</div>
														<input type="text" name="tutor_name_<?= $i ?>_1" class="form-control" value="">
														<?php if (!empty($errors['tutor_name_' . $i . '_1'])): ?>
															<div class="text-danger mt-2"><?= htmlspecialchars($errors['tutor_name_' . $i . '_1']); ?></div>
														<?php endif; ?>
													</div>
													<div class="mb-3">
														<div class="form-label d-flex align-items-center">
															<label class="me-2">講義名</label>
															<?php if($i < 3): ?><span class="badge bg-danger">必須</span><?php endif; ?>
														</div>
														<input type="text" name="lecture_name_<?= $i ?>_1" class="form-control" value="">
														<?php if (!empty($errors['lecture_name_' . $i . '_1'])): ?>
															<div class="text-danger mt-2"><?= htmlspecialchars($errors['lecture_name_' . $i . '_1']); ?></div>
														<?php endif; ?>
													</div>
													<div class="mb-3">
														<div class="form-label d-flex align-items-center">
															<label class="me-2">講義概要</label>
															<?php if($i < 3): ?><span class="badge bg-danger">必須</span><?php endif; ?>
														</div>
														<textarea name="program_<?= $i ?>_1" class="form-control"></textarea>
														<?php if (!empty($errors['program_' . $i . '_1'])): ?>
															<div class="text-danger mt-2"><?= htmlspecialchars($errors['program_' . $i . '_1']); ?></div>
														<?php endif; ?>
													</div>
												</div>
												<hr>
											<?php endif; ?>
											<div class="mb-3">
												<div class="form-label d-flex align-items-center">
													<button type="button" class="add_colum_lecture btn btn-primary ms-auto me-0" data-target="<?= $i ?>">項目追加</button>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
									<!-- <div class="mb-3">
										<label class="form-label">プログラム</label>
										<textarea name="program" class=" form-control" rows="5"><?= htmlspecialchars($eventData['program'] ?? ($old_input['program'] ?? '')) ?></textarea>
										<?php if (!empty($errors['program'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['program']); ?></div>
										<?php endif; ?>
									</div> -->
									<div class="mb-3">
										<label class="form-label">主催</label>
										<input name="sponsor" class=" form-control" type="text"
                                            value="<?= htmlspecialchars(isSetValue($eventData['sponsor'] ?? '', $old_input['sponsor'] ?? '')) ?>" />
										<?php if (!empty($errors['sponsor'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['sponsor']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">共催</label>
										<input name="co_host" class="form-control" type="text"
                                            value="<?= htmlspecialchars(isSetValue($eventData['co_host'] ?? '', $old_input['co_host'] ?? '')) ?>" />
										<?php if (!empty($errors['co_host'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['co_host']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">後援</label>
										<input name="sponsorship" class="form-control" type="text"
                                            value="<?= htmlspecialchars(isSetValue($eventData['sponsorship'] ?? '', $old_input['sponsorship'] ?? '')) ?>" />
										<?php if (!empty($errors['sponsorship'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['sponsorship']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">協力</label>
										<input name="cooperation" class=" form-control" type="text"
                                            value="<?= htmlspecialchars(isSetValue($eventData['cooperation'] ?? '', $old_input['cooperation'] ?? '')) ?>" />
										<?php if (!empty($errors['cooperation'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['cooperation']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">企画</label>
										<input name="plan" class="form-control" type="text"
                                            value="<?= htmlspecialchars(isSetValue($eventData['plan'] ?? '', $old_input['plan'] ?? '')) ?>" />
										<?php if (!empty($errors['plan'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['plan']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">お問い合わせ先メールアドレス</label>
										<span class="badge bg-danger">必須</span>
										<input type="email" name="inquiry_mail" class="form-control" value="<?= htmlspecialchars(isSetValue($eventData['inquiry_mail'] ?? '', $old_input['inquiry_mail'] ?? '')) ?>"
											inputmode="email"
											autocomplete="email"
											oninput="this.value = this.value.replace(/[^a-zA-Z0-9@._-]/g, '');">
										<?php if (!empty($errors['inquiry_mail'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['inquiry_mail']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">定員</label><label>　※未入力、または0の場合、無制限になります。</label>
										<input name="capacity" class=" form-control" min="0" type="number"
                                            value="<?= htmlspecialchars(isSetValue($eventData['capacity'] ?? '', $old_input['capacity'] ?? '')) ?>" />
										<?php if (!empty($errors['capacity'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['capacity']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label" id="participation_fee_label">参加費<?php if(!empty($eventData) && $eventData['event_kbn'] == PLURAL_EVENT): ?>( 全て受講 )<?php endif; ?></label><label>　※申込が発生すると変更が出来なくなります。</label>
										<input name="participation_fee" class=" form-control" min="0" type="number"
                                            value="<?= htmlspecialchars(isSetValue($eventData['participation_fee'] ?? '', $old_input['participation_fee'] ?? '')) ?>"
											<?php if(!empty(count($tickets) > 0)): ?>style="background-color: #e6e6e6;" readonly<?php endif; ?>
											 />
										<?php if (!empty($errors['participation_fee'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['participation_fee']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3 repeatedly_area">
										<label class="form-label" id="single_participation_fee_label">参加費</label><label>　※申込が発生すると変更が出来なくなります。</label>
										<input name="single_participation_fee" class=" form-control" min="0" type="number"
                                            value="<?= htmlspecialchars(isSetValue($eventData['single_participation_fee'] ?? '', $old_input['single_participation_fee'] ?? '')) ?>"
											<?php if(!empty($ticket_count) && $ticket_count > 0): ?>style="background-color: #e6e6e6;" readonly<?php endif ?> />
										<?php if (!empty($errors['single_participation_fee'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['single_participation_fee']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label" id="tekijuku_discount_label">適塾記念会会員割引額</label><label>　※申込が発生すると変更が出来なくなります。</label>
										<input name="tekijuku_discount" class=" form-control" min="0" type="number"
                                            value="<?= htmlspecialchars(isSetValue($eventData['tekijuku_discount'] ?? '', $old_input['tekijuku_discount'] ?? '')) ?>"
											<?php if(!empty($ticket_count) && $ticket_count > 0): ?>style="background-color: #e6e6e6;" readonly<?php endif ?>
											 />
										<?php if (!empty($errors['tekijuku_discount'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['tekijuku_discount']); ?></div>
										<?php endif; ?>
									</div>
									<?php if(!isset($eventData['event_kbn']) || (isset($eventData['event_kbn']) && $eventData['event_kbn'] != EVERY_DAY_EVENT)): ?>
										<div id="deadline_area" class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label id="deadline_label" class="me-2">申し込み締切日>　※未入力の場合、申し込み締切はイベント開催日の終了時間までになります。</label>
											</div>
											<input name="deadline" class=" form-control" type="date"
												value="<?= explode (' ', htmlspecialchars(isSetValue($eventData['deadline'] ?? '', $old_input['deadline'] ?? '')))[0] ?>" />
											<?php if (!empty($errors['deadline'])): ?>
												<div class="text-danger mt-2"><?= htmlspecialchars($errors['deadline']); ?></div>
											<?php endif; ?>
										</div>
									<?php endif; ?>
									<div class="mb-3 all_deadline_area">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">各回申し込み締切日</label>
										</div>
										<input name="all_deadline" class="form-control" type="number"
                                            value="<?= explode (' ', htmlspecialchars(isSetValue($eventData['all_deadline'] ?? '', $old_input['all_deadline'] ?? '')) ?? '')[0] ?>" />
										<?php if (!empty($errors['all_deadline'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['all_deadline']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">リアルタイム配信URL</label>
										<input name="real_time_distribution_url" class="form-control" type="text"
                                            value="<?= htmlspecialchars(isSetValue($eventData['real_time_distribution_url'] ?? '', $old_input['real_time_distribution_url'] ?? '')) ?>" />
										<?php if (!empty($errors['real_time_distribution_url'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['real_time_distribution_url']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">アーカイブ配信期間</label>
										<input name="archive_streaming_period" class=" form-control" min="0" type="number"
                                            value="<?= htmlspecialchars(isSetValue($eventData['archive_streaming_period'] ?? '', $old_input['archive_streaming_period'] ?? '')) ?>" />
										<?php if (!empty($errors['archive_streaming_period'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['archive_streaming_period']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">講義資料公開期間</label>
										<input name="material_release_period" class=" form-control" min="0" type="number"
                                            value="<?= htmlspecialchars(isSetValue($eventData['material_release_period'] ?? '', $old_input['material_release_period'] ?? '')) ?>" />
										<?php if (!empty($errors['material_release_period'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['material_release_period']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">
											<input type="checkbox" name="is_double_speed" class="form-check-input" value="1" <?= isSelected(1, $eventData['is_double_speed'] ?? null, $old_input['is_double_speed'] ?? null) ? 'checked' : '' ?>>
											<span name="is_double_speed" class=" form-check-label">動画倍速機能</span>
										</label>
									</div>
									<div class="mb-3">
										<label class="form-label">
											<input type="checkbox" name="is_apply_btn" class="form-check-input" value="1" <?= isSelected(1, $eventData['is_apply_btn'] ?? null, $old_input['is_apply_btn'] ?? null) ? 'checked' : '' ?>>
											<span name="is_apply_btn" class=" form-check-label">申込みボタンを表示する</span>
										</label>
									</div>
									<div class="mb-3">
										<label class="form-label">イベントカスタム区分</label>
										<select id="event_customfield_category_id" class=" form-control mb-3" name="event_customfield_category_id" <?php if (count($tickets) > 0) { ?>style="pointer-events: none; background-color: #e6e6e6;"<?php } ?>>
											<option value="">未選択</option>
											<?php foreach ($event_category_list as $key => $event_category): ?>
												<option value="<?= htmlspecialchars($event_category['id']) ?>" <?php if(isset($eventData['event_customfield_category_id']) && $event_category['id'] == $eventData['event_customfield_category_id']): ?> selected <?php endif; ?>><?= htmlspecialchars($event_category['name']) ?></option>
											<?php endforeach ?>
										</select>
										<?php if (!empty($errors['event_customfield_category_id'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['note']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">アンケートカスタム区分</label>
										<select id="event_survey_customfield_category_id" class=" form-control  mb-3" name="event_survey_customfield_category_id" <?php if(isset($eventData['survey_answer']) && $eventData['survey_answer']) { ?>style="pointer-events: none; background-color: #e6e6e6;"<?php } ?>>
											<option value="">未選択</option>
											<?php foreach ($curvey_custom_list as $key => $curvey_custom): ?>
												<option value="<?= htmlspecialchars($curvey_custom['id']) ?>"  <?php if(isset($eventData['event_survey_customfield_category_id']) && $curvey_custom['id'] == $eventData['event_survey_customfield_category_id']): ?> selected <?php endif; ?>><?= htmlspecialchars($curvey_custom['name']) ?></option>
											<?php endforeach ?>
										</select>
										<?php if (!empty($errors['event_survey_customfield_category_id'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['note']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">その他</label>
										<textarea name="note" class="form-control" rows="5"><?= htmlspecialchars(isSetValue($eventData['note'] ?? '', $old_input['note'] ?? '')) ?></textarea>
										<?php if (!empty($errors['note'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['note']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<?php if (!$start_event_flg): ?>
											<input type="submit" id="submit" class="btn btn-primary" value="登録">
										<?php endif ?>
									</div>
								</form>
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
							<form method="POST" action="/custom/admin/app/Controllers/event/thumbnail_delete_controller.php">
								<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
								<input type="hidden" name="id" value="<?= htmlspecialchars($id ?? '', ENT_QUOTES, 'UTF-8') ?>">
								<input type="hidden" name="thumbnail_img" value="<?= htmlspecialchars($eventData['thumbnail_img'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
								<div class="modal-body">
									本当にこのサムネール画像を削除しますか？
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

	<script src="/custom/admin/public/js/app.js"></script>
</body>

</html>
<script>
	let tutor_options = '<?php echo $tutor_options ?>';

	document.addEventListener('DOMContentLoaded', () => {
		const eventKbnElement = document.querySelector('select[name="event_kbn"]');
		const onetimeArea = $('.onetime_area'); // 1：単発イベントに必要な項目を表示している箇所
		const repeatedlyArea = $('.repeatedly_area'); // 2：複数回イベントに必要な項目を表示している箇所
		const termArea = $('.term_area'); // 3：期間内毎日イベントに必要な項目を表示している箇所
		const event_id = $('#event_id').val();
		const capacityReq = $('#capacity_req');
		const participationFeeLabel = $('#participation_fee_label'); // 参加費のlabelタグ
		const participationFeeReq = $('#participation_fee_req'); // 参加費の必須表示
		const deadlineLabel = $('#deadline_label'); // 各回申し込み締切日のlabelタグ
		const deadlineReq = $('#deadline_req'); // 申し込み締切日の必須表示
		const allDeadlineArea = $('.all_deadline_area'); // 各回申し込み締切日を表示している箇所
		const allDeadlineReq = $('#all_deadline_req'); // 各回申し込み締切日の必須表示
		const oneArea = $('.one_area');
		const deadlineArea = $('#deadline_area');
		const is_best = $('#is_best').prop('checked'); // 推しイベント設定
		const best_event_img_tag = $('#best_event_img_tag'); // 推しイベント画像

		if(is_best) {
			best_event_img_tag.css('display', 'block');
		} else {
			best_event_img_tag.css('display', 'none');
		}

		// 初期表示で value="2" の場合は表示
		if (eventKbnElement.value == '2') {
			onetimeArea.css('display', 'none');
			oneArea.css('display', 'none');
			repeatedlyArea.css('display', 'block');
			allDeadlineArea.css('display', 'block');
			termArea.css('display', 'none');

			participationFeeLabel.text("参加費( 全て受講 )");
			deadlineLabel.text("申し込み締切日( 全て受講 )　※未入力の場合、申し込み締切はイベント開催日の前日なります。");
			capacityReq.css('display', 'inline-block');
			participationFeeReq.css('display', 'inline-block');
			deadlineReq.css('display', 'block');
			allDeadlineReq.css('display', 'block');
			deadlineArea.css('display', 'block');
		} else if (eventKbnElement.value == '3') { // 3：期間内に毎日開催のイベント
			onetimeArea.css('display', 'none');
			oneArea.css('display', 'block');
			repeatedlyArea.css('display', 'none');
			allDeadlineArea.css('display', 'block');
			termArea.css('display', 'block');

			participationFeeLabel.text("参加費");
			deadlineLabel.text("申し込み締切日　※未入力の場合、申し込み締切はイベント開催日の終了時間までになります。");
			capacityReq.css('display', 'none');
			participationFeeReq.css('display', 'none');
			deadlineReq.css('display', 'none');
			allDeadlineReq.css('display', 'none');
			deadlineArea.css('display', 'none');
		} else { // 1：単発のイベント
			onetimeArea.css('display', 'block');
			oneArea.css('display', 'block');
			repeatedlyArea.css('display', 'none');
			allDeadlineArea.css('display', 'none');
			termArea.css('display', 'none');

			participationFeeLabel.text("参加費");
			deadlineLabel.text("申し込み締切日　※未入力の場合、申し込み締切はイベント開催日の前日なります。");
			capacityReq.css('display', 'inline-block');
			participationFeeReq.css('display', 'inline-block');
			deadlineReq.css('display', 'block');
			deadlineArea.css('display', 'block');
		}

		const ids = ['lecture_format_id', 'category_id'];
		ids.forEach((id) => {
			const element = document.getElementById(id);
			if (element) {
				new Choices(element, {
					shouldSort: false,
					shouldSortItems: false,
					removeItemButton: true,
				});
			}
		});
		// select要素が変更された時にアラートを表示
		$('select[name="event_kbn"]').on('change', function() {
			if ($(this).val() == 3) {
				onetimeArea.css('display', 'none');
				oneArea.css('display', 'block');
				repeatedlyArea.css('display', 'none');
				allDeadlineArea.css('display', 'block');
				termArea.css('display', 'block');
				capacityReq.css('display', 'none');
				participationFeeReq.css('display', 'none');
				participationFeeLabel.text("参加費");
				deadlineLabel.text("申し込み締切日");
				deadlineReq.css('display', 'none');
				allDeadlineReq.css('display', 'none');
				deadlineArea.css('display', 'none');
			} else if ($(this).val() == 2) {
				onetimeArea.css('display', 'none');
				oneArea.css('display', 'none');
				repeatedlyArea.css('display', 'block');
				allDeadlineArea.css('display', 'block');
				termArea.css('display', 'none');
				capacityReq.css('display', 'inline-block');
				participationFeeReq.css('display', 'inline-block');
				participationFeeLabel.text("参加費( 全て受講 )");
				deadlineLabel.text("申し込み締切日( 全て受講 )");
				deadlineReq.css('display', 'block');
				allDeadlineReq.css('display', 'block');
				deadlineArea.css('display', 'block');
			} else {
				onetimeArea.css('display', 'block');
				oneArea.css('display', 'block');
				repeatedlyArea.css('display', 'none');
				allDeadlineArea.css('display', 'none');
				termArea.css('display', 'none');
				capacityReq.css('display', 'inline-block');
				participationFeeReq.css('display', 'inline-block');
				participationFeeLabel.text("参加費");
				deadlineLabel.text("申し込み締切日");
				deadlineReq.css('display', 'block');
				deadlineArea.css('display', 'block');
			}
		});
		let itemCount = 1; // 初期値として1を設定
		// select要素が変更された時にアラートを表示
		$('.add_colum').on('click', function() {
			const targetId = $(this).data('target'); // 削除対象のIDを取得
			itemCount++; // 番号をインクリメント

			const element = `
			<div id="area_${itemCount}" class="add_area">
				<div class="mb-3 mt-4">
				<div class="form-label d-flex align-items-center">
					<label class="me-2">講師</label>
					<span class="badge bg-danger">必須</span>
				</div>
				<select id="tutor_id_${itemCount}" class="form-control mb-3" name="tutor_id_${itemCount}">
					<optgroup label="">
					<option value="">講師無し</option>
					${tutor_options}
					</optgroup>
				</select>
				</div>
				<div id="tutor_name_area_${itemCount}" class="mb-3">
					<div class="form-label d-flex align-items-center">
					<label class="me-2">講師名</label>
					<span class="badge bg-danger">必須</span>
					</div>
					<input type="text" name="tutor_name_${itemCount}" class="form-control" placeholder="">
				</div>
				<div class="mb-3">
					<div class="form-label align-items-center">
						<label class="me-2">講義名</label>
						<span class="badge bg-danger">必須</span>
					</div>
					<input type="text" name="lecture_name_${itemCount}" class="form-control" placeholder="">
				</div>
				<div class="mb-3">
				<div class="form-label d-flex align-items-center">
					<label class="me-2">講義概要</label>
					<span class="badge bg-danger">必須</span>
				</div>
				<textarea name="program_${itemCount}" class="form-control" rows="5"></textarea>
				</div>
				<div class="mb-3">
				<div class="form-label mt-3 d-flex align-items-center">
					<button type="button" class="delete_btn btn btn-danger ms-auto me-0" data-target="${itemCount}">削除</button>
				</div>
				</div>
				<hr>
			</div>
			`;

			$(this).parent().parent().before(element);
		});

		// 各講座における項目数を管理するオブジェクト
		const lectureCounts = {};

		// 項目追加ボタンのクリックイベント
		$('.add_colum_lecture').on('click', function() {
			const targetLecture = $(this).data('target'); // 対象の講座番号を取得
			if (!lectureCounts[targetLecture]) {
				lectureCounts[targetLecture] = 1; // 初期化
			}
			lectureCounts[targetLecture]++; // 番号をインクリメント

			const newItemCount = lectureCounts[targetLecture];

			// 新しい項目のHTMLを生成
			const element = `
			<div id="area_${targetLecture}_${newItemCount}">
				<div class="mb-3">
					<label class="form-label">講師</label>
					<select id="tutor_id_${targetLecture}_${newItemCount}" class="form-control mb-3" name="tutor_id_${targetLecture}_${newItemCount}">
					<optgroup label="">
						<option value="">講師無し</option>
						${tutor_options}
					</optgroup>
					</select>
				</div>
				<div id="tutor_name_area_${targetLecture}_${newItemCount}" class="mb-3">
					<div class="form-label d-flex align-items-center">
					<label class="me-2">講師名</label>
					<span class="badge bg-danger">必須</span>
					</div>
					<input type="text" name="tutor_name_${targetLecture}_${newItemCount}" class="form-control" placeholder="">
				</div>
				<div class="mb-3">
					<div class="form-label d-flex align-items-center">
					<label class="me-2">講義名</label>
					<span class="badge bg-danger">必須</span>
					</div>
					<input type="text" name="lecture_name_${targetLecture}_${newItemCount}" class="form-control" placeholder="">
				</div>
				<div class="mb-5">
					<div class="form-label d-flex align-items-center">
					<label class="me-2">講義概要</label>
					<span class="badge bg-danger">必須</span>
					</div>
					<textarea name="program_${targetLecture}_${newItemCount}" class="form-control" rows="5"></textarea>
				</div>
				<div class="mb-3">
					<div class="form-label mt-3 d-flex align-items-center">
						<button type="button" class="delete_btn btn btn-danger ms-auto me-0" data-target="${targetLecture}_${newItemCount}">削除</button>
					</div>
				</div>
				<hr>
			</div>`;
			// ボタンの直前に新しい項目を挿入
			$(this).closest('.mb-3').before(element);
		});

		$(document).on('click', '.delete_btn', function() {
			const targetId = "area_" + $(this).data('target'); // 削除対象のIDを取得
			$(`#${targetId}`).remove(); // 対象の要素を削除
		});
	});

	// 講師のセレクトボックスを変更した時
	// 講師名のテキストエリアを表示
	$(document).on('change', 'select[id^="tutor_id_"]', function() {
		let selectedValue = $(this).val();
		let selectId = $(this).attr('id'); // セレクトボックスのIDを取得

		// tutor_id_ を tutor_name_area_ に変換して、対応する div の ID を取得
		let divId = selectId.replace("tutor_id_", "tutor_name_area_");

		if (selectedValue !== "") {
			$('#' + divId).css('display', 'none'); // 非表示
		} else {
			$('#' + divId).css('display', 'block'); // 表示
		}
	});

	$(document).ready(function() {
		$("#del_submit").on("click", function(e) {
			e.preventDefault(); // フォームの送信を一旦キャンセル
			let confirmed = confirm("本当にこのイベントを削除しますか？");
			if (confirmed) {
				$(this).closest("form").submit(); // OKならフォームを送信
			}
		});

		$('#thumbnail_img').on('change', function(event) {
			const file = event.target.files[0]; // 選択されたファイルを取得

			// ファイルが画像であるか確認
			if (file && file.type.match('image.*')) {
				const reader = new FileReader(); // FileReader のインスタンスを作成

				// ファイルの読み込みが完了したらプレビューを表示
				reader.onload = function(e) {
					$('#image-preview').html(
						`<img src="${e.target.result}" alt="プレビュー" class="preview">`
					);
				};

				reader.readAsDataURL(file); // ファイルを読み込む
			} else {
				alert('画像ファイルを選択してください。');
				$('#image-preview').html(''); // プレビューをクリア
			}
		});
													
		$('#is_best').on('change', function () {
			if ($(this).prop('checked')) {
				$('#best_event_img_tag').css('display', 'block'); // 表示
			} else {
				$('#best_event_img_tag').css('display', 'none'); // 非表示
			}
		});

		$('#best_event_img').on('change', function(event) {
			const file = event.target.files[0]; // 選択されたファイルを取得

			// ファイルが画像であるか確認
			if (file && file.type.match('image.*')) {
				const reader = new FileReader(); // FileReader のインスタンスを作成

				// ファイルの読み込みが完了したらプレビューを表示
				reader.onload = function(e) {
					$('#best-image-preview').html(
						`<img src="${e.target.result}" alt="プレビュー" class="preview">`
					);
				};

				reader.readAsDataURL(file); // ファイルを読み込む
			} else {
				alert('画像ファイルを選択してください。');
				$('#best-image-preview').html(''); // プレビューをクリア
			}
		});

		$('#best_event_sp_img').on('change', function(event) {
			const file = event.target.files[0]; // 選択されたファイルを取得

			// ファイルが画像であるか確認
			if (file && file.type.match('image.*')) {
				const reader = new FileReader(); // FileReader のインスタンスを作成

				// ファイルの読み込みが完了したらプレビューを表示
				reader.onload = function(e) {
					$('#best-sp-image-preview').html(
						`<img src="${e.target.result}" alt="プレビュー" class="preview">`
					);
				};

				reader.readAsDataURL(file); // ファイルを読み込む
			} else {
				alert('画像ファイルを選択してください。');
				$('#best-sp-image-preview').html(''); // プレビューをクリア
			}
		});

		let selectedId;
		// 削除リンクがクリックされたとき
		$('.delete-link').on('click', function(event) {
			event.preventDefault();
			selectedId = $(this).data('id');
			$('#delete_confirm_modal').modal('show');
		});
		// モーダル内の削除ボタンがクリックされたとき
		$('#confirm_delete').on('click', function(e) {
			e.preventDefault();

			var form = $(this).closest('form');
			var formData = new FormData(form[0]);

			fetch(form.attr('action'), {
					method: 'POST',
					body: formData
				})
				.then(response => response.json()) // JSON を解析
				.then(data => {
					// 取得した結果を変数に格納
					var result = data;

					if (result.success) {
						alert(result.message);

						// サムネイル画像を完全に削除
						$('#thumbnail_img_tag').remove();
						$('.delete-link').remove();

						// モーダルを閉じる
						var modal = bootstrap.Modal.getInstance($('#delete_confirm_modal'));
						modal.hide();
					} else {
						alert(result.message);
					}
				})
				.catch(error => {
					console.error('Error:', error.message);

					// モーダルを閉じる
					var modal = bootstrap.Modal.getInstance($('#delete_confirm_modal'));
					modal.hide();
				});
		});
	});
</script>