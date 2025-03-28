<?php
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/survey/survey_custom_controller.php');
$survey_custom_controller = new SurveyCustomController();
$survey_custom_list = $survey_custom_controller->index();
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
					<p class="header-title title ms-4 fs-4 fw-bold mb-0">カスタムフィールド一覧</p>
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
					<div class="card min-70vh">
						<div class="card-body p-0">
							<div class="d-flex w-100 mt-3">
								<button onclick="window.location.href='/custom/admin/app/Views/survey/custom_upsert.php';"
									class="btn btn-primary mt-3 mb-3 ms-auto">新規登録
								</button>
							</div>
							<div class="card m-auto mb-5 w-95">
								<table class="table table-responsive table-striped table_list">
									<thead>
										<tr>
											<th>ID</th>
											<th>アンケートカテゴリ区分</th>
											<th>紐づけられているイベント</th>
											<th class="text-center">Actions</th>
										</tr>
									</thead>
									<tbody>
									<?php foreach ($survey_custom_list as $survey_custom) { ?>
										<?php $last_key = array_key_last($survey_custom['event']); ?>
										<tr>
											<td><?= htmlspecialchars($survey_custom['id']) ?></td>
											<td><?= htmlspecialchars($survey_custom['name']) ?></td>
											<td>
												<?php foreach ($survey_custom['event'] as $key => $event) { ?>
													<a href="/custom/admin/app/Views/event/upsert.php?id=<?= htmlspecialchars($event['id']) ?>" class="text-decoration-underline link-primary"><?= htmlspecialchars($event['name']) ?></a>
													<?= ($key !== $last_key) ? '、' : '' ?>
												<?php } ?>
											</td>
											<td class="text-center">
												<a href='/custom/admin/app/Views/survey/custom_upsert.php?id=<?= htmlspecialchars($survey_custom['id']) ?>' class="me-3">
													<i class="align-middle" data-feather="edit-2"></i>
												</a>
												<a class="delete-link" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal-<?= htmlspecialchars($survey_custom['id']) ?>">
													<i class="align-middle" data-feather="trash"></i>
												</a>
											</td>
										</tr>
										<!-- 削除確認モーダル -->
										<div class="modal fade" id="confirmDeleteModal-<?= htmlspecialchars($survey_custom['id']) ?>" tabindex="-1" aria-hidden="true">
											<div class="modal-dialog modal-dialog-centered">
												<form method="POST" action="/custom/admin/app/Controllers/survey/survey_custom_delete_controller.php">
													<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
													<input type="hidden" name="id" value="<?= htmlspecialchars($survey_custom['id']) ?>">
													<div class="modal-content">
														<div class="modal-header">
															<h5 class="modal-title">削除確認</h5>
															<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
														</div>
														<div class="modal-body">
															<p class="mt-3">「<?= htmlspecialchars($survey_custom['name']) ?>」を削除します。本当によろしいですか？</p>
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
															<button type="submit" class="btn btn-danger">削除</button>
														</div>
													</div>
												</form>
											</div>
										</div>
									<?php } ?>
									</tbody>
								</table>
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