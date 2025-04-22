<?php 
require_once('/var/www/html/moodle/config.php');
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/survey/survey_custom_controller.php');
$surveyCustomController = new SurveyCustomController();
$id = isset($_GET['id']) ? $_GET['id'] : null;
$customs = $surveyCustomController->edit($id);
$details = isset($customs['detail']) ? $customs['detail'] : [];
$detail_count = count($details);
$answer = $customs['answer'];

// session
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
$count = $_SESSION['count'] ?? ($detail_count > 0 ? count($customs['detail']) : 1);
unset($_SESSION['errors'], $_SESSION['old_input'], $_SESSION['count']);
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
					<p class="header-title title ms-4 fs-4 fw-bold mb-0">アンケートカスタムフィールド登録</p>
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
								<form id="form" method="POST" action="/custom/admin/app/Controllers/survey/survey_custom_upsert_controller.php">
									<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
									<input type="hidden" name="id" value="<?= htmlspecialchars($id ?? '') ?>">
										<div class="field-container">
											<div class="mb-4">
												<div class="form-label d-flex align-items-center">
													<label class="me-2">カテゴリ区分名</label>
													<span class="badge bg-danger">必須</span>
												</div>
												<input type="text" name="name" class="form-control"
													value="<?= htmlspecialchars(isSetValue($customs['name'] ?? '', ($old_input['name'] ?? ''))) ?>">
												<?php if (!empty($errors['name'])): ?>
													<div class=" text-danger mt-2"><?= htmlspecialchars($errors['name']); ?></div>
												<?php endif; ?>
											</div>
											<?php for ($i = 0; $i < $count; $i++) { ?>
											<input type="hidden" name="event_survey_customfield_id[]" value="">
											<div class="mb-3">
												<div class="form-label d-flex align-items-center">
													<label class="me-2">項目名</label>
													<span class="badge bg-danger">必須</span>
												</div>
												<input type="text" name="item_name[]"
													class="form-control <?php if ($i < $detail_count) { ?>readonly readonly-select <?php } ?>"
													value="<?= htmlspecialchars(isSetValue(isset($details[$i]) ? $details[$i]['name'] : '', ($old_input['item_name'][$i] ?? ''))) ?>">
											</div>
											<div class="mb-3">
												<div class="form-label d-flex align-items-center">
													<label class="me-2">表示順</label>
													<span class="badge bg-danger">必須</span>
												</div>
												<input type="number" name="sort[]" class="form-control"
													value=<?= htmlspecialchars(isSetValue($details[$i]['sort'] ?? '', ($old_input['sort'][$i] ?? ''))) ?> >
												<?php if (isset($errors['sort[' . $i . ']'])): ?>
													<div class="text-danger mt-2"><?= htmlspecialchars($errors['sort[' . $i . ']']) ?></div>
												<?php endif; ?>
											</div>
											<div class="mb-3">
												<div class="form-label d-flex align-items-center">
													<label class="me-2">フィールドタイプ</label>
													<span class="badge bg-danger">必須</span>
												</div>
												<select name="field_type[]" class="form-control mb-3 <?php if (!empty($id)) { ?>readonly-select<?php } ?>">
													<?php foreach ($customfield_select_list as $index => $customfield_select) { ?>
														<option value=<?= htmlspecialchars($index) ?> <?= isSelected($index, $details[$i]['field_type'] ?? null, null) ? 'selected' : '' ?>>
															<?= htmlspecialchars($customfield_select) ?>
														</option>
													<?php } ?>
												</select>
											</div>
											<div class="mb-5">
												<label class="form-label ">選択肢 (カンマ区切り)</label>
												<input type="text" name="selection[]" class="form-control <?php if (!empty($id)) { ?>readonly-select<?php } ?>"
													value="<?= htmlspecialchars(isSetValue($details[$i]['selection'] ?? '', ($old_input['selection'][$i] ?? ''))) ?>">
											</div>
											<?php if(!$answer) { ?>
											<div class="mb-3 <?= ($i > 0) ? 'd-block' : 'd-none' ?>">
												<div class="form-label mt-3 d-flex align-items-center">
													<button type="button" class="delete_btn btn btn-danger ms-auto me-0">削除</button>
												</div>
											</div>
											<?php } ?>
											<hr>
											<?php } ?>
										</div>
									<?php if(!$answer) { ?>
										<div class="d-flex">
											<button type="button" id="add_btn" class=" btn btn-primary ms-auto" onclick="addField()">追加</button>
										</div>
									<?php } ?>
									<button id="submit" type="submit" class="mt-5 btn btn-primary ms-auto">登録</button>
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
			$(this).parents('.field-container').remove();
		});

		// フィールド追加
		$("#add_btn").on("click", function() {
			const newField = document.createElement('div');
			newField.classList.add('field-container', 'mt-5');
			// 最後余裕があればtemplateで記載してcloneNodeしたい
			newField.innerHTML = ` 
			    <div class="add_area">
				<input type="hidden" name="event_survey_customfield_id[]">
				<div class="mb-3">
					<div class="form-label d-flex align-items-center">
						<label class="me-2">項目名</label>
						<span class="badge bg-danger">必須</span>
					</div>
					<input type="text" name="item_name[]" class="form-control">
				</div>
				<div class="mb-3">
					<div class="form-label d-flex align-items-center">
						<label class="me-2">表示順</label>
						<span class="badge bg-danger">必須</span>
					</div>
					<input type="number" name="sort[]" min="1" max="999"class="form-control">
				</div>
				<div class="mb-3">
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
		// 登録ボタン押下時
		$("#form").on("submit", function(e) {
			let isValid = true;
			let values = {
				"item_name[]": {},
				"field_name[]": {},
				"sort[]": {}
			};
			$(".error-message").remove();

			// 必須項目バリデーション
			$(".field-container").each(function() {
				$(this).find("input[type='text'], input[type='number'], select").each(function() {
					let $input = $(this);
					const value = $input.val().trim();
					const nameAttr = $input.attr("name");

					// チェックボックスまたはラジオ選択時は選択肢を必須項目に
					if (nameAttr === "selection[]") {
						const fieldType = $(this).closest('div').prev().find('select').val();
						if ((fieldType === "3" || fieldType === "4") && value === "") {
							let label = $(this).closest('div').find('label').text().trim();
							let errorMsg = `<div class='text-danger mt-2 error-message'>${label}は必須です</div>`;
							$input.after(errorMsg);
							isValid = false;
						}
					}

					// 他項目必須チェック
					if (nameAttr !== "selection[]" && nameAttr !== "field_type[]") {
						let label = $(this).closest('.mb-3').find('label').text().trim();
						if (nameAttr === "name") {
							label = $(this).closest('.mb-4').find('label').text().trim();
						}
						if (value === "") {
							let errorMsg = `<div class='text-danger mt-2 error-message'>${label}は必須です</div>`;
							$input.after(errorMsg);
							isValid = false;
						}
					}

					// 同一項目名のみで重複チェック
					if (values[nameAttr] !== undefined) {
						if (value !== "" && values[nameAttr][value]) {
							let label = $(this).closest('.mb-3').find('label').text().trim();
							let errorMsg = `<div class='text-danger mt-2 error-message'>${label}が重複しています</div>`;
							$input.after(errorMsg);
							isValid = false;
						} else if (value !== "") {
							values[nameAttr][value] = true;
						}
					}

					// 文字数制限
					if (nameAttr === "item_name[]" || nameAttr === "name") {
						if (value.length > 500) {
							let label = $(this).closest('.mb-3').find('label').text().trim();
							if (nameAttr === "name") {
								label = $(this).closest('.mb-4').find('label').text().trim();
							}
							let errorMsg = `<div class='text-danger mt-2 error-message'>${label}は500文字以内で入力してください</div>`;
							$input.after(errorMsg);
							isValid = false;
						}
					}
					if (nameAttr === "field_name[]") {
						if (value.length > 100) {
							let label = $(this).closest('.mb-3').find('label').text().trim();
							let errorMsg = `<div class='text-danger mt-2 error-message'>${label}は100文字以内で入力してください</div>`;
							$input.after(errorMsg);
							isValid = false;
						}
					}
				});
			});
			// フォーム送信を制御
			if (isValid) {
				$('#form').submit();
			} else {
				e.preventDefault();
				return false;
			}
		});
	</script>
</body>

</html>