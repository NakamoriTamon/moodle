<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php'); 
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/survey/survey_controller.php');

$event_statuses = DISPLAY_EVENT_STATUS_LIST;
$old_input = $_SESSION['old_input'] ?? [];
?>


<body id="survey" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative d-block">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<a class="sidebar-toggle js-sidebar-toggle">
					<i class="hamburger align-self-center"></i>
				</a>
				<div class="navbar-collapse collapse">
					<p class="header-title title ms-4 fs-4 fw-bold mb-0">アンケート集計</p>
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
				<div class="card">
					<div class="card-body p-055 p-025">
						<form method="POST" action="/custom/admin/app/Controllers/survey/survey_controller.php">
							<input type="hidden" name="action" value="index">
							<div class="sp-block d-flex justify-content-between">
								<div class="mb-3 w-100">
									<label class="form-label" for="notyf-message">カテゴリー</label>
									<select name="category_id" class="form-control" id="category-select">
										<option value="">すべて</option>
										<?php foreach ($categorys as $category): ?>
											<option value="<?= htmlspecialchars($category['id']) ?>"
												<?= isset($old_input['category_id']) && $category['id'] == $old_input['category_id'] ? 'selected' : '' ?>>
												<?= htmlspecialchars($category['name']) ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="sp-ms-0 ms-3 mb-3 w-100">
									<label class="form-label" for="notyf-message">開催ステータス</label>
									<select name="event_status" class="form-control" id="status-select">
										<option value="">すべて</option>
										<?php foreach ($event_statuses as $id => $name): ?>
											<option value="<?= htmlspecialchars($id) ?>"
												<?= isset($old_input['event_status']) && $id == $old_input['event_status'] ? 'selected' : '' ?>>
												<?= htmlspecialchars($name) ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<div class="sp-block d-flex justify-content-between">
								<div class="mb-3 w-100">
									<label class="form-label" for="notyf-message">イベント名</label>
									<select name="event_id" class="form-control" id="event-select">
										<option value="">すべて</option>
										<?php if (isset($events) && !empty($events)): ?>
											<?php foreach ($events as $event): ?>
												<option value="<?= htmlspecialchars($event['id']) ?>"
													<?= isset($old_input['event_id']) && $event['id'] == $old_input['event_id'] ? 'selected' : '' ?>>
													<?= htmlspecialchars($event['name']) ?>
												</option>
											<?php endforeach; ?>
										<?php endif; ?>
									</select>
								</div>
								<div class="sp-ms-0 ms-3 mb-3 w-100">
									<label class="form-label" for="notyf-message">回数</label>
									<select name="event_count" class="form-control" id="count-select">
										<option value="">すべて</option>
										<?php if (isset($event_counts) && !empty($event_counts)): ?>
											<?php foreach ($event_counts as $count): ?>
												<option value="<?= htmlspecialchars($count['id']) ?>"
													<?= isset($old_input['event_count']) && $count['id'] == $old_input['event_count'] ? 'selected' : '' ?>>
													<?= htmlspecialchars($count['no']) ?>回目
												</option>
											<?php endforeach; ?>
										<?php endif; ?>
									</select>
								</div>
							</div>
							<!-- <hr> -->
							<div class="d-flex w-100">
								<!-- 検索ボタンを廃止 -->
							</div>
						</form>
					</div>
				</div>
				<div class="search-area col-12 col-lg-12">
					<div class="card">
						<div class="card-body p-0">
							<div class="d-flex w-100 mt-3 align-items-center justify-content-end">
								<div></div>
								<!-- 非表示のform -->
								<form id="csvExportForm" method="POST" action="/custom/admin/app/Controllers/survey/survey_export_controller.php">
									<input type="hidden" name="category_id" value="<?= $old_input['category_id'] ?? '' ?>">
									<input type="hidden" name="event_status_id" value="<?= $old_input['event_status_id'] ?? '' ?>">
									<input type="hidden" name="event_id" value="<?= $old_input['event_id'] ?? '' ?>">
									<input type="hidden" name="event_count" value="<?= $old_input['event_count'] ?? '' ?>">
								</form>
								<!-- 元のデザインのボタン -->
								<button class="btn btn-primary ms-auto mt-3 mb-3 mr-025 d-flex justify-content-center align-items-center" onclick="document.getElementById('csvExportForm').submit()">
									<i class="align-middle me-1" data-feather="download"></i>CSV出力
								</button>
								<div class="btn mt-3 mb-3 mr-025 ms-auto fw-bold">総件数 : <?= $totalCount ?>件</div>
							</div>
							<div class="card m-auto mb-5 w-95">
								<table class="table table-responsive table-striped table_list text-break">
									<thead>
										<tr>
											<th class="w-25 p-4">回答時間</th>
											<th class="w-25 p-4">本日の講義内容について、ご意見・ご感想をお書きください</th>
											<th class="w-25 p-4">今までに大阪大学公開講座のプログラムに参加されたことはありますか </th>
											<th class="w-25 p-4">本日のプログラムをどのようにしてお知りになりましたか</th>
											<th class="w-25 p-4">その他 </th>
											<th class="w-25 p-4">本日のテーマを受講した理由は何ですか </th>
											<th class="w-25 p-4">その他</th>
											<th class="w-25 p-4">本日のプログラムの満足度について、あてはまるもの1つをお選びください</th>
											<th class="w-25 p-4">本日のプログラムの理解度について、あてはまるもの1つをお選びください </th>
											<th class="w-25 p-4">
												本日のプログラムで特に良かった点について教えてください。いかに当てはまるもの
												があれば、1つお選びください。あてはまるものがなければ「その他」の欄に記述し
												てください
											</th>
											<th class="w-25 p-4">その他</th>
											<th class="w-25 p-4">本日のプログラムの開催時間(90分)についてあてはまるものを1つお選びください </th>
											<th class="w-25 p-4">
												本日のプログラムの開催環境について、あてはまるものを１つお選びください。
												※「あまり快適ではなかった」「全く快適ではなかった」と回答された方は次の
												質問にその理由を教えてください
											</th>
											<th class="w-25 p-4">「あまり快適ではなかった」「全く快適ではなかった」と回答された方はその理由を教えてください。</th>
											<th class="w-25 p-4">今後の大阪大学公開講座で、希望するジャンルやテーマ、話題があれば、ご提案ください</th>
											<th class="w-25 p-4">話を聞いてみたい大阪大学の教員や研究者がいれば、具体的にご提案ください</th>
											<th class="w-25 p-4">ご職業等を教えてください</th>
											<th class="w-25 p-4">性別をご回答ください</th>
											<th class="w-25 p-4">お住いの地域を教えてください（〇〇県△△市のようにご回答ください</th>
										</tr>
									</thead>
									<tbody>
										<?php if (isset($surveyApplications) && !empty($surveyApplications)): ?>
											<?php foreach ($surveyApplications as $key => $surveyApplication): ?>
												<tr>
													<td class="p-4">2024/12/26 10:00</td>
													<td class="p-4"><?= htmlspecialchars($surveyApplication['thoughts']) ?></td>
													<td class="p-4">
														<?php if ($surveyApplication['attend'] == 1): ?>
															はい
														<?php else: ?>
															いいえ
														<?php endif; ?>
													</td>
													<td class="p-4">
														<?php if ($surveyApplication['found_method'] == 1): ?>
															チラシ
														<?php elseif ($surveyApplication['found_method'] == 2): ?>
															ウェブサイト
														<?php elseif ($surveyApplication['found_method'] == 3): ?>
															大阪大学公開講座「知の広場」からのメール
														<?php elseif ($surveyApplication['found_method'] == 4): ?>
															SNS（X, Instagram, Facebookなど）
														<?php elseif ($surveyApplication['found_method'] == 5): ?>
															21世紀懐徳堂からのメールマガジン
														<?php elseif ($surveyApplication['found_method'] == 6): ?>
															大阪大学卒業生メールマガジン
														<?php elseif ($surveyApplication['found_method'] == 7): ?>
															大阪大学入試課からのメール
														<?php elseif ($surveyApplication['found_method'] == 8): ?>
															Peatixからのメール
														<?php elseif ($surveyApplication['found_method'] == 9): ?>
															知人からの紹介
														<?php elseif ($surveyApplication['found_method'] == 10): ?>
															講師・スタッフからの紹介
														<?php elseif ($surveyApplication['found_method'] == 11): ?>
															自治体の広報・掲示
														<?php elseif ($surveyApplication['found_method'] == 12): ?>
															スマートニュース広告
														<?php endif; ?>
													</td>
													<td class="p-4">
														<?= htmlspecialchars($surveyApplication['other_found_method']) ?>
													</td>
													<td class="p-4">
														<?php if ($surveyApplication['reason'] == 1): ?>
															テーマに関心があったから
														<?php elseif ($surveyApplication['reason'] == 2): ?>
															本日のプログラム内容に関心があったから
														<?php elseif ($surveyApplication['reason'] == 3): ?>
															本日のゲストに関心があったから
														<?php elseif ($surveyApplication['reason'] == 4): ?>
															大阪大学のプログラムに参加したかったから
														<?php elseif ($surveyApplication['reason'] == 5): ?>
															教養を高めたいから
														<?php elseif ($surveyApplication['reason'] == 6): ?>
															仕事に役立つと思われたから
														<?php elseif ($surveyApplication['reason'] == 7): ?>
															日常生活に役立つと思われたから
														<?php elseif ($surveyApplication['reason'] == 8): ?>
															余暇を有効に利用したかったから
														<?php endif; ?>
													</td>
													<td class="p-4"><?= htmlspecialchars($surveyApplication['other_reason']) ?></td>
													<td class="p-4">
														<?php if ($surveyApplication['satisfaction'] == 1): ?>
															非常に満足
														<?php elseif ($surveyApplication['satisfaction'] == 2): ?>
															満足
														<?php elseif ($surveyApplication['satisfaction'] == 3): ?>
															ふつう
														<?php elseif ($surveyApplication['satisfaction'] == 4): ?>
															不満
														<?php elseif ($surveyApplication['satisfaction'] == 5): ?>
															非常に不満
														<?php endif; ?>
													</td>
													<td class="p-4">
														<?php if ($surveyApplication['understanding'] == 1): ?>
															よく理解できた
														<?php elseif ($surveyApplication['understanding'] == 2): ?>
															理解できた
														<?php elseif ($surveyApplication['understanding'] == 3): ?>
															ふつう
														<?php elseif ($surveyApplication['understanding'] == 4): ?>
															理解できなかった
														<?php elseif ($surveyApplication['understanding'] == 5): ?>
															全く理解できなかった
														<?php endif; ?>
													</td>
													<td class="p-4">
														<?php if ($surveyApplication['good_point'] == 1): ?>
															テーマについて考えを深めることができた
														<?php elseif ($surveyApplication['good_point'] == 2): ?>
															最先端の研究について学べた
														<?php elseif ($surveyApplication['good_point'] == 3): ?>
															大学の研究者と対話ができた
														<?php elseif ($surveyApplication['good_point'] == 4): ?>
															大学の講義の雰囲気を味わえた
														<?php elseif ($surveyApplication['good_point'] == 5): ?>
															大阪大学について知ることができた
														<?php elseif ($surveyApplication['good_point'] == 6): ?>
															身の周りの社会課題に対する解決のヒントが得られた
														<?php endif; ?>
													</td>
													<td class="p-4"><?= htmlspecialchars($surveyApplication['other_good_point']) ?></td>
													<td class="p-4">
														<?php if ($surveyApplication['time'] == 1): ?>
															適当である
														<?php elseif ($surveyApplication['time'] == 2): ?>
															長すぎる
														<?php elseif ($surveyApplication['time'] == 3): ?>
															短すぎる
														<?php endif; ?>
													</td>
													<td class="p-4">
														<?php if ($surveyApplication['holding_environment'] == 1): ?>
															とても快適だった
														<?php elseif ($surveyApplication['holding_environment'] == 2): ?>
															快適だった
														<?php elseif ($surveyApplication['holding_environment'] == 3): ?>
															ふつう
														<?php elseif ($surveyApplication['holding_environment'] == 4): ?>
															あまり快適ではなかった
														<?php elseif ($surveyApplication['holding_environment'] == 5): ?>
															全く快適ではなかった
														<?php endif; ?>
													</td>
													<td class="p-4"><?= htmlspecialchars($surveyApplication['no_good_enviroment_reason']) ?></td>
													<td class="p-4"><?= htmlspecialchars($surveyApplication['lecture_suggestions']) ?></td>
													<td class="p-4"><?= htmlspecialchars($surveyApplication['speaker_suggestions']) ?></td>
													<td class="p-4">
														<?php if ($surveyApplication['work'] == 1): ?>
															高校生以下
														<?php elseif ($surveyApplication['work'] == 2): ?>
															学生（高校生、大学生、大学院生等）
														<?php elseif ($surveyApplication['work'] == 3): ?>
															会社員
														<?php elseif ($surveyApplication['work'] == 4): ?>
															自営業・フリーランス
														<?php elseif ($surveyApplication['work'] == 5): ?>
															公務員
														<?php elseif ($surveyApplication['work'] == 6): ?>
															教職員
														<?php elseif ($surveyApplication['work'] == 7): ?>
															パート・アルバイト
														<?php elseif ($surveyApplication['work'] == 8): ?>
															主婦・主夫
														<?php elseif ($surveyApplication['work'] == 9): ?>
															定年退職
														<?php elseif ($surveyApplication['work'] == 10): ?>
															その他
														<?php endif; ?>
													</td>
													<td class="p-4">
														<?php if ($surveyApplication['sex'] == 1): ?>
															男性
														<?php elseif ($surveyApplication['sex'] == 2): ?>
															女性
														<?php elseif ($surveyApplication['sex'] == 3): ?>
															その他
														<?php endif; ?>
													</td>
													<td class="p-4">
														<?php
														if (!empty($surveyApplication['prefectures'])) {
															echo htmlspecialchars($prefectures[$surveyApplication['prefectures']] ?? $surveyApplication['prefectures']);
														}
														?>
														<?= htmlspecialchars($surveyApplication['address']) ?>
													</td>
												</tr>
											<?php endforeach; ?>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<div class="d-flex">
					<div class="dataTables_paginate paging_simple_numbers ms-auto mr-025" id="datatables-buttons_paginate">
						<ul class="pagination">
							<?php if ($currentPage >= 1 && $totalCount > $perPage): ?>
								<li class="paginate_button page-item previous" id="datatables-buttons_previous"><a href="?page=<?= intval($currentPage) - 1 ?>&<?= $queryString ?>" aria-controls="datatables-buttons" class="page-link">Previous</a></li>
							<?php endif; ?>
							<?php for ($i = 1; $i <= ceil($totalCount / $perPage); $i++): ?>
								<li class="paginate_button page-item <?= $i == $currentPage ? 'active' : '' ?>"><a href="?page=<?= $i ?>&<?= $queryString ?>" aria-controls="datatables-buttons" class="page-link"><?= $i ?></a></li>
							<?php endfor; ?>
							<?php if ($currentPage >= 0 && $totalCount > $perPage): ?>
								<li class="paginate_button page-item next" id="datatables-buttons_next"><a href="?page=<?= intval($currentPage) + 1 ?>&<?= $queryString ?>" aria-controls="datatables-buttons" class="page-link">Next</a></li>
							<?php endif; ?>
						</ul>
					</div>
				</div>
			</main>
		</div>
	</div>

	<script src="/custom/admin/public/js/app.js"></script>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const categorySelect = document.getElementById('category-select');
			const statusSelect = document.getElementById('status-select');
			const eventSelect = document.getElementById('event-select');
			const countSelect = document.getElementById('count-select');
			const searchForm = document.querySelector('form');
			
			// カテゴリーまたは開催ステータスが変更されたときのイベントリスナー
			categorySelect.addEventListener('change', updateEventOptions);
			statusSelect.addEventListener('change', updateEventOptions);
			
			// イベント名が変更されたときのイベントリスナー
			eventSelect.addEventListener('change', updateCountOptions);
			
			// 回数が変更されたときに自動的にフォームをサブミット
			countSelect.addEventListener('change', function() {
				searchForm.submit();
			});
			
			function updateEventOptions() {
				const categoryId = categorySelect.value;
				const eventStatus = statusSelect.value;
				
				// 選択された値をもとにAjaxリクエストを送信
				fetch('/custom/admin/app/Controllers/survey/survey_controller.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: `ajax=get_filtered_events&category_id=${categoryId}&event_status=${eventStatus}`
				})
				.then(response => response.json())
				.then(data => {
					// イベント選択肢を更新
					updateSelectOptions(eventSelect, data);
					
					// イベントが変更されたので回数も更新
					countSelect.innerHTML = '<option value="">すべて</option>';
					
					// フォームを送信して結果を更新
					searchForm.submit();
				})
				.catch(() => {
					// エラー時は空のオプションを設定して送信
					updateSelectOptions(eventSelect, []);
					countSelect.innerHTML = '<option value="">すべて</option>';
					searchForm.submit();
				});
			}
			
			function updateCountOptions() {
				const eventId = eventSelect.value;
				
				// イベントが選択されていない場合、回数をリセットして検索
				if (!eventId) {
					countSelect.innerHTML = '<option value="">すべて</option>';
					searchForm.submit();
					return;
				}
				
				// 選択されたイベントIDに基づいて回数を取得
				fetch('/custom/admin/app/Controllers/survey/survey_controller.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: `ajax=get_event_counts&event_id=${eventId}`
				})
				.then(response => response.json())
				.then(data => {
					// 回数選択肢を更新（「回目」を付けて表示）
					updateSelectOptions(countSelect, data, item => `${item.no}回目`);
					
					// フォームを送信して結果を更新
					searchForm.submit();
				})
				.catch(() => {
					// エラー時は空のオプションを設定して送信
					countSelect.innerHTML = '<option value="">すべて</option>';
					searchForm.submit();
				});
			}
			
			// select要素のオプションを更新するヘルパー関数
			function updateSelectOptions(selectElement, data, textFormatter = null) {
				selectElement.innerHTML = '<option value="">すべて</option>';
				
				if (data && data.length > 0) {
					data.forEach(item => {
						const option = document.createElement('option');
						option.value = item.id;
						option.textContent = textFormatter ? textFormatter(item) : item.name;
						selectElement.appendChild(option);
					});
				}
			}
		});
	</script>
</body>

</html>