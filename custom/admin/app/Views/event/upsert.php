<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/event/event_edit_controller.php');
// id を取得
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// コントローラに id を渡す
$controller = new EventEditController();
$eventData = $controller->getEventData($id);

$event_kbns = require '/var/www/html/moodle/custom/path/to/event_kbn.php';
// セッションからエラーメッセージを取得
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']); // 一度表示したら削除
 ?>

<body id="event" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<div class="navbar-collapse collapse">
					<p class="title ms-4 fs-4 fw-bold mb-0">イベント登録</p>
					<ul class="navbar-nav navbar-align">
						<li class="nav-item dropdown">
							<a class="nav-icon pe-md-0 dropdown-toggle" href="#" data-bs-toggle="dropdown">
								<div class="fs-5 me-4">システム管理者</div>
							</a>
							<div class="dropdown-menu dropdown-menu-end">
								<a class="dropdown-item" href="login.php">Log out</a>
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
									<input type="hidden" name="id" value="<?php $id ?? '' ?>">
									<div class=" mb-3">
										<label class="form-label">イベント区分</label>
										<select name="event_kbn" class="form-control mb-3">
											<?php foreach ($event_kbns as $id => $name): ?>
												<option value="<?= htmlspecialchars($id) ?>"
													<?= isset($old_input['event_kbn']) && $id == $old_input['event_kbn'] ? 'selected' : '' ?>>
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
                                            value="<?= htmlspecialchars($eventData['name'] ?? ($old_input['name'] ?? '')) ?>" />
										<?php if (!empty($errors['name'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['name']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">説明文</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<textarea name="description" class=" form-control" rows="5"><?= htmlspecialchars($eventData['description'] ?? ($old_input['description'] ?? '')) ?></textarea>
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
											<optgroup label="">
												<?php foreach ($categorys as $category): ?>
													<option value="<?= htmlspecialchars($category['id']) ?>"
													<?= in_array($category['id'], $old_input['category_id'] ?? []) ? 'selected' : '' ?>>
														<?= htmlspecialchars($category['name']) ?>
													</option>
												<?php endforeach; ?>
											</optgroup>
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
										<input type="file" name="thumbnail_img" class="form-control" accept=".png,.jpeg,.jpg">
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
													<?= in_array($lectureFormat['id'], $old_input['lecture_format_id'] ?? []) ? 'selected' : '' ?>>
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
                                            value="<?= htmlspecialchars($eventData['venue_name'] ?? ($old_input['venue_name'] ?? '')) ?>" />
										<?php if (!empty($errors['venue_name'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['venue_name']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">対象</label>
										<input name="target" class=" form-control" type="text"
                                            value="<?= htmlspecialchars($eventData['target'] ?? ($old_input['target'] ?? '')) ?>" />
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
                                            value="<?= htmlspecialchars($eventData['event_date'] ?? ($old_input['event_date'] ?? '')) ?>" />
										<?php if (!empty($errors['event_date'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['event_date']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">時間</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input name="start_hour" class="timepicker" type="text" placeholder="12:00"
											value="<?= htmlspecialchars($eventData['start_hour'] ?? ($old_input['start_hour'] ?? '')) ?>" /> <span class="ps-2 pe-2">～</span>
										<input name="end_hour" class="timepicker" type="text" placeholder="12:00"
											value="<?= htmlspecialchars($eventData['end_hour'] ?? ($old_input['end_hour'] ?? '')) ?>" />
										<?php if (!empty($errors['start_hour'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['start_hour']); ?></div>
										<?php endif; ?>
										<?php if (!empty($errors['end_hour'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['end_hour']); ?></div>
										<?php endif; ?>
									</div>
									<!-- <div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">コンテンツ閲覧期間</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input name="end_hour" class="timepicker" type="number"><span class="ps-2 pe-2">日間</span>
									</div> -->
									<div class="mb-3">
										<label class="form-label">交通アクセス</label>
										<textarea name="access" class=" form-control" rows="5"><?= htmlspecialchars($eventData['access'] ?? ($old_input['access'] ?? '')) ?></textarea>
										<?php if (!empty($errors['access'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['access']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">Google Map</label>
										<textarea name="google_map" class="form-control" rows="5"></textarea>
									</div>
									<div class="mb-3">
										<label class="form-label">
											<input name="is_top" type="checkbox" value="1" checked class="form-check-input">
											<span class="form-check-label">トップに固定する</span>
										</label>
									</div>
									<div class="mb-3 onetime_area">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">講師</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<select id="tutor_id_1" class=" form-control mb-3" name="tutor_id_1">
											<optgroup label="">
												<option value="">選択してください</option>
												<option value=1>海道 尊</option>
												<option value=2>川上 潤</option>
											</optgroup>
										</select>
										<?php if (!empty($errors['tutor_id_1'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['tutor_id_1']); ?></div>
										<?php endif; ?>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義名</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input type="text" name="lecture_name_1" class="form-control" placeholder="">
											<?php if (!empty($errors['lecture_name_1'])): ?>
												<div class="text-danger mt-2"><?= htmlspecialchars($errors['lecture_name_1']); ?></div>
											<?php endif; ?>
										</div>
										<div class="mb-5">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義概要</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<textarea name="program_1" class=" form-control" rows="5"><?= htmlspecialchars($old_input['program_1'] ?? '') ?></textarea>
											<?php if (!empty($errors['program_1'])): ?>
												<div class="text-danger mt-2"><?= htmlspecialchars($errors['program_1']); ?></div>
											<?php endif; ?>
										</div>
										<hr>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<button type="button" class="add_colum btn btn-primary ms-auto me-0" data-target="">項目追加</button>
											</div>
										</div>
									</div>

									<div class="repeatedly_area">
										<div class="mb-3">
											<P class="fs-5 fw-bold">第1講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="course_date_1" class="form-control" type="date">
										</div>
										<div class="mb-3">
											<label class="form-label">講師</label>
											<span class="badge bg-danger">必須</span>
											<select id="tutor_id_1_1" class=" form-control mb-3" name="tutor_id_1_1">
												<optgroup label="">
													<option value="">選択してください</option>
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義名</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input type="text" name="lecture_name_1_1" class="form-control" placeholder="">
										</div>
										<div class="mb-5">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義概要</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<textarea name="program_1_1" class=" form-control" rows="5"></textarea>
										</div>
										<hr>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<button type="button" class="add_colum_lecture btn btn-primary ms-auto me-0" data-target="1">項目追加</button>
											</div>
										</div>

										<div class="mb-3">
											<P class="fs-5 fw-bold">第2講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="course_date_2" class="form-control" type="date">
										</div>
										<div class="mb-3">
											<label class="form-label">講師</label>
											<span class="badge bg-danger">必須</span>
											<select id="tutor_id_2_1" class=" form-control mb-3" name="tutor_id_2_1">
												<optgroup label="">
													<option value="">選択してください</option>
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義名</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input type="text" name="lecture_name_2_1" class="form-control" placeholder="">
										</div>
										<div class="mb-5">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義概要</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<textarea name="program_2_1" class=" form-control" rows="5"></textarea>
										</div>
										<hr>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<button type="button" class="add_colum_lecture btn btn-primary ms-auto me-0" data-target="2">項目追加</button>
											</div>
										</div>

										<div class="mb-3">
											<P class="fs-5 fw-bold">第3講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
											</div>
											<input name="course_date_3" class="form-control" type="date">
										</div>
										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_3_1" class=" form-control mb-3" name="tutor_id_3_1">
												<optgroup label="">
													<option value="">選択してください</option>
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義名</label>
											</div>
											<input type="text" name="lecture_name_3_1" class="form-control" placeholder="">
										</div>
										<div class="mb-5">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義概要</label>
											</div>
											<textarea name="program_3_1" class=" form-control" rows="5"></textarea>
										</div>
										<hr>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<button type="button" class="add_colum_lecture btn btn-primary ms-auto me-0" data-target="3">項目追加</button>
											</div>
										</div>

										<div class="mb-3">
											<P class="fs-5 fw-bold">第4講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
											</div>
											<input name="course_date_4" class="form-control" type="date">
										</div>
										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_4_1" class=" form-control mb-3" name="tutor_id_4_1">
												<optgroup label="">
													<option value="">選択してください</option>
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義名</label>
											</div>
											<input type="text" name="lecture_name_4_1" class="form-control" placeholder="">
										</div>
										<div class="mb-5">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義概要</label>
											</div>
											<textarea name="program_4_1" class=" form-control" rows="5"></textarea>
										</div>
										<hr>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<button type="button" class="add_colum_lecture btn btn-primary ms-auto me-0" data-target="4">項目追加</button>
											</div>
										</div>

										<div class="mb-3">
											<P class="fs-5 fw-bold">第5講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
											</div>
											<input name="course_date_5" class="form-control" type="date">
										</div>
										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_5_1" class=" form-control mb-3" name="tutor_id_5_1">
												<optgroup label="">
													<option value="">選択してください</option>
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義名</label>
											</div>
											<input type="text" name="lecture_name_5_1" class="form-control" placeholder="">
										</div>
										<div class="mb-5">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義概要</label>
											</div>
											<textarea name="program_5_1" class=" form-control" rows="5"></textarea>
										</div>
										<hr>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<button type="button" class="add_colum_lecture btn btn-primary ms-auto me-0" data-target="5">項目追加</button>
											</div>
										</div>

										<div class="mb-3">
											<P class="fs-5 fw-bold">第6講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
											</div>
											<input name="course_date_6" class="form-control" type="date">
										</div>
										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_6_1" class=" form-control mb-3" name="tutor_id_6_1">
												<optgroup label="">
													<option value="">選択してください</option>
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義名</label>
											</div>
											<input type="text" name="lecture_name_6_1" class="form-control" placeholder="">
										</div>
										<div class="mb-5">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義概要</label>
											</div>
											<textarea name="program_6_1" class=" form-control" rows="5"></textarea>
										</div>
										<hr>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<button type="button" class="add_colum_lecture btn btn-primary ms-auto me-0" data-target="6">項目追加</button>
											</div>
										</div>

										<div class="mb-3">
											<P class="fs-5 fw-bold">第7講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
											</div>
											<input name="course_date_7" class="form-control" type="date">
										</div>
										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_7_1" class=" form-control mb-3" name="tutor_id_7_1">
												<optgroup label="">
													<option value="">選択してください</option>
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義名</label>
											</div>
											<input type="text" name="lecture_name_7_1" class="form-control" placeholder="">
										</div>
										<div class="mb-5">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義概要</label>
											</div>
											<textarea name="program_7_1" class=" form-control" rows="5"></textarea>
										</div>
										<hr>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<button type="button" class="add_colum_lecture btn btn-primary ms-auto me-0" data-target="7">項目追加</button>
											</div>
										</div>

										<div class="mb-3">
											<P class="fs-5 fw-bold">第8講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
											</div>
											<input name="course_date_8" class="form-control" type="date">
										</div>
										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_8_1" class=" form-control mb-3" name="tutor_id_8_1">
												<optgroup label="">
													<option value="">選択してください</option>
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義名</label>
											</div>
											<input type="text" name="lecture_name_8_1" class="form-control" placeholder="">
										</div>
										<div class="mb-5">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義概要</label>
											</div>
											<textarea name="program_8_1" class=" form-control" rows="5"></textarea>
										</div>
										<hr>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<button type="button" class="add_colum_lecture btn btn-primary ms-auto me-0" data-target="8">項目追加</button>
											</div>
										</div>

										<div class="mb-3">
											<P class="fs-5 fw-bold">第9講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
											</div>
											<input name="course_date_9" class="form-control" type="date">
										</div>
										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_9_1" class=" form-control mb-3" name="tutor_id_9_1">
												<optgroup label="">
													<option value="">選択してください</option>
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義名</label>
											</div>
											<input type="text" name="lecture_name_9_1" class="form-control" placeholder="">
										</div>
										<div class="mb-5">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">講義概要</label>
											</div>
											<textarea name="program_9_1" class=" form-control" rows="5"></textarea>
										</div>
										<hr>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<button type="button" class="add_colum_lecture btn btn-primary ms-auto me-0" data-target="9">項目追加</button>
											</div>
										</div>
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
										<textarea name="program" class=" form-control" rows="5"><?= htmlspecialchars($old_input['program'] ?? ''); ?></textarea>
										<?php if (!empty($errors['program'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['program']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">主催</label>
										<input name="sponsor" class=" form-control" type="text"
											value="<?= htmlspecialchars($old_input['sponsor'] ?? ''); ?>" />
										<?php if (!empty($errors['sponsor'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['sponsor']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">共催</label>
										<input name="co_host" class="form-control" type="text"
											value="<?= htmlspecialchars($old_input['co_host'] ?? ''); ?>" />
										<?php if (!empty($errors['co_host'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['co_host']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">後援</label>
										<input name="sponsorship" class="form-control" type="text"
											value="<?= htmlspecialchars($old_input['sponsorship'] ?? ''); ?>" />
										<?php if (!empty($errors['sponsorship'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['sponsorship']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">協力</label>
										<input name="cooperation" class=" form-control" type="text"
											value="<?= htmlspecialchars($old_input['cooperation'] ?? ''); ?>" />
										<?php if (!empty($errors['cooperation'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['cooperation']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">企画</label>
										<input name="plan" class="form-control" type="text"
											value="<?= htmlspecialchars($old_input['plan'] ?? ''); ?>" />
										<?php if (!empty($errors['plan'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['plan']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">定員</label>
										<span class="badge bg-danger">必須</span>
										<input name="capacity" class=" form-control" min="0" type="number"
											value="<?= htmlspecialchars($old_input['capacity'] ?? ''); ?>" />
										<?php if (!empty($errors['capacity'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['capacity']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3 onetime_area">
										<label class="form-label">参加費</label>
										<span class="badge bg-danger">必須</span>
										<input name="participation_fee" class=" form-control" min="0" type="number"
											value="<?= htmlspecialchars($old_input['participation_fee'] ?? ''); ?>" />
										<?php if (!empty($errors['participation_fee'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['participation_fee']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3 repeatedly_area">
										<label class="form-label">参加費( 全て受講 )</label>
										<span class="badge bg-danger">必須</span>
										<input id="all_participation_fee" name="all_participation_fee" class="form-control" min="0" type="number"
											value="<?= htmlspecialchars($old_input['all_participation_fee'] ?? ''); ?>" />
										<?php if (!empty($errors['participation_fee'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['participation_fee']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3 onetime_area">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">申し込み締切日</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input name="deadline" class=" form-control" type="date"
											value="<?= htmlspecialchars($old_input['deadline'] ?? ''); ?>" />
										<?php if (!empty($errors['deadline'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['deadline']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3 repeatedly_area">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">申し込み締切日( 全て受講 )</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input name="all_deadline" class="form-control" type="date"
											value="<?= htmlspecialchars($old_input['all_deadline'] ?? ''); ?>" />
										<?php if (!empty($errors['deadline'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['deadline']); ?></div>
										<?php endif; ?>
									</div>
									<div class="mb-3">
										<label class="form-label">アーカイブ配信期間</label>
										<span class="badge bg-danger">必須</span>
										<input name="archive_streaming_period" class=" form-control" min="0" type="number"
											value="<?= htmlspecialchars($old_input['archive_streaming_period'] ?? ''); ?>" />
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
										<select id="event_custom_id" class=" form-control mb-3" name="event_custom_id">
											<option value=1>イベント一般</option>
											<option value=2>適塾記念会イベント</option>
											<option value=3>生命科学分野イベント</option>
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
										<textarea name="note" class="form-control" rows="5"><?= htmlspecialchars($old_input['note'] ?? ''); ?></textarea>
										<?php if (!empty($errors['note'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['note']); ?></div>
										<?php endif; ?>
									</div>

									<button type="submit" class="btn btn-primary">登録</button>
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
	});
	$(document).ready(function() {
		// select要素が変更された時にアラートを表示
		$('select[name="event_kbn"]').on('change', function() {
			if ($(this).val() == 2) {
				$('.onetime_area').css('display', 'none');
				$('.repeatedly_area').css('display', 'block');
			} else {
				$('.onetime_area').css('display', 'block');
				$('.repeatedly_area').css('display', 'none');
			}
		});
	});
	$(document).ready(function() {
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
</script>