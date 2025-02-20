<?php
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/event/event_controller.php');
?>

<body id="qr" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative show">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<a class="sidebar-toggle js-sidebar-toggle">
					<i class="hamburger align-self-center"></i>
				</a>
				<div class="navbar-collapse collapse">
					<p class="header-title title ms-4 fs-4 fw-bold mb-0">QR読取</p>
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

			<main class="content d-flex justify-content-center align-items-center" style="height: 100vh;">
				<div class="col-12 col-lg-12">
					<div class="card">
						<div class="card-body p-0 d-flex flex-column justify-content-center align-items-center" style="height: 100%;">
							<div class="qr-frame">
								<div class="video-container d-flex justify-content-center align-items-center">
									<video id="qr-video" autoplay loop>
										<source src="qr-code-video.mp4" type="video/mp4">
										Your browser does not support the video tag.
									</video>
								</div>
								<div class="top-left"></div>
								<div class="top-right"></div>
								<div class="bottom-left"></div>
								<div class="bottom-right"></div>
							</div>
							<p class="scan-text text-center mb-0 fs-3">Scanning...</p>
						</div>
					</div>
				</div>

				<!-- 登録完了モーダル -->
				<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="qrModalLabel">参加登録完了</h5>
							</div>
							<div class="modal-body">
								<p class="mt-2 mb-1 fw-bold">イベント名</p>
								<p>中之島芸術センター 演劇公演 「中の島デリバティブIII」</p>
								<p class="mb-1 fw-bold">ユーザー名</p>
								<p>高橋 望</p>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
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

<script type="module">
	import QrScanner from "https://unpkg.com/qr-scanner@1.4.2/qr-scanner.min.js";

	const videoElem = document.getElementById('qr-video');
	let qrScanner = new QrScanner(videoElem, (result) => {
		console.log(result);
		videoElem.pause();
		$('.scan-text').text('Success');
		$('.scan-text').css('color', '#249f2a');
		$('.qr-frame .top-left, .qr-frame .top-right, .qr-frame .bottom-left, .qr-frame .bottom-right').css('border-color', '#249f2a');
		var modal = new bootstrap.Modal(document.getElementById('qrModal'));
		modal.show();
	});
	qrScanner.start();

	$('#qrModal').on('hidden.bs.modal', function() {
		videoElem.play();
		$('.scan-text').text('Scannning...');
		$('.scan-text').css('color', '#00bcd4');
		$('.qr-frame .top-left, .qr-frame .top-right, .qr-frame .bottom-left, .qr-frame .bottom-right').css('border-color', '#00bcd4');
	});
</script>