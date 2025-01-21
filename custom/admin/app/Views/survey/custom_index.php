<?php
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/event_controller.php');
$eventController = new EventController();
$events = $eventController->index();
?>

<body id="event" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<div class="navbar-collapse collapse">
					<p class="title ms-4 fs-4 fw-bold mb-0">アンケートカスタムフィールド一覧</p>
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
							<div class="card m-auto mb-5">
								<table class="table table-responsive table-striped table_list w-95">
									<thead>
										<tr>
											<th>ID</th>
											<th>アンケートカテゴリ区分</th>
											<th class="d-none d-md-table-cell">紐づけられているイベント</th>
											<th class="text-center">Actions</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>1</td>
											<td>イベント一般</td>
											<td>
												<a class="text-decoration-underline link-primary">イベントA</a>、
												<a class="text-decoration-underline link-primary">イベントB</a>、
												<a class="text-decoration-underline link-primary">イベントC</a>
											</td>
											<td class="text-center">
												<a class="me-3" href='/custom/admin/app/Views/survey/custom_upsert.php'><i class="align-middle" data-feather="edit-2"></i></a>
												<a class="delete-link"><i class="align-middle" data-feather="trash"></i></a>
											</td>
										</tr>
										<tr>
											<td>2</td>
											<td>適塾記念会イベント</td>
											<td>
												<a class="text-decoration-underline link-primary">イベントD</a>、
												<a class="text-decoration-underline link-primary">イベントE</a>
											</td>
											<td class="text-center">
												<a class="me-3" href='/custom/admin/app/Views/survey/custom_upsert.php'><i class="align-middle" data-feather="edit-2"></i></a>
												<a class="delete-link"><i class="align-middle" data-feather="trash"></i></a>
											</td>
										</tr>
										<tr>
											<td>3</td>
											<td>生命科学分野イベント</td>
											<td>
												<a class="text-decoration-underline link-primary">イベントF</a>、
												<a class="text-decoration-underline link-primary">イベントG</a>、
												<a class="text-decoration-underline link-primary">イベントH</a>
											</td>
											<td class="text-center">
												<a class="me-3" href='/custom/admin/app/Views/survey/custom_upsert.php'><i class="align-middle" data-feather="edit-2"></i></a>
												<a class="delete-link"><i class="align-middle" data-feather="trash"></i></a>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
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
								<p class="mt-3">「アンケートカテゴリ区分名」を削除します。本当によろしいですか</p>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
								<button type="button" class="btn btn-danger" id="confirmDeleteButton">削除</button>
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