<?php
require_once('/var/www/html/moodle/config.php');
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/survey/survey_controller.php');

$survey_controller = new SurveyController();
$result_list = $survey_controller->index();

// バリデーションエラー
$errors   = $_SESSION['errors']   ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

// 情報取得
$category_list = $result_list['category_list'] ?? [];
$event_list = $result_list['event_list']  ?? [];
$survey_list = $result_list['survey_list'];
$survey_period = $result_list['survey_period'];
$survey_field_list = $result_list['survey_field_list'] ?? [];
$course_list = $result_list['course_list'] ?? [];

// ページネーション
$total_count = $result_list['total_count'];
$per_page = $result_list['per_page'];
$current_page = $result_list['current_page'];
$page = $result_list['page'];
$department = $result_list['department'];
$is_single = $result_list['is_single'];
$is_disp_no = $result_list['is_disp_no'];

$current_page = max(1, (int)$current_page);
$page         = max(1, (int)$page);
?>


<body id="survey" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative d-block">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<a class="sidebar-toggle js-sidebar-toggle">
					<i class="hamburger align-self-center"></i>
				</a>
				<div class="navbar-collapse collapse">
					<p class="header-title title ms-4 fs-4 fw-bold mb-0">アンケート集計</p>
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
				<div class="card">
					<div class="card-body p-055 p-025">
						<form id="form" method="POST" action="/custom/admin/app/Views/survey/index.php" class="w-100">
							<input type="hidden" name="page" value="<?= $current_page ?>">
							<div class="d-flex sp-block justify-content-between">
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
								<div class="ms-3 sp-ms-0 mb-3 w-100">
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
							<div class="d-flex sp-block justify-content-between">
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
									<label class="form-label" for="notyf-message">回数</label>
									<div class="d-flex align-items-center">
										<select name="course_no" class="form-control w-100" <?= $is_single ? 'disabled' : '' ?>>
											<option value="">未選択</option>
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
							<!-- <hr> -->
							<div class="d-flex w-100">
								<button id="search-button" class="btn btn-primary mb-3 me-0 ms-auto">検索</button>
							</div>
						</form>
					</div>
				</div>
				<!-- 非表示のform -->
				<form id="csvExportForm" method="POST" action="/custom/admin/app/Controllers/survey/survey_export_controller.php">
					<input type="hidden" name="category_id" value="<?= $old_input['category_id'] ?? '' ?>">
					<input type="hidden" name="course_info_id" value="<?= $result_list['course_info_id'] ?>">
					<input type="hidden" name="event_id" value="<?= $old_input['event_id'] ?? '' ?>">
					<input type="hidden" name="event_status_id" value="<?= $old_input['event_status_id'] ?? '' ?>">
					<input type="hidden" name="course_no" value="<?= $old_input['course_no'] ?? '' ?>">
				</form>

				<div class="search-area col-12 col-lg-12">
					<div class="card">
						<div class="card-body p-0">
							<div class="d-flex w-100 mt-3 align-items-center justify-content-end">
								<div class=" mt-3 mb-3 me-auto ml-025 fw-bold">総件数 : <?= $total_count ?? 0 ?> 件</div>
								<button id="csv_button" class="btn btn-primary ms-auto mt-3 mb-3 mr-025 d-flex justify-content-center align-items-center">
									<i class="align-middle me-1" data-feather="download"></i>CSV出力
								</button>
							</div>
							<div class="card m-auto mb-5 w-95">
								<table class="table table-responsive table-striped table_list text-break">
									<thead>
										<tr>
											<th class="w-25 p-4">回答時間</th>
											<?php if ($is_disp_no) { ?><th class="w-25 p-4">回数</th><?php } ?>
											<th class="w-25 p-4">本日のイベントについて、ご意見・ご感想をお書きください</th>
											<th class="w-25 p-4">今までに大阪大学<?php echo htmlspecialchars(' [ ' . $department . ' ] ') ?>主催のイベントに参加されたことはありますか </th>
											<th class="w-25 p-4">本日のイベントをどのようにしてお知りになりましたか</th>
											<th class="w-25 p-4">その他 </th>
											<th class="w-25 p-4">本日のイベントに参加した理由は何ですか </th>
											<th class="w-25 p-4">その他</th>
											<th class="w-25 p-4">本日のイベントの満足度について、あてはまるもの1つをお選びください</th>
											<th class="w-25 p-4">
												本日のイベントで特に良かった点について教えてください。以下に当てはまるもの
												があれば、1つお選びください。あてはまるものがなければ「その他」の欄に記述し
												てください
											</th>
											<th class="w-25 p-4">その他</th>
											<th class="w-25 p-4">
												（会場での開催の場合のみ回答ください）本日のイベントの開催環境について、あてはまるものを１つお選びください。
												※「あまり快適ではなかった」「全く快適ではなかった」と回答された方は次の
												質問にその理由を教えてください
											</th>
											<th class="w-25 p-4">「あまり快適ではなかった」「全く快適ではなかった」と回答された方はその理由を教えてください。</th>
											<th class="w-25 p-4">今後の大阪大学<?php echo htmlspecialchars(' [ ' . $department . ' ] ') ?>主催のイベントで、希望するジャンルやテーマ、話題があれば、ご提案ください</th>
											<th class="w-25 p-4">年代を教えて下さい</th>
											<th class="w-25 p-4">ご職業や学生区分を教えてください</th>
											<th class="w-25 p-4">性別を教えてください</th>
											<th class="w-25 p-4">お住まいの地域を教えてください</th>
											<?php foreach ($survey_field_list as $survey_field): ?>
												<th class="w-25 p-4"><?= htmlspecialchars($survey_field['name']) ?></th>
											<?php endforeach; ?>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($survey_list as $key => $survey): ?>
											<?php
											$found_num_list = array_map('trim', explode(",", $survey['found_method'] ?? ""));
											$reason_num_list = array_map('trim', explode(",", $survey['reason'] ?? ""));
											$satisfaction_num_list = array_map('trim', explode(",", $survey['satisfaction'] ?? ""));
											?>
											<tr>
												<td class="p-4"><?= htmlspecialchars(date("Y/n/j H:i", strtotime('+9 hours', strtotime($survey['created_at'] ?? '')))) ?></td>
												<?php if ($is_disp_no) { ?>
													<td class="p-4"><?= '第' . htmlspecialchars($survey['course_info']['no'] ?? '') . '回' ?></td>
												<?php } ?>
												<td class="p-4"><?= htmlspecialchars($survey['thoughts'] ?? '') ?></td>
												<td class="p-4">
													<?= htmlspecialchars(DECISION_LIST[$survey['attend']] ?? '') ?>
												</td>
												<td class="p-4">
													<?php
													$last_key = count($found_num_list) - 1;
													foreach ($found_num_list as $key => $found_num) { ?>
														<?= htmlspecialchars(FOUND_METHOD_LIST[$found_num] ?? '') ?>
														<?= $key !== $last_key ? ',' : ''; ?>
													<?php } ?>
												</td>
												<td class="p-4">
													<?= htmlspecialchars($survey['other_found_method'] ?? '') ?>
												</td>
												<td class="p-4">
													<?php
													$last_key = count($reason_num_list) - 1;
													foreach ($reason_num_list as $key => $reason_num) { ?>
														<?= htmlspecialchars(REASON_LIST[$reason_num] ?? '') ?>
														<?= $key !== $last_key ? ',' : ''; ?>
													<?php } ?>
												</td>
												<td class="p-4"><?= htmlspecialchars($survey['other_reason']) ?></td>
												<td class="p-4">
													<?= htmlspecialchars(SATISFACTION_LIST[$survey['satisfaction']] ?? '') ?>
												</td>
												<td class="p-4">
													<?= htmlspecialchars(GOOD_POINT_LIST[$survey['good_point']] ?? '') ?>
												</td>
												<td class="p-4"><?= htmlspecialchars($survey['other_good_point'] ?? '') ?></td>
												<td class="p-4">
													<?= htmlspecialchars(HOLDING_ENVIRONMENT_LIST[$survey['holding_environment']] ?? '') ?>
												</td>
												<td class="p-4"><?= htmlspecialchars($survey['no_good_environment_reason'] ?? '') ?></td>
												<td class="p-4"><?= htmlspecialchars($survey['lecture_suggestions'] ?? '') ?></td>
												<td class="p-4"><?= htmlspecialchars(AGE_LIST[$survey['age']] ?? '') ?></td>
												<td class="p-4"><?= htmlspecialchars(WORK_LIST[$survey['work']] ?? '') ?></td>
												<td class="p-4"><?= htmlspecialchars(SEX_LIST[$survey['sex']] ?? '') ?></td>
												<td class="p-4">
													<?= htmlspecialchars(($survey['prefectures'] ?? '') . ($survey['address'] ?? '')) ?>
												</td>
												<?php foreach ($survey['customfiel'] as $customfiel): ?>
													<td class="p-4"><?= htmlspecialchars($customfiel['input_data'] ?? '') ?></td>
												<?php endforeach; ?>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>

							</div>
							<div class="d-flex">
								<div class="dataTables_paginate paging_simple_numbers ms-auto mr-025" id="datatables-buttons_paginate">
									<ul class="pagination">
										<?php
										$total_pages = ceil($total_count / $per_page);
										$start_page = max(1, $current_page - 1);
										$end_page = min($total_pages, $start_page + 2);

										// 前のページボタン
										if ($current_page > 1): ?>
											<li class="paginate_button page-item previous">
												<a data-page="<?= $current_page - 1 ?>" aria-controls="datatables-buttons" class="page-link">Previous</a>
											</li>
										<?php endif; ?>

										<?php
										// ページ番号の表示
										for ($i = $start_page; $i <= $end_page; $i++): ?>
											<li class="paginate_button page-item <?= $i == $current_page ? 'active' : '' ?>">
												<a data-page="<?= $i ?>" aria-controls="datatables-buttons" class="page-link"><?= $i ?></a>
											</li>
										<?php endfor; ?>

										<?php
										// 次のページボタン
										if ($current_page < $total_pages): ?>
											<li class="paginate_button page-item next">
												<a data-page="<?= $current_page + 1 ?>" aria-controls="datatables-buttons" class="page-link">Next</a>
											</li>
										<?php endif; ?>
									</ul>
								</div>
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
			// 検索フォームから検索時URLを動的に変更
			const params = new URLSearchParams(window.location.search);
			const currentPage = $('input[name="page"]').val();
			params.set('page', currentPage);
			history.replaceState(null, '', window.location.pathname + '?' + params.toString());

			// 検索
			$('select[name="category_id"], select[name="event_status_id"], select[name="event_id"], select[name="course_no"]').change(function() {
				$('input[name="page"]').val(1);
				$('#form').submit();
			});
			$('#search-button').on('click', function(event) {
				$('input[name="page"]').val(1);
			});
			// ページネーション押下時
			$(document).on("click", ".paginate_button a", function(e) {
				e.preventDefault();
				const nextPage = $(this).data("page");
				$('input[name="page"]').val(nextPage);
				$('#form').submit();
			});
			$('#csv_button').on('click', function(event) {
				$('#csvExportForm').submit();
			});
		});
	</script>
</body>

</html>