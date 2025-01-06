<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/EventController.php');
$eventController = new EventController();
$events = $eventController->index();
?>

<body id="event" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
    <div class="wrapper">
        <?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <div class="navbar-collapse collapse">
                    <p class="title ms-4 fs-4 fw-bold mb-0">DM送信</p>
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
                        <div class="card-body p-025">
                            <div class="mb-3">
                                <label class="form-label" for="notyf-message">対象区分</label>
                                <select name="category_id" class="form-control">
                                    <option value=1>全体</option>
                                    <option value=2>イベント</option>
                                    <option value=3>適塾記念会</option>
                                    <option value=4>名誉教授会</option>
                                    <option value=5>同窓会</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="notyf-message">イベント名</label>
                                <select name="category_id" class="form-control">
                                    <option value=1>イベントA</option>
                                    <option value=2>イベントB</option>
                                    <option value=3>イベントC</option>
                                    <option value=4>イベントD</option>
                                    <option value=5>イベントE</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="notyf-message">フリーワード</label>
                                <input id="notyf-message" name="notyf-message" type="text" class="form-control" placeholder="田中 翔太">
                            </div>
                            <!-- <hr> -->
                            <div class="d-flex w-100">
                                <button class="btn btn-primary mb-3 me-0 ms-auto">検索</button>
                            </div>
                        </div>
                    </div>
                    <div class="card min-70vh">
                        <div class="card-body p-0">
                            <div class="d-flex w-100 align-items-center justify-content-end mt-3">
                                <button class="btn btn-primary mt-3 mb-3 d-flex justify-content-center align-items-center">
                                    <i class="align-middle me-1 mt-01" data-feather="send"></i>送信
                                </button>
                            </div>
                            <div class="card m-auto mb-5 w-95">
                                <table class="table table-responsive table-striped table_list" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th class="ps-4 pe-4">ID</th>
                                            <th class="ps-4 pe-4">ユーザー名</th>
                                            <th class="ps-4 pe-4">メールアドレス</th>
                                            <th class="ps-4 pe-4">メニュー</th>
                                            <th class="ps-4 pe-4">決済方法</th>
                                            <th class="ps-4 pe-4">決済状況</th>
                                            <th class="ps-4 pe-4">登録日</th>
                                            <th class="ps-4 pe-4">申込日</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-4 pe-4">1</td>
                                            <td class="ps-4 pe-4">田中 翔太</td>
                                            <td class="ps-4 pe-4">tanaka@gmail.com</td>
                                            <td class="ps-4 pe-4">普通会員</td>
                                            <td class="ps-4 pe-4">クレジット</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">2</td>
                                            <td class="ps-4 pe-4">山田 健太</td>
                                            <td class="ps-4 pe-4">yamada@gmail.com</td>
                                            <td class="ps-4 pe-4">賛助会員</td>
                                            <td class="ps-4 pe-4">口座振替</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/6/5</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">3</td>
                                            <td class="ps-4 pe-4">中村 優衣</td>
                                            <td class="ps-4 pe-4">nakamura@gmail.com</td>
                                            <td class="ps-4 pe-4">賛助会員</td>
                                            <td class="ps-4 pe-4">クレジット</td>
                                            <td class="ps-4 pe-4">未決済</td>
                                            <td class="ps-4 pe-4">2021/10/21</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">4</td>
                                            <td class="ps-4 pe-4">佐藤 夢</td>
                                            <td class="ps-4 pe-4">sato@gmail.com</td>
                                            <td class="ps-4 pe-4">普通会員</td>
                                            <td class="ps-4 pe-4">口座振替</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/4/1</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">5</td>
                                            <td class="ps-4 pe-4">高橋 美咲</td>
                                            <td class="ps-4 pe-4">takahashi@gmail.com</td>
                                            <td class="ps-4 pe-4">普通会員</td>
                                            <td class="ps-4 pe-4">口座振替</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/4/1</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">6</td>
                                            <td class="ps-4 pe-4">伊藤 大輔</td>
                                            <td class="ps-4 pe-4">ito@gmail.com</td>
                                            <td class="ps-4 pe-4">普通会員</td>
                                            <td class="ps-4 pe-4">コンビニ決済</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/4/1</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">7</td>
                                            <td class="ps-4 pe-4">清水 由佳</td>
                                            <td class="ps-4 pe-4">shimizu@gmail.com</td>
                                            <td class="ps-4 pe-4">賛助会員</td>
                                            <td class="ps-4 pe-4">コンビニ決済</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/4/1</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">8</td>
                                            <td class="ps-4 pe-4">加藤 拓也</td>
                                            <td class="ps-4 pe-4">kato@gmail.com</td>
                                            <td class="ps-4 pe-4">賛助会員</td>
                                            <td class="ps-4 pe-4">クレジット</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/4/1</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
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