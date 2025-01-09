<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/event_controller.php');
$eventController = new EventController();
$events = $eventController->index();
?>

<body id="survey" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative d-block">
	<div class="wrapper">
		<?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<div class="navbar-collapse collapse">
					<p class="title ms-4 fs-4 fw-bold mb-0">アンケート集計</p>
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
							<div class="d-flex w-100 mt-3 align-items-center justify-content-end">
								<select name="category_id" class="form-control w-25 search-select">
									<option value="1">イベントA</option>
									<option value="2">イベントB</option>
									<option value="3">イベントC</option>
								</select>
								<button class="btn btn-primary ms-auto mt-3 mb-3  mr-025 d-flex justify-content-center align-items-center">
									<i class="align-middle me-1" data-feather="download"></i>CSV出力
								</button>
								<!-- <div class="btn mt-3 mb-3 mr-025 ms-auto fw-bold">総件数 : 3件</div> -->
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
											<th class="w-25 p-4">今後の大阪大学公開講座で、希望するジャンルやテーマ、話題があれば、ご提案ください</th>
											<th class="w-25 p-4">話を聞いてみたい大阪大学の教員や研究者がいれば、具体的にご提案ください</th>
											<th class="w-25 p-4">ご職業等を教えてください</th>
											<th class="w-25 p-4">性別をご回答ください</th>
											<th class="w-25 p-4">お住いの地域を教えてください（〇〇県△△市のようにご回答ください</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td class="p-4">2024/12/26 10:00</td>
											<td class="p-4">とても分かりやすかったです。</td>
											<td class="p-4">ある</td>
											<td class="p-4">ウェブサイト</td>
											<td class="p-4">大阪大学公式ホームページ</td>
											<td class="p-4">テーマに関心があったから</td>
											<td class="p-4"></td>
											<td class="p-4">非常に満足</td>
											<td class="p-4">よく理解できた</td>
											<td class="p-4">大学の研究者と対話ができた</td>
											<td class="p-4"></td>
											<td class="p-4">適当である</td>
											<td class="p-4">とても快適だった</td>
											<td class="p-4">植物形態学</td>
											<td class="p-4">古谷朋之准教授</td>
											<td class="p-4">会社員</td>
											<td class="p-4">男性</td>
											<td class="p-4">愛知県名古屋市</td>
										</tr>
										<tr>
											<td class="p-4">2024/12/26 11:30</td>
											<td class="p-4">知識を深めることができました。</td>
											<td class="p-4">ある</td>
											<td class="p-4">SNS(X,Instagram,Facebookなど)</td>
											<td class="p-4"></td>
											<td class="p-4">本日のプログラム内容に関心があったから</td>
											<td class="p-4"></td>
											<td class="p-4">非常に満足</td>
											<td class="p-4">よく理解できた</td>
											<td class="p-4">大学の研究者と対話ができた</td>
											<td class="p-4"></td>
											<td class="p-4">適当である</td>
											<td class="p-4">とても快適だった</td>
											<td class="p-4"></td>
											<td class="p-4"></td>
											<td class="p-4">自営業</td>
											<td class="p-4">女性</td>
											<td class="p-4">三重県津市</td>
										</tr>
										<tr>
											<td class="p-4">2024/12/27 16:35</td>
											<td class="p-4">とても楽しく知識を付けることができました。また受講したいです！</td>
											<td class="p-4">ある</td>
											<td class="p-4">SNS(X,Instagram,Facebookなど)</td>
											<td class="p-4"></td>
											<td class="p-4">本日のゲストに関心があったから, 余暇を有効に利用したかったから</td>
											<td class="p-4"></td>
											<td class="p-4">非常に満足</td>
											<td class="p-4">よく理解できた</td>
											<td class="p-4">大学の講義の雰囲気を味わえた</td>
											<td class="p-4"></td>
											<td class="p-4">適当である</td>
											<td class="p-4">とても快適だった</td>
											<td class="p-4">生物科学科</td>
											<td class="p-4">古谷朋之准教授</td>
											<td class="p-4">学生</td>
											<td class="p-4">男性</td>
											<td class="p-4">東京都八王子市</td>
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