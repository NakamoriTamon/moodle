	<?php $currentPage = basename($_SERVER['REQUEST_URI']); ?>

	<nav id="sidebar" class="sidebar js-sidebar">
		<div class="sidebar-content js-simplebar">
			<a class="sidebar-brand" href="index.html">
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
					<a href="#information" data-bs-toggle="collapse" class="sidebar-link collapsed">
						<i class="align-middle" data-feather="database"></i> <span class="align-middle">情報管理</span>
					</a>
					<ul id="information" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/management/index.php">管理者一覧</a></li>
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/management/event_registration.php">イベント登録情報管理</a></li>
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/management/member_registration.php">費用請求</a></li>
						<li class="sidebar-item"><a class="sidebar-link" href="pages-404.html">管理者用申込画面(現金ユーザー登録)</a></li>
					</ul>
				</li>
				<li class="sidebar-item">
					<a href="#send" data-bs-toggle="collapse" class="sidebar-link collapsed">
						<i class="align-middle" data-feather="send"></i> <span class="align-middle">送信機能</span>
					</a>
					<ul id="send" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/message/index.php">DM送信</a></li>
						<!-- <li class="sidebar-item"><a class="sidebar-link" href="pages-sign-up.html">メールマガジン</a></li> -->
					</ul>
				</li>
				<li class="sidebar-item">
					<a href="#event" data-bs-toggle="collapse" class="sidebar-link collapsed">
						<i class="align-middle" data-feather="book-open"></i><span class="align-middle">イベント</span>
					</a>
					<ul id="event" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/event/index.php">イベント一覧</a></li>
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/event/material.php">講義資料アップロード</a></li>
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/event/movie.php">講義動画アップロード</a></li>
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/event/custom_index.php">イベントカスタムフィールド</a></li>
					</ul>
				</li>
				<li class="sidebar-item">
					<a href="#survey" data-bs-toggle="collapse" class="sidebar-link collapsed">
						<i class="align-middle" data-feather="users"></i> <span class="align-middle">アンケート</span>
					</a>
					<ul id="survey" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/survey/index.php">アンケート集計</a></li>
						<li class="sidebar-item"><a class="sidebar-link" href="pages-404.html">アンケートカスタムフィールド</a></li>
					</ul>
				</li>
				<li class="sidebar-item">
					<a href="#master" data-bs-toggle="collapse" class="sidebar-link collapsed">
						<i class="align-middle" data-feather="table"></i> <span class="align-middle">各種マスタ</span>
					</a>
					<ul id="master" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
						<li class="sidebar-item"><a class="sidebar-link" href="/custom/admin/app/Views/master/category/index.php">カテゴリーマスタ</a></li>
						<li class="sidebar-item"><a class="sidebar-link" href="pages-404.html">講師マスタ</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</nav>

	<script>
		$(document).ready(function() {
			const currentPath = window.location.pathname;
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