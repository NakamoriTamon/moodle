<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php'); ?>

<body id="event" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
    <div class="wrapper">
        <?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>
                <div class="navbar-collapse collapse">
                    <p class="title header-title ms-4 fs-4 fw-bold mb-0">費用請求</p>
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
                        <div class="card-body p-025 p-055">
                            <div class="d-flex sp-block justify-content-between">
                                <div class="mb-3 w-100">
                                    <label class="form-label" for="notyf-message">会名</label>
                                    <select name="category_id" class="form-control">
                                        <option value=1>適塾記念会</option>
                                        <!-- <option value=2>名誉教授会</option>
                                        <option value=3>同窓会</option> -->
                                    </select>
                                </div>
                                <div class="ms-3 sp-ms-0 mb-3 w-100">
                                    <label class="form-label" for="notyf-message">支払年度</label>
                                    <select name="category_id" class="form-control">
                                        <option value=1>2025年</option>
                                        <option value=1>2026年</option>
                                        <option value=2>2027年</option>
                                        <option value=3>2028年</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex sp-block justify-content-between">
                                <div class="mb-4 sp-ms-0 w-100">
                                    <label class="form-label" for="notyf-message">フリーワード</label>
                                    <input id="notyf-message" name="notyf-message" type="text" class="form-control" placeholder="田中 翔太">
                                </div>
                                <div class="w-100"></div>
                            </div>
                            <!-- <hr> -->
                            <div class="d-flex w-100">
                                <button id="search-button" class="btn btn-primary mb-3 me-0 ms-auto">検索</button>
                            </div>
                        </div>
                    </div>
                    <div class="card min-70vh search-area">
                        <div class="card-body p-0">
                            <div class="d-flex w-100 align-items-center justify-content-end mt-3 mb-3">
                                <div></div>
                                <div class="d-flex ms-auto  button-div mr-025">
                                    <button class="btn btn-primary mt-3 d-flex justify-content-center align-items-center">
                                        <i class="align-middle me-1" data-feather="download"></i>CSV出力
                                    </button>
                                </div>
                            </div>
                            <div class="card m-auto mb-5 w-95">
                                <table class="table table-responsive table-striped table_list" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th class="ps-4 pe-4">会員ID</th>
                                            <th class="ps-4 pe-4">ユーザー名</th>
                                            <th class="ps-4 pe-4">メールアドレス</th>
                                            <th class="ps-4 pe-4">メニュー</th>
                                            <th class="ps-4 pe-4">決済方法</th>
                                            <th class="ps-4 pe-4">決済状況</th>
                                            <th class="ps-4 pe-4">支払日</th>
                                            <th class="ps-4 pe-4">申込日</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-4 pe-4">1827 8563</td>
                                            <td class="ps-4 pe-4">田中 翔太</td>
                                            <td class="ps-4 pe-4">tanaka@gmail.com</td>
                                            <td class="ps-4 pe-4">普通会員</td>
                                            <td class="ps-4 pe-4">クレジット</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">1992 8274</td>
                                            <td class="ps-4 pe-4">山田 健太</td>
                                            <td class="ps-4 pe-4">yamada@gmail.com</td>
                                            <td class="ps-4 pe-4">賛助会員</td>
                                            <td class="ps-4 pe-4">銀行振込</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/6/5</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4 text-nowrap">0004 9001</td>
                                            <td class="ps-4 pe-4">中村 優衣</td>
                                            <td class="ps-4 pe-4">nakamura@gmail.com</td>
                                            <td class="ps-4 pe-4">賛助会員</td>
                                            <td class="ps-4 pe-4">クレジット</td>
                                            <td class="ps-4 pe-4 text-danger">未決済</td>
                                            <td class="ps-4 pe-4">2021/10/21</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4 text-nowrap">0985 6485</td>
                                            <td class="ps-4 pe-4">佐藤 夢</td>
                                            <td class="ps-4 pe-4">sato@gmail.com</td>
                                            <td class="ps-4 pe-4">普通会員</td>
                                            <td class="ps-4 pe-4">銀行振込</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/4/1</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">1211 6421</td>
                                            <td class="ps-4 pe-4">高橋 美咲</td>
                                            <td class="ps-4 pe-4">takahashi@gmail.com</td>
                                            <td class="ps-4 pe-4">普通会員</td>
                                            <td class="ps-4 pe-4">銀行振込</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/4/1</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4 text-nowrap">0098 7362</td>
                                            <td class="ps-4 pe-4">伊藤 大輔</td>
                                            <td class="ps-4 pe-4">ito@gmail.com</td>
                                            <td class="ps-4 pe-4">普通会員</td>
                                            <td class="ps-4 pe-4">コンビニ決済</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/4/1</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">1135 5455</td>
                                            <td class="ps-4 pe-4">清水 由佳</td>
                                            <td class="ps-4 pe-4">shimizu@gmail.com</td>
                                            <td class="ps-4 pe-4">賛助会員</td>
                                            <td class="ps-4 pe-4">コンビニ決済</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/4/1</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">7767 6533</td>
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
                <div class="card search-area">
                    <div class="card-header ml-025">
                        <h5 class="card-title mb-0 mt-3">メール送信設定</h5>
                    </div>
                    <div class="card-body ml-025">
                        <div class="mb-3">
                            <label class="form-label">請求メール送信日時</label>
                            <div class="d-flex align-items-center">
                                <input type="number" name="event_date" class="form-control sp-w-35 w-25" value=3><span class="ps-2 pe-2">月</span>
                                <input name="event_date" class="form-control sp-w-35 w-25" value=25 type="number"><span class="ps-2 pe-2">日</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">督促メール送信日時( 1回目 )</label>
                            <div class="d-flex align-items-center">
                                <input name="event_date" class="form-control sp-w-35 w-25" value=4 type="number"><span class="ps-2 pe-2">月</span>
                                <input name="event_date" class="form-control sp-w-35 w-25" value=2 type="number"><span class="ps-2 pe-2">日</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">督促メール送信日時( 2回目 )</label>
                            <div class="d-flex align-items-center">
                                <input name="event_date" class="form-control sp-w-35 w-25" value=4 type="number"><span class="ps-2 pe-2">月</span>
                                <input name="event_date" class="form-control sp-w-35 w-25" value=16 type="number"><span class="ps-2 pe-2">日</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">除名期日</label>
                            <div class="d-flex align-items-center">
                                <input name="event_date" class="form-control sp-w-35 w-25" value=4 type="number"><span class="ps-2 pe-2">月</span>
                                <input name="event_date" class="form-control sp-w-35 w-25" value=16 type="number"><span class="ps-2 pe-2">日</span>
                            </div>
                        </div>
                        <div class="d-flex w-100 align-items-center justify-content-end">
                            <button id="submit" class="btn btn-primary mt-3 mb-3 ms-auto">更新</button>
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
    $('#search-button').on('click', function(event) {
        $('.search-area').css('display', 'block');
    });
    // モック用アラート　本番時は消してください
    $('#submit').on('click', function(event) {
        sessionStorage.setItem('alert', 'mock');
        setTimeout(() => {
            location.reload();
        }, 50);
    });
</script>