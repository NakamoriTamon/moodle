<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php'); ?>

<body id="" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<a class="sidebar-toggle js-sidebar-toggle">
					<i class="hamburger align-self-center"></i>
				</a>
				<div class="navbar-collapse collapse">
					<p class="header-title title ms-4 fs-4 fw-bold mb-0">カスタムフィールド登録</p>
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
						<div class="card-body p-0 min-70vh">
							<p class="content_title p-3">アンケートカスタムフィールド登録</p>
							<div class="form-wrapper">
								<form method="POST" action="">
									<div class="field-container">
										<input type="hidden" name="eventId">
										<div class="mb-4">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">カテゴリ区分名</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input type="text" name="" class="form-control" value="<?php if ($_GET['id']) { ?>イベント一般<?php } ?>">
										</div>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">項目名</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input type="text" name="" class="form-control" value="<?php if ($_GET['id']) { ?>今後受講してみたい講師の方はいらっしゃいますか <?php } ?>">
										</div>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">フィールド名</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input type="text" name="name" class="form-control <?php if ($_GET['id']) { ?>readonly-select<?php } ?>" value="<?php if ($_GET['id']) { ?>lecturer_name<?php } ?>">
										</div>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">表示順</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input type="number" name="" class="form-control" value=<?php if ($_GET['id']) { ?>1<?php } ?>>
										</div>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">フィールドタイプ</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<select name="fieldType[]" class="form-control mb-3 <?php if ($_GET['id']) { ?>readonly-select<?php } ?>">
												<option value="text">テキスト</option>
												<option value="textarea">テキストエリア</option>
												<option value="checkbox">チェックボックス</option>
												<option value="radio">ラジオ</option>
												<option value="date">日付</option>
											</select>
										</div>
										<div class="mb-5">
											<label class="form-label ">選択肢 (カンマ区切り)</label>
											<input type="text" name="" class="form-control <?php if ($_GET['id']) { ?>readonly-select<?php } ?>">
										</div>
									</div>
									<hr>
									<?php if ($_GET['id']) { ?>
										<div class="field-container mt-5">
											<div class="add_area">
												<input type="hidden" name="eventId">
												<div class="mb-3">
													<div class="form-label d-flex align-items-center">
														<label class="me-2">項目名</label>
														<span class="badge bg-danger">必須</span>
													</div>
													<input type="text" name="" class="form-control" value="<?php if ($_GET['id']) { ?>今後の人生に活かせるような内容でしたか<?php } ?>">
												</div>
												<div class="mb-3">
													<div class="form-label d-flex align-items-center">
														<label class="me-2">フィールド名</label>
														<span class="badge bg-danger">必須</span>
													</div>
													<input type="text" name="" class="form-control <?php if ($_GET['id']) { ?>readonly-select<?php } ?>" value="<?php if ($_GET['id']) { ?>life_check<?php } ?>">
												</div>
												<div class="mb-3">
													<div class="form-label d-flex align-items-center">
														<label class="me-2">表示順</label>
														<span class="badge bg-danger">必須</span>
													</div>
													<input type="number" name="" class="form-control" value=<?php if ($_GET['id']) { ?>2<?php } ?>>
												</div>
												<div class=" mb-3">
													<div class="form-label d-flex align-items-center">
														<label class="me-2">フィールドタイプ</label>
														<span class="badge bg-danger">必須</span>
													</div>
													<select name="" class="form-control mb-3 <?php if ($_GET['id']) { ?>readonly-select<?php } ?>">
														<option value="text">テキスト</option>
														<option value="textarea">テキストエリア</option>
														<option value="checkbox">チェックボックス</option>
														<option selected value="radio">ラジオ</option>
														<option value="date">日付</option>
													</select>
												</div>
												<div class="mb-3">
													<label class="form-label">選択肢 (カンマ区切り)</label>
													<input type="text" name="" class="form-control <?php if ($_GET['id']) { ?>readonly-select<?php } ?>" value="<?php if ($_GET['id']) { ?>そう思う,どちらとも言えない,そう思わない<?php } ?>">
												</div>
											</div>
											<div class="mb-3">
												<div class="form-label mt-3 d-flex align-items-center">
													<button type="button" class="delete_btn btn btn-danger ms-auto me-0">削除</button>
												</div>
											</div>
											<hr>
										</div>
									<?php } ?>
									<div class="d-flex">
										<button type="button" id="add_btn" class=" btn btn-primary ms-auto">追加</button>
									</div>
									<button type="button" id="submit" class="mt-5 btn btn-primary ms-auto">登録</button>
								</form>
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
			const Values = $('select[name="fieldType[]"]').map(function() {
				if ($(this).val() == 'radio' || $(this).val() == 'checkbox') {
					$(this).next().css('display', 'block');
				}
			}).get();
		});
		$(document).on('change', 'select[name="fieldType[]"]', function() {
			if ($(this).val() === 'checkbox' || $(this).val() === 'radio') {
				$(this).next().css('display', 'block');
			} else {
				$(this).next().css('display', 'none');
			}
		});
		$(document).on('click', '.delete_btn', function() {
			event.preventDefault();
			$(this).parent().find('input[name="id[]"]').prop("disabled", true);
			$(this).parents('.field-container').css('display', 'none');
		});

		// フィールド追加
		$("#add_btn").on("click", function() {
			const newField = document.createElement('div');
			newField.classList.add('field-container', 'mt-5');
			newField.innerHTML = ` 
				<div class="add_area">
				<input type="hidden" name="eventId">
				<div class="mb-3">
					<div class="form-label d-flex align-items-center">
						<label class="me-2">項目名</label>
						<span class="badge bg-danger">必須</span>
					</div>
					<input type="text" name="" class="form-control">
				</div>
				<div class="mb-3">
					<div class="form-label d-flex align-items-center">
						<label class="me-2">フィールド名</label>
						<span class="badge bg-danger">必須</span>
					</div>
					<input type="text" name="" class="form-control">
				</div>
				<div class="mb-3">
					<div class="form-label d-flex align-items-center">
						<label class="me-2">表示順</label>
						<span class="badge bg-danger">必須</span>
					</div>
					<input type="number" name="" class="form-control">
				</div>
				<div class=" mb-3">
					<div class="form-label d-flex align-items-center">
						<label class="me-2">フィールドタイプ</label>
						<span class="badge bg-danger">必須</span>
					</div>
					<select name="" class="form-control mb-3">
						<option value="text">テキスト</option>
						<option value="textarea">テキストエリア</option>
						<option value="checkbox">チェックボックス</option>
						<option value="radio">ラジオ</option>
						<option value="date">日付</option>
					</select>
				</div>
				<div class="mb-3">
					<label class="form-label">選択肢 (カンマ区切り)</label>
					<input type="text" name="" class="form-control">
				</div>
				</div>
				<div class ="mb-3"><div class ="form-label mt-3 d-flex align-items-center">
				<button type="button" class ="delete_btn btn btn-danger ms-auto me-0">削除</button></div></div><hr>
    		`;

			// 新しいフィールドを追加
			$(this).parent().before(newField);
		});
		$(document).on('click', '.delete_btn', function() {
			console.log($(this).closest('.add_area'));
			$(this).closest('.add_area').remove();
		});
		// モック用アラート　本番時は消してください
		$('#submit').on('click', function(event) {
			sessionStorage.setItem('alert', 'aaasss');
			setTimeout(() => {
				location.href = '/custom/admin/app/Views/survey/custom_index.php';
			}, 50);
		});
	</script>
</body>

</html>