<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');

$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);
?>

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
								<div class="fs-5 me-4b text-decoration-underline">システム管理者</div>
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
							<p class="content_title p-3">イベントカスタムフィールド登録</p>
							<div class="form-wrapper">
								<form method="POST" action="/custom/admin/app/Controllers/event/custom_upsert_controller.php">
									<div class=" field-container">
										<input type="hidden" name="id">
										<div class="mb-4">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">カテゴリ区分名</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input type="text" name="name" class="form-control" value="<?= htmlspecialchars($old_input['name'] ?? '') ?>">
											<?php if (!empty($errors['name'])): ?>
												<div class=" text-danger mt-2"><?= htmlspecialchars($errors['name']); ?></div>
											<?php endif; ?>
										</div>
										<input type="hidden" name="event_customfield_id[]">
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">項目名</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input type="text" name="item_name[]" class="form-control" value="<?php if ($_GET['id']) { ?>このイベントに参加するにあたりご要望等ありましたら教えてください<?php } ?>">
										</div>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">フィールド名</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input type="text" name="field_name[]" class="form-control <?php if ($_GET['id']) { ?>readonly-select<?php } ?>"
												<?php if ($_GET['id']) { ?>readonly <?php } ?> value="<?php if ($_GET['id']) { ?>request<?php } ?>">
										</div>
										<div class="mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">表示順</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input type="number" name="sort[]" class="form-control" value=<?php if ($_GET['id']) { ?>1<?php } ?>>
										</div>
										<div class=" mb-3">
											<div class="form-label d-flex align-items-center">
												<label class="me-2">フィールドタイプ</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<select name="field_type[]" class="form-control mb-3 <?php if ($_GET['id']) { ?>readonly-select<?php } ?>">
												<option value=1>テキスト</option>
												<option <?php if ($_GET['id']) { ?>selected<?php } ?> value=2>テキストエリア</option>
												<option value=3>チェックボックス</option>
												<option value=4>ラジオ</option>
												<option value=5>日付</option>
											</select>
										</div>
										<div class="mb-5">
											<label class="me-2 form-label">選択肢 (カンマ区切り)</label>
											<input type="text" name="selection[]" <?php if ($_GET['id']) { ?>readonly <?php } ?> class="form-control <?php if ($_GET['id']) { ?>readonly-select<?php } ?>">
										</div>
									</div>
									<hr>
									<div class="d-flex">
										<button type="button" id="add_btn" class=" btn btn-primary ms-auto" onclick="addField()">追加</button>
									</div>
									<button type="submit" class="mt-5 btn btn-primary ms-auto">登録</button>
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
				<input type="hidden" name="event_customfield_id[]">
				<div class="mb-3">
					<div class="form-label d-flex align-items-center">
						<label class="me-2">項目名</label>
						<span class="badge bg-danger">必須</span>
					</div>
					<input type="text" name="item_name[]" class="form-control">
				</div>
				<div class="mb-3">
					<div class="form-label d-flex align-items-center">
						<label class="me-2">フィールド名</label>
						<span class="badge bg-danger">必須</span>
					</div>
					<input type="text" name="field_name[]" class="form-control">
				</div>
				<div class="mb-3">
					<div class="form-label d-flex align-items-center">
						<label class="me-2">表示順</label>
						<span class="badge bg-danger">必須</span>
					</div>
					<input type="number" name="sort[]" class="form-control">
				</div>
				<div class=" mb-3">
					<div class="form-label d-flex align-items-center">
						<label class="me-2">フィールドタイプ</label>
						<span class="badge bg-danger">必須</span>
					</div>
					<select name="field_type[]" class="form-control mb-3">
						<option value=1>テキスト</option>
						<option value=2>テキストエリア</option>
						<option value=3>チェックボックス</option>
						<option value=4>ラジオ</option>
						<option value=5>日付</option>
					</select>
				</div>
				<div class="mb-3">
					<label class="me-2 form-label">選択肢 (カンマ区切り)</label>
					<input type="text" name="selection[]" class="form-control">
				</div>
				<div class ="mb-3"><div class = "form-label mt-3 d-flex align-items-center">
				<button type="button" class ="delete_btn btn btn-danger ms-auto me-0">削除</button></div></div><hr>
    		`;

			// 新しいフィールドを追加
			$(this).parent().before(newField);
		});
		$(document).on('click', '.delete_btn', function() {
			$(this).closest('.add_area').remove();
		});
	</script>
</body>

</html>