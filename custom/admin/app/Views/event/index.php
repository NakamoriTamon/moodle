<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/event/event_controller.php');

$event_status = require '/var/www/html/moodle/custom/path/to/event_status.php';
$eventController = new EventController();
$events = $eventController->index();
?>

<body id="event" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative d-block">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<div class="navbar-collapse collapse">
					<p class="title ms-4 fs-4 fw-bold mb-0">イベント一覧</p>
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
						<div class="card-body p-025">
							<div class="d-flex justify-content-between">
								<div class="mb-3 w-100">
									<label class="form-label" for="notyf-message">カテゴリー</label>
									<select name="category_id" class="form-control">
										<option value="">すべて</option>
										<?php foreach ($categorys as $category): ?>
											<option value="<?= htmlspecialchars($category['id']) ?>"
											<?= in_array($category['id'], $old_input['category_id'] ?? []) ? 'selected' : '' ?>>
												<?= htmlspecialchars($category['name']) ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="ms-3 mb-3 w-100">
									<label class="form-label" for="notyf-message">開催ステータス</label>
									<select name="event_status" class="form-control">
										<option value="">すべて</option>$
										<?php foreach ($event_statuses as $key => $event_status): ?>
											<option value="<?= htmlspecialchars($key) ?>"
												<?= isset($old_input['event_status']) && $key == $old_input['event_status'] ? 'selected' : '' ?>>
													<?= htmlspecialchars($event_status) ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<div class="mb-4">
								<label class="form-label" for="notyf-message">イベント名</label>
								<select name="event_id" class="form-control">
									<option value="">すべて</option>
									<option value=1>タンパク質の精製技術の基礎</option>
									<option value=2>AIと機械学習の基礎講座</option>
									<option value=3>量子コンピュータ入門: 次世代計算技術の扉を開く</option>
									<option value=4>気候変動と持続可能なエネルギーソリューション</option>
									<option value=5>心理学で学ぶ意思決定と行動経済学</option>
								</select>
							</div>
							<!-- <hr> -->
							<div class="d-flex w-100">
								<button id="search-button" class="btn btn-primary mb-3 me-0 ms-auto">検索</button>
							</div>
						</div>
					</div>
					<div class="col-12 col-lg-12">
						<div class="card">
							<div class="card-body p-0">
								<div class="d-flex w-100 mt-3">
									<button onclick="window.location.href='/custom/admin/app/Views/event/upsert.php';" class="btn btn-primary mt-3 mb-3 ms-auto">新規登録</button>
								</div>
								<div class="card m-auto mb-5 overflow-auto w-95">
									<table class="table table-responsive table-striped table_list">
										<thead>
											<tr>
												<th class="ps-4 pe-4">ID</th>
												<th class="ps-4 pe-4">タイトル</th>
												<th class="ps-4 pe-4">開催ステータス</th>
												<th class="ps-4 pe-4">講義形式</th>
												<th class="ps-4 pe-4">会場名</th>
												<th class="ps-4 pe-4">定員</th>
												<th class="ps-4 pe-4">参加費</th>
												<th class="text-center ps-4 pe-4">Actions</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach($events as $key => $event): ?>
												<tr>
													<td class="ps-4 pe-4"><?= htmlspecialchars($event['id']); ?></td>
													<td class="ps-4 pe-4"><?= htmlspecialchars($event['name']); ?></td>
													<td class="ps-4 pe-4">開催前</td>
													<td class="ps-4 pe-4">
														<?php foreach ($event['lecture_formats'] as $key => $lecture_format): ?>
															<?php if ($key == 0): ?>
																<?= htmlspecialchars($lecture_format['name']); ?>
															<?php else: ?>
																, <?= htmlspecialchars($lecture_format['name']); ?>
															<?php endif; ?>
														<?php endforeach; ?>
													</td>
													<td class="ps-4 pe-4"><?= htmlspecialchars($event['venue_name']); ?></td>
													<td class="ps-4 pe-4"><?= htmlspecialchars(number_format($event['capacity'])); ?>人</td>
													<td class="ps-4 pe-4"><?= htmlspecialchars(number_format($event['participation_fee'])); ?>円</td>
													<td class="text-center ps-4 pe-4 text-nowrap">
														<a  href="upsert.php?id=<?= htmlspecialchars($event['id']); ?>" class="me-3"><i class="align-middle" data-feather="edit-2"></i></a>
														<a class="delete-link"><i class=" align-middle" data-feather="trash"></i></a>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
								<!-- 削除確認モーダル -->
								<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered">
										<div class="modal-content">
											<div class="modal-header">
												<h5 class="modal-title">削除確認</h5>
												<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
											</div>
											<div class="modal-body">
												<p class="mt-3">「イベントタイトル」を削除します。本当によろしいですか</p>
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
												<button type="button" class="btn btn-danger" id="confirmDeleteButton">削除</button>
											</div>
										</div>
									</div>
								</div>
								<div class="d-flex">
									<div class="dataTables_paginate paging_simple_numbers ms-auto mr-025" id="datatables-buttons_paginate">
										<ul class="pagination">
											<li class="paginate_button page-item previous" id="datatables-buttons_previous"><a href="#" aria-controls="datatables-buttons" data-dt-idx="0" tabindex="0" class="page-link">Previous</a></li>
											<li class="paginate_button page-item active"><a href="#" aria-controls="datatables-buttons" data-dt-idx="1" tabindex="0" class="page-link">1</a></li>
											<li class="paginate_button page-item "><a href="#" aria-controls="datatables-buttons" data-dt-idx="2" tabindex="0" class="page-link">2</a></li>
											<li class="paginate_button page-item "><a href="#" aria-controls="datatables-buttons" data-dt-idx="3" tabindex="0" class="page-link">3</a></li>
											<li class="paginate_button page-item "><a href="#" aria-controls="datatables-buttons" data-dt-idx="4" tabindex="0" class="page-link">4</a></li>
											<li class="paginate_button page-item "><a href="#" aria-controls="datatables-buttons" data-dt-idx="5" tabindex="0" class="page-link">5</a></li>
											<li class="paginate_button page-item "><a href="#" aria-controls="datatables-buttons" data-dt-idx="6" tabindex="0" class="page-link">6</a></li>
											<li class="paginate_button page-item next" id="datatables-buttons_next"><a href="#" aria-controls="datatables-buttons" data-dt-idx="7" tabindex="0" class="page-link">Next</a></li>
										</ul>
									</div>
								</div>
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