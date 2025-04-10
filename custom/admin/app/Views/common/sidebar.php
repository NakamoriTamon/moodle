	<?php $currentPage = basename($_SERVER['REQUEST_URI']); ?>

	<nav id="sidebar" class="sidebar js-sidebar">
		<div class="sidebar-content js-simplebar">
			<a class="sidebar-brand" href="#">
				<span class="sidebar-brand-text align-middle">
					大阪大学 知の広場
				</span>
			</a>

			<div class="sidebar-user">
				<div class="d-flex justify-content-center">
				</div>
			</div>

			<ul class="sidebar-nav">
				<li class="sidebar-item">
					<a href="#information-side" data-bs-toggle="collapse" class="sidebar-link collapsed">
						<i class="align-middle" data-feather="database"></i> <span class="align-middle">情報管理</span>
					</a>
					<ul id="information-side" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
						<?php if (in_array('admin', $roles)): ?>
							<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/management/index.php">管理者一覧</a></li>
							<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/management/user_registration.php">ユーザー情報管理</a></li>
						<?php endif; ?>
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/management/event_registration.php">イベント登録情報管理</a></li>
						<?php if (in_array('admin', $roles) || $USER->id == MEMBERSHIP_ACCESS_ACOUNT): ?>
							<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/management/membership_fee_registration.php">費用請求</a></li>
						<?php endif; ?>
						<!-- <li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/management/cash_application.php">管理者用申込画面<p class="side_break">( 現金ユーザー登録 )</p></a></li> -->
					</ul>
				</li>
				<li class="sidebar-item">
					<a href="#send-side" data-bs-toggle="collapse" class="sidebar-link collapsed">
						<i class="align-middle" data-feather="send"></i> <span class="align-middle">送信機能</span>
					</a>
					<ul id="send-side" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/message/index.php">DM送信</a></li>
						<!-- <li class="sidebar-item"><a class="sidebar-link" href="pages-sign-up.html">メールマガジン</a></li> -->
					</ul>
				</li>
				<li class="sidebar-item">
					<a href="#event-side" data-bs-toggle="collapse" class="sidebar-link collapsed">
						<i class="align-middle" data-feather="book-open"></i><span class="align-middle">イベント</span>
					</a>
					<ul id="event-side" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/event/index.php">イベント一覧</a></li>
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/event/material.php">講義資料アップロード</a></li>
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/event/movie.php">講義動画アップロード</a></li>
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/event/custom_index.php">カスタムフィールド</a></li>
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/event/qr.php">QR読取</a></li>
					</ul>
				</li>
				<li class="sidebar-item">
					<a href="#survey-side" data-bs-toggle="collapse" class="sidebar-link collapsed">
						<i class="align-middle" data-feather="users"></i> <span class="align-middle">アンケート</span>
					</a>
					<ul id="survey-side" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/survey/index.php">アンケート集計</a></li>
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/survey/custom_index.php">カスタムフィールド</p></a></li>
					</ul>
				</li>
				<li class="sidebar-item pc-none">
					<a class="sidebar-link" href="/custom/admin/app/Views/login/login.php">
						<i class="align-middle" data-feather="log-out"></i> <span class="align-middle">Log out</span>
					</a>
				</li>
				<!-- 管理者以外アクセス禁止へ -->
				<!-- <li class="sidebar-item">
					<a href="#master" data-bs-toggle="collapse" class="sidebar-link collapsed">
						<i class="align-middle" data-feather="table"></i> <span class="align-middle">各種マスタ</span>
					</a>
					<ul id="master" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/master/category/index.php">カテゴリーマスタ</a></li>
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/master/target/index.php">対象マスタ</a></li>
					</ul>
				</li> -->
			</ul>
		</div>
	</nav>

	<script>
		$(document).ready(function() {
			let currentPath = window.location.pathname;
			if (currentPath == "/custom/admin/app/Views/event/upsert.php") {
				currentPath = "/custom/admin/app/Views/event/index.php";

			}
			if (currentPath == "/custom/admin/app/Views/survey/custom_upsert.php") {
				currentPath = "/custom/admin/app/Views/survey/custom_index.php";
			}
			if (currentPath == "/custom/admin/app/Views/event/custom_upsert.php") {
				currentPath = "/custom/admin/app/Views/event/custom_index.php";
			}
			// マスタ
			if (currentPath == "/custom/admin/app/Views/master/category/upsert.php") {
				currentPath = "/custom/admin/app/Views/master/category/index.php";
			}
			if (currentPath == "/custom/admin/app/Views/master/target/upsert.php") {
				currentPath = "/custom/admin/app/Views/master/target/index.php";
			}

			const sidebarLinks = $(".sidebar-link");

			sidebarLinks.each(function() {
				const link = $(this);

				if (link.attr("href").includes(currentPath)) {
					const linkParent = link.parent();
					linkParent.addClass("active");

					const parentCollapse = link.closest(".collapse");
					if (parentCollapse.length) {
						parentCollapse.addClass("show");
						const parentLink = parentCollapse.prev(".sidebar-link");
						if (parentLink.length) {
							parentLink.removeClass("collapsed");
						}
					}
				}
			});
		});
	</script>