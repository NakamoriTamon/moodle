<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/event/event_edit_controller.php');
require_once('/var/www/html/moodle/custom/helpers/form_helpers.php');

// id を取得
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// コントローラに id を渡す
$controller = new EventEditController();
$eventData = $controller->getEventData($id);

// セッションからエラーメッセージを取得
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];

$details = array();
for($i = 1; $i < 10; $i++){
    if (!empty($old_input)) {
		$j = 0;
		$n = 1;
		while (isset($old_input["tutor_id_{$i}_{$n}"])) {
			$details[$i][$j] = [
				'tutor_id' => $old_input["tutor_id_{$i}_{$n}"] ?? null,
				'name' =>  $old_input["lecture_name_{$i}_{$n}"] ?? null,
				'program' => $old_input["program_{$i}_{$n}"] ?? null,
			];
			$j++;
			$n++;
		}
    } else {
		$details[$i] = $eventData['select_course'][$i]['details'] ?? [[]];
	}
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
							<p class="content_title p-3">イベント登録</p>
							<div class="form-wrapper">
								<form method="POST" action="/custom/admin/app/Controllers/event/event_upsert_controller.php" enctype="multipart/form-data">
									<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
									<input type="hidden" name="action" value="createUpdate">
									<input type="hidden" id="event_id" name="id" value="<?= $id ?? '' ?>">
									<div class=" mb-3">
										<label class="form-label">イベント区分</label>
										<select name="event_kbn" class="form-control mb-3">
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
											<span class="badge bg-danger">必須</span>
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
        											<?= isChoicesSelected($category['id'], $eventData['select_categorys'] ?? null, $old_input['category_id'] ?? null) ? 'selected' : '' ?>>
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
											<span class="badge bg-danger">必須</span>
										</div>
										<div class="mb-3">
											<input type="file" name="thumbnail_img" id="thumbnail_img" class="form-control" accept=".png,.jpeg,.jpg">
										</div>
										<div id="image-preview" class="mb-3">
											<!-- プレビュー画像がここに表示されます -->
										</div>
										<?php if(isset($eventData['thumbnail_img'])): ?>
												<img class="fit-picture"
													src="<?= htmlspecialchars($eventData['thumbnail_img']) ?>"
													width="300" />
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
										<input name="target" class=" form-control" type="text"
                                            value="<?= htmlspecialchars(isSetValue($eventData['target'] ?? '', ($old_input['target'] ?? ''))) ?>" />
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
                                            value="<?= htmlspecialchars(isSetDate ($eventData['event_date'] ?? '', $old_input['event_date'] ?? '')) ?>" />
										<?php if (!empty($errors['event_date'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['event_date']); ?></div>
										<?php endif; ?>
									</div>
									<?php if (!is_mobile_device()): ?>
										<div class=" mb-3 sp-none">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">時間</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="start_hour" class="timepicker" type="text" placeholder="12:00"
												value="<?= htmlspecialchars(isSetValue($eventData['start_hour'] ?? '', ($old_input['start_hour'] ?? ''))) ?>" /> <span class="ps-2 pe-2">～</span>
											<input name="end_hour" class="timepicker" type="text" placeholder="12:00"
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
											<input name="start_hour" class="timepicker w-100" type="text" placeholder="12:00" value="<?= htmlspecialchars(isSetValue($eventData['start_hour'] ?? '', ($old_input['start_hour'] ?? ''))) ?>">
										</div>
										<div class="mb-3 pc-none">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">時間( 終了時間 )</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="end_hour" class="timepicker w-100" type="text" placeholder="12:00" value="<?= htmlspecialchars(isSetValue($eventData['end_hour'] ?? '', ($old_input['end_hour'] ?? ''))) ?>"">
										</div>
									<?php endif; ?>
									<div class="mb-3">
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
										</div>
										<div class="mb-3">
											<?php if (!is_null($eventData)): ?><?= $eventData['google_map'] ?? '' ?><?php endif; ?>
										</div>
									</div>
									<div class="mb-3">
										<label class="form-label">
											<input name="is_top" type="checkbox" value="1" checked class="form-check-input">
											<span class="form-check-label">トップに固定する</span>
										</label>
									</div>
									<div class="mb-3 onetime_area">
									<?php foreach ($details[1] as $key => $detail): ?>
										<div class="form-label d-flex align-items-center">
											<label class="me-2">講師</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<select id="tutor_id_<?= $key+1 ?>" class=" form-control mb-3" name="tutor_id_<?= $key+1 ?>">
											<optgroup label="">
												<option value="">選択してください</option>
												<?php foreach ($tutors as $tutor): ?>
													<option value="<?= htmlspecialchars($tutor['id']) ?>"
												<?= isSelected($tutor['id'], $detail['tutor_id'] ?? null, $old_input['tutor_id_' . $key+1] ?? null) ? 'selected' : '' ?>>
														<?= htmlspecialchars($tutor['name']) ?>
													</option>
												<?php endforeach; ?>
											</optgroup>
										</select>
										<?php if (!empty($errors['tutor_id_' . $key+1])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['tutor_id_' . $key+1]); ?></div>
										<?php endif; ?>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義名</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input type="text" name="lecture_name_<?= $key+1 ?>" class="form-control" placeholder=""
												value="<?= htmlspecialchars(isSetValue($detail['name'] ?? '', $old_input['lecture_name_' . $key+1] ?? '')) ?>" />
											<?php if (!empty($errors['lecture_name_' . $key+1])): ?>
												<div class="text-danger mt-2"><?= htmlspecialchars($errors['lecture_name_' . $key+1]); ?></div>
											<?php endif; ?>
										</div>
										<div class="mb-5">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義概要</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<textarea name="program_<?= $key+1 ?>" class=" form-control" rows="5"><?= htmlspecialchars(isSetValue($detail['program'] ?? '', $old_input['program_' . $key+1] ?? '')) ?></textarea>
											<?php if (!empty($errors['program_' . $key+1])): ?>
												<div class="text-danger mt-2"><?= htmlspecialchars($errors['program_' . $key+1]); ?></div>
											<?php endif; ?>
										</div>
										<hr>
									<?php endforeach; ?>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<button type="button" class="add_colum btn btn-primary ms-auto me-0" data-target="">項目追加</button>
											</div>
										</div>
									</div>

									<div class="repeatedly_area">
										<?php for($i = 1; $i < 10; $i++): ?>
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
											<?php foreach ($details[$i] as $key => $detail): ?>
												<div id="area_<?= $i ?>_<?= $key+1 ?>">
													<div class="mb-3">
														<div class="form-label d-flex align-items-center">
															<label class="me-2">講師</label>
															<?php if($i < 3): ?><span class="badge bg-danger">必須</span><?php endif; ?>
														</div>
														<select id="tutor_id_<?= $i ?>_<?= $key+1 ?>" class="form-control mb-3" name="tutor_id_<?= $i ?>_<?= $key+1 ?>">
															<option value="">選択してください</option>
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
											<div class="mb-3">
												<div class="form-label d-flex align-items-center">
													<button type="button" class="add_colum_lecture btn btn-primary ms-auto me-0" data-target="<?= $i ?>">項目追加</button>
												</div>
											</div>
										<?php endfor; ?>
									</div>
									<!-- <div class="mb-3">
										<label class="form-label">講義名</label>
										<input name="lecture_name" class=" form-control" type="text">
									</div>
									<div class="mb-3">
										<label class="form-label">講義概要</label>
										<textarea name="lecture_outline" class="form-control" rows="5"></textarea>
									</div> -->
									<div class="mb-3">
										<label class="form-label">プログラム</label>
										<textarea name="program" class=" form-control" rows="5"><?= htmlspecialchars($eventData['program'] ?? ($old_input['program'] ?? '')) ?></textarea>
										<?php if (!empty($errors['program'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['program']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">主催</label>
										<input name="sponsor" class=" form-control" type="text"
                                            value="<?= htmlspecialchars($eventData['sponsor'] ?? ($old_input['sponsor'] ?? '')) ?>" />
										<?php if (!empty($errors['sponsor'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['sponsor']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">共催</label>
										<input name="co_host" class="form-control" type="text"
                                            value="<?= htmlspecialchars($eventData['co_host'] ?? ($old_input['co_host'] ?? '')) ?>" />
										<?php if (!empty($errors['co_host'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['co_host']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">後援</label>
										<input name="sponsorship" class="form-control" type="text"
                                            value="<?= htmlspecialchars($eventData['sponsorship'] ?? ($old_input['sponsorship'] ?? '')) ?>" />
										<?php if (!empty($errors['sponsorship'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['sponsorship']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">協力</label>
										<input name="cooperation" class=" form-control" type="text"
                                            value="<?= htmlspecialchars($eventData['cooperation'] ?? ($old_input['cooperation'] ?? '')) ?>" />
										<?php if (!empty($errors['cooperation'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['cooperation']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">企画</label>
										<input name="plan" class="form-control" type="text"
                                            value="<?= htmlspecialchars($eventData['plan'] ?? ($old_input['plan'] ?? '')) ?>" />
										<?php if (!empty($errors['plan'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['plan']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">定員</label>
										<span class="badge bg-danger">必須</span>
										<input name="capacity" class=" form-control" min="0" type="number"
                                            value="<?= htmlspecialchars($eventData['capacity'] ?? ($old_input['capacity'] ?? '')) ?>" />
										<?php if (!empty($errors['capacity'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['capacity']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label" id="participation_fee_label">参加費<?php if(!empty($eventData) && $eventData['event_kbn'] == 2): ?>( 全て受講 )<?php endif; ?></label>
										<span class="badge bg-danger">必須</span>
										<input name="participation_fee" class=" form-control" min="0" type="number"
                                            value="<?= htmlspecialchars($eventData['participation_fee'] ?? ($old_input['participation_fee'] ?? '')) ?>" />
										<?php if (!empty($errors['participation_fee'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['participation_fee']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">申し込み締切日</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input name="deadline" class=" form-control" type="date"
                                            value="<?= explode (' ', htmlspecialchars($eventData['deadline'] ?? ($old_input['deadline'] ?? '')))[0] ?>" />
										<?php if (!empty($errors['deadline'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['deadline']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3 repeatedly_area">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">各回申し込み締切日</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input name="all_deadline" class="form-control" type="number"
                                            value="<?= explode (' ', htmlspecialchars($eventData['all_deadline'] ?? ($old_input['all_deadline'] ?? '')))[0] ?>" />
										<?php if (!empty($errors['all_deadline'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['all_deadline']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">アーカイブ配信期間</label>
										<span class="badge bg-danger">必須</span>
										<input name="archive_streaming_period" class=" form-control" min="0" type="number"
                                            value="<?= htmlspecialchars($eventData['archive_streaming_period'] ?? ($old_input['archive_streaming_period'] ?? '')) ?>" />
										<?php if (!empty($errors['archive_streaming_period'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['archive_streaming_period']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">
											<input type="checkbox" checked name="is_double_speed" class="form-check-input">
											<span name="is_double_speed" class=" form-check-label">動画倍速機能</span>
										</label>
									</div>
									<div class="mb-3">
										<label class="form-label">
											<input type="checkbox" checked name="is_apply_btn" class="form-check-input">
											<span name="is_apply_btn" class=" form-check-label">申込みボタンを表示する</span>
										</label>
									</div>
									<div class="mb-3">
										<label class="form-label">イベントカスタム区分</label>
										<select id="event_customfield_category_id" class=" form-control mb-3" name="event_customfield_category_id">
											<option value="">未選択</option>
											<?php foreach ($event_category_list as $key => $event_category): ?>
												<option value="<?= htmlspecialchars($event_category['id']) ?>"  <?php if(isset($eventData['event_customfield_category_id']) && $event_category['id'] == $eventData['event_customfield_category_id']): ?> selected <?php endif; ?>><?= htmlspecialchars($event_category['name']) ?></option>
											<?php endforeach ?>
										</select>
									</div>
									<div class="mb-3">
										<label class="form-label">アンケートカスタム区分</label>
										<select id="survey_custom_id" class=" form-control  mb-3" name="survey_custom_id">
											<option value="">未選択</option>
											<option value=1>イベント一般</option>
											<option value=2>適塾記念会イベント</option>
											<option value=3>生命科学分野イベント</option>
										</select>
									</div>
									<div class="mb-3">
										<label class="form-label">その他</label>
										<textarea name="note" class="form-control" rows="5"><?= htmlspecialchars($eventData['note'] ?? ($old_input['note'] ?? '')) ?></textarea>
										<?php if (!empty($errors['note'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['note']); ?></div>
										<?php endif; ?>
									</div>
									<input type="submit" id="submit" class="btn btn-primary" value="登録">
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
<script>
	document.addEventListener('DOMContentLoaded', () => {
		const eventKbnElement = document.querySelector('select[name="event_kbn"]');
		const repeatedlyArea =$('.repeatedly_area');
		const onetimeArea = $('.onetime_area');
		const event_id = $('#event_id').val();
		const participationFeeLabel = $('#participation_fee_label');

		// 初期表示で value="2" の場合は表示
		if (eventKbnElement.value == '2') {
			onetimeArea.css('display', 'none');
			repeatedlyArea.css('display', 'block');
			participationFeeLabel.text("参加費( 全て受講 )");
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
			if ($(this).val() == 2) {
				onetimeArea.css('display', 'none');
				repeatedlyArea.css('display', 'block');
				participationFeeLabel.text("参加費( 全て受講 )");
			} else {
				onetimeArea.css('display', 'block');
				repeatedlyArea.css('display', 'none');
				participationFeeLabel.text("参加費");
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
					<option value="1">海道 尊</option>
					<option value="2">川上 潤</option>
					</optgroup>
				</select>
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
		$('.add_colum_lecture').on('click', function () {
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
						<option value="1">海道 尊</option>
						<option value="2">川上 潤</option>
					</optgroup>
					</select>
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

	$(document).ready(function () {
            $('#thumbnail_img').on('change', function (event) {
                const file = event.target.files[0]; // 選択されたファイルを取得

                // ファイルが画像であるか確認
                if (file && file.type.match('image.*')) {
                    const reader = new FileReader(); // FileReader のインスタンスを作成

                    // ファイルの読み込みが完了したらプレビューを表示
                    reader.onload = function (e) {
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
        });
</script>