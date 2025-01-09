<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
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
					<p class="title ms-4 fs-4 fw-bold mb-0">イベント一覧</p>
					<ul class="navbar-nav navbar-align">
						<li class="nav-item dropdown">
							<a class="nav-icon pe-md-0 dropdown-toggle" href="#" data-bs-toggle="dropdown">
								<div class="fs-5 me-4">システム管理者</div>
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
							<div class="d-flex w-100 mt-3">
								<button onclick="window.location.href='/custom/admin/app/Views/event/upsert.php';" class="btn btn-primary mt-3 mb-3 ms-auto">新規登録</button>
							</div>
							<div class="card m-auto mb-5 overflow-auto w-95">
								<table class="table table-responsive table-striped table_list">
									<thead>
										<tr>
											<th class="ps-4 pe-4">ID</th>
											<th class="ps-4 pe-4">タイトル</th>
											<th class="ps-4 pe-4">開催日</th>
											<th class="ps-4 pe-4">講義形式</th>
											<th class="ps-4 pe-4">会場名</th>
											<th class="ps-4 pe-4">定員</th>
											<th class="ps-4 pe-4">参加費</th>
											<th class="text-center ps-4 pe-4">Actions</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td class="ps-4 pe-4">1</td>
											<td class="ps-4 pe-4">タンパク質の生成技術の基礎</td>
											<td class="ps-4 pe-4">2025/1/10</td>
											<td class="ps-4 pe-4">会場</td>
											<td class="ps-4 pe-4">〇〇研究室</td>
											<td class="ps-4 pe-4">50人</td>
											<td class="ps-4 pe-4">5,000円</td>
											<td class="text-center ps-4 pe-4 text-nowrap">
												<a class="me-3"><i class="align-middle" data-feather="edit-2"></i></a>
												<a><i class="align-middle" data-feather="trash"></i></a>
											</td>
										</tr>
										<tr>
											<td class="ps-4 pe-4">2</td>
											<td class="ps-4 pe-4">AIと機械学習の基礎講座</td>
											<td class="ps-4 pe-4">2025/1/15</td>
											<td class="ps-4 pe-4 text-nowrap">会場（オンデマンドあり）</td>
											<td class="ps-4 pe-4 text-nowrap">〇〇講義棟A 大ホール</td>
											<td class="ps-4 pe-4">50人</td>
											<td class="ps-4 pe-4">5,500円</td>
											<td class="text-center ps-4 pe-4 text-nowrap">
												<a class="me-3"><i class="align-middle" data-feather="edit-2"></i></a>
												<a><i class="align-middle" data-feather="trash"></i></a>
											</td>
										</tr>
										<tr>
											<td class="ps-4 pe-4">3</td>
											<td class="ps-4 pe-4 text-nowrap">量子コンピュータ入門: 次世代計算技術の扉を開く</td>
											<td class="ps-4 pe-4">2025/1/15</td>
											<td class="ps-4 pe-4 text-nowrap">オンライン</td>
											<td class="ps-4 pe-4 text-nowrap"></td>
											<td class="ps-4 pe-4">30人</td>
											<td class="ps-4 pe-4">3,000円</td>
											<td class="text-center ps-4 pe-4 text-nowrap">
												<a class="me-3"><i class="align-middle" data-feather="edit-2"></i></a>
												<a><i class="align-middle" data-feather="trash"></i></a>
											</td>
										</tr>
										<tr>
											<td class="ps-4 pe-4">4</td>
											<td class="ps-4 pe-4">気候変動と持続可能なエネルギーソリューション</td>
											<td class="ps-4 pe-4">2025/1/17</td>
											<td class="ps-4 pe-4 text-nowrap">ハイブリッド</td>
											<td class="ps-4 pe-4 text-nowrap">〇〇講義棟 国際ホール5F</td>
											<td class="ps-4 pe-4">150人</td>
											<td class="ps-4 pe-4">8,000円</td>
											<td class="text-center ps-4 pe-4 text-nowrap">
												<a class="me-3"><i class="align-middle" data-feather="edit-2"></i></a>
												<a><i class="align-middle" data-feather="trash"></i></a>
											</td>
										</tr>
										<tr>
											<td class="ps-4 pe-4">5</td>
											<td class="ps-4 pe-4">心理学で学ぶ意思決定と行動経済学</td>
											<td class="ps-4 pe-4">2025/2/10</td>
											<td class="ps-4 pe-4 text-nowrap">オンライン</td>
											<td class="ps-4 pe-4 text-nowrap"></td>
											<td class="ps-4 pe-4">100人</td>
											<td class="ps-4 pe-4">5,000円</td>
											<td class="text-center ps-4 pe-4 text-nowrap">
												<a class="me-3"><i class="align-middle" data-feather="edit-2"></i></a>
												<a><i class="align-middle" data-feather="trash"></i></a>
											</td>
										</tr>

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