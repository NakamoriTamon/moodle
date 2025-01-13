<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php'); ?>

<body id="event" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<div class="navbar-collapse collapse">
					<p class="title ms-4 fs-4 fw-bold mb-0">イベント登録</p>
					<ul class="navbar-nav navbar-align">
						<li class="nav-item dropdown">
							<a class="nav-icon pe-md-0 dropdown-toggle" href="#" data-bs-toggle="dropdown">
								<div class="fs-5 me-4">システム管理者</div>
							</a>
							<div class="dropdown-menu dropdown-menu-end">
								<a class="dropdown-item" href="login.php">Log out</a>
							</div>
						</li>
					</ul>
				</div>
			</nav>

			<main class="content">
				<div class="col-12 col-lg-12">
					<div class="card">
						<div class="card-body p-0">
							<p class="content_title p-3">イベント登録</p>
							<div class="form-wrapper">
								<form method="POST" action="/custom/admin/app/Controllers/EventUpsertController.php">
									<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
									<input type="hidden" name="id" value="<?php echo $_GET['id'] ?>">
									<div class=" mb-3">
										<label class="form-label">イベント区分</label>
										<select name="event_kbn" class="form-control mb-3">
											<option value=1 selected>1度きりのイベント</option>
											<option value=2>複数回シリーズのイベント</option>
										</select>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">イベントタイトル</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input type="name" name="name" class="form-control" placeholder="">
									</div>
									<div class="mb-3">
										<label class="form-label">説明文</label>
										<textarea name="description" class=" form-control" rows="5"></textarea>
									</div>
									<div class="mb-3">
										<label class="form-label">カテゴリー</label>
										<select id="category_id" name="category_id" class="form-control choices-multiple mb-3" multiple>
											<optgroup label="">
												<option value=1>未選択</option>
												<option value=2>医療・健康</option>
												<option value=3>科学・技術</option>
												<option value=4>生活・福祉</option>
												<option value=5>文化・芸術</option>
												<option value=6>社会・経済</option>
												<option value=7>自然・環境</option>
												<option value=8>子ども・教育</option>
												<option value=9>国際・言語</option>
												<option value=10>その他</option>
											</optgroup>
										</select>
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">サムネール画像</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input name="thumbnail_img_name" class="form-control" type="file" accept=".png,.jpeg,.jpg">
									</div>
									<div class="mb-3">
										<label class="form-label">講義形式</label>
										<select id="venue_id" class=" form-control choices-multiple mb-3" multiple>
											<optgroup label="">
												<option selected value=1>会場</option>
												<option value=2>会場(オンデマンドあり)</option>
												<option value=3>オンライン</option>
												<option value=4>ハイブリッド</option>
											</optgroup>
										</select>
									</div>
									<div class="mb-3">
										<label class="form-label">会場名</label>
										<input name="venue_name" class=" form-control" type="text">
									</div>
									<div class="mb-3">
										<label class="form-label">対象</label>
										<input name="target" class=" form-control" type="text">
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">開催日</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input name="event_date" class="form-control" type="date">
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">時間</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input name="start_hour" class="timepicker" type="text" placeholder="12:00"> <span class="ps-2 pe-2">～</span>
										<input name="end_hour" class="timepicker" type="text" placeholder="12:00">
									</div>
									<div class="mb-3">
										<label class="form-label">交通アクセス</label>
										<textarea name="access" class=" form-control" rows="5"></textarea>
									</div>
									<div class="mb-3">
										<label class="form-label">Google Map</label>
										<textarea name="google_map" class="form-control" rows="5"></textarea>
									</div>
									<div class="mb-3">
										<label class="form-label">
											<input name="is_top" type="checkbox" checked class="form-check-input">
											<span class="form-check-label">トップに固定する</span>
										</label>
									</div>
									<div class="mb-3 onetime_area">
										<label class="form-label">講師</label>
										<select id="tutor_id" class=" form-control choices-multiple mb-3" name="tutor_id" multiple>
											<optgroup label="">
												<option value=1>海道 尊</option>
												<option value=2>川上 潤</option>
											</optgroup>
										</select>
									</div>
									<div class="repeatedly_area">
										<div class="mb-3">
											<P class="fs-5 fw-bold">第1講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="event_date" class="form-control" type="date">
										</div>
										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_01" class=" form-control choices-multiple mb-3" name="tutor_id" multiple>
												<optgroup label="">
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>

										<div class="mb-3">
											<P class="fs-5 fw-bold">第2講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="event_date" class="form-control" type="date">
										</div>

										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_02" class=" form-control choices-multiple mb-3" name="tutor_id" multiple>
												<optgroup label="">
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<P class="fs-5 fw-bold">第3講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="event_date" class="form-control" type="date">
										</div>

										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_03" class=" form-control choices-multiple mb-3" name="tutor_id" multiple>
												<optgroup label="">
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<P class="fs-5 fw-bold">第4講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="event_date" class="form-control" type="date">
										</div>

										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_04" class=" form-control choices-multiple mb-3" name="tutor_id" multiple>
												<optgroup label="">
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<P class="fs-5 fw-bold">第5講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="event_date" class="form-control" type="date">
										</div>

										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_05" class=" form-control choices-multiple mb-3" name="tutor_id" multiple>
												<optgroup label="">
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<P class="fs-5 fw-bold">第6講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="event_date" class="form-control" type="date">
										</div>

										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_06" class=" form-control choices-multiple mb-3" name="tutor_id" multiple>
												<optgroup label="">
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<P class="fs-5 fw-bold">第7講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="event_date" class="form-control" type="date">
										</div>

										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_07" class=" form-control choices-multiple mb-3" name="tutor_id" multiple>
												<optgroup label="">
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<P class="fs-5 fw-bold">第8講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="event_date" class="form-control" type="date">
										</div>

										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_08" class=" form-control choices-multiple mb-3" name="tutor_id" multiple>
												<optgroup label="">
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>
										<div class="mb-3">
											<P class="fs-5 fw-bold">第9講座</P>
											<div class="form-label d-flex align-items-center">
												<label class="me-2">開催日</label>
												<span class="badge bg-danger">必須</span>
											</div>
											<input name="event_date" class="form-control" type="date">
										</div>

										<div class="mb-3">
											<label class="form-label">講師</label>
											<select id="tutor_id_09" class=" form-control choices-multiple mb-3" name="tutor_id" multiple>
												<optgroup label="">
													<option value=1>海道 尊</option>
													<option value=2>川上 潤</option>
												</optgroup>
											</select>
										</div>

									</div>
									<!-- <div class="mb-3">
										<label class="form-label">講義名</label>
										<input name="lecture_name" class=" form-control" type="text">
									</div>
									<div class="mb-3">
										<label class="form-label">講義概要</label>
										<textarea name="lecture_outline" class="form-control" rows="5"></textarea>
									</div> -->
									<div class="mb-3">
										<label class="form-label">プログラム</label>
										<textarea name="program" class=" form-control" rows="5"></textarea>
									</div>
									<div class="mb-3">
										<label class="form-label">主催</label>
										<input name="sponsor" class=" form-control" type="text">
									</div>
									<div class="mb-3">
										<label class="form-label">共催</label>
										<input name="co_host" class="form-control" type="text">
									</div>
									<div class="mb-3">
										<label class="form-label">後援</label>
										<input name="sponsorship" class="form-control" type="text">
									</div>
									<div class="mb-3">
										<label class="form-label">協力</label>
										<input name="cooperation" class=" form-control" type="text">
									</div>
									<div class="mb-3">
										<label class="form-label">企画</label>
										<input name="plan" class="form-control" type="text">
									</div>
									<div class="mb-3">
										<label class="form-label">定員</label>
										<input name="capacity" class=" form-control" min="0" type="number">
									</div>
									<div class="mb-3">
										<label class="form-label">参加費</label>
										<input name="participation_fee" class=" form-control" min="0" type="number">
									</div>
									<div class="mb-3 repeatedly_area">
										<label class="form-label">参加費( 全て受講 )</label>
										<input id="" name="all_participation_fee" class="form-control" min="0" type="number">
									</div>
									<div class="mb-3">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">申し込み締切日</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input name="deadline" class=" form-control" type="date">
									</div>
									<div class="mb-3 repeatedly_area">
										<div class="form-label d-flex align-items-center">
											<label class="me-2">申し込み締切日( 全て受講 )</label>
											<span class="badge bg-danger">必須</span>
										</div>
										<input name="deadline" class="repeatedly_area form-control" type="date">
									</div>
									<div class="mb-3">
										<label class="form-label">アーカイブ配信期間</label>
										<input name="archive_streaming_period" class=" form-control" min="0" type="number">
									</div>
									<div class="mb-3">
										<label class="form-label">
											<input type="checkbox" checked name="is_double_speed" class="form-check-input">
											<span name="is_double_speed" class=" form-check-label">動画倍速機能</span>
										</label>
									</div>
									<div class="mb-3">
										<label class="form-label">イベントカスタム区分</label>
										<select id="event_custom_id" class=" form-control mb-3" name="event_custom_id">
											<option value=1>イベント一般</option>
											<option value=2>適塾記念会イベント</option>
											<option value=3>生命科学分野イベント</option>
										</select>
									</div>
									<div class="mb-3">
										<label class="form-label">アンケートカスタム区分</label>
										<select id="survey_custom_id" class=" form-control  mb-3" name="survey_custom_id">
											<option value="">未選択</option>
											<option value=1>イベント一般</option>
											<option value=2>適塾記念会イベント</option>
											<option value=3>生命科学分野イベント</option>
										</select>
									</div>
									<div class="mb-3">
										<label class="form-label">その他</label>
										<textarea name="note" class="form-control" rows="5"></textarea>
									</div>

									<button type="submit" class="btn btn-primary">登録</button>
								</form>
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
	document.addEventListener('DOMContentLoaded', () => {
		const ids = ['venue_id', 'tutor_id', 'tutor_id_01', 'tutor_id_02', 'tutor_id_03', 'tutor_id_04', 'tutor_id_05',
			'tutor_id_06', 'tutor_id_07', 'tutor_id_08', 'tutor_id_09', 'category_id'
		];
		ids.forEach((id) => {
			const element = document.getElementById(id);
			if (element) {
				new Choices(element, {
					shouldSort: false,
					shouldSortItems: false,
					removeItemButton: true,
				});
			}
		});
	});
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
	});
</script>