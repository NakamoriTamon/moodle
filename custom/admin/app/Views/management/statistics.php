<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php'); ?>

<body id="management" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
    <div class="wrapper">
        <?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <div class="navbar-collapse collapse">
                    <p class="title ms-4 fs-4 fw-bold mb-0">統計データ一覧</p>
                    <p class="title mb-0"></p>
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

            <!-- <main class="content">
                <div class="col-12 col-lg-12">
                    <div class="card min-70vh">
                        <div class="card-body p-0">
                            <div class="d-flex w-100 align-items-center justify-content-between mt-3">
                                <div></div>
                                <div class="d-flex align-items-center button-div mr-025">
                                    <button class="btn btn-primary mt-3 mb-3 me-2 d-flex justify-content-center align-items-center">
                                        <i class="align-middle me-1" data-feather="download"></i>CSV出力
                                    </button>
                                    <button class="btn btn-primary mt-3 mb-3">更新</button>
                                </div>
                            </div>
                            <div class="card m-auto mb-5 w-95">
                                <table class="table table-responsive table-striped table_list">
                                    <thead>
                                        <tr>
                                            <th class="ps-4 pe-4">ID</th>
                                            <th class="ps-4 pe-4">氏名</th>
                                            <th class="ps-4 pe-4">フリガナ</th>
                                            <th class="ps-4 pe-4 text-nowrap">住所</th>
                                            <th class="ps-4 pe-4">メールアドレス</th>
                                            <th class="ps-4 pe-4">電話番号</th>
                                            <th class="ps-4 pe-4">保護者指名</th>
                                            <th class="ps-4 pe-4">保護者連絡先</th>
                                            <th class="ps-4 pe-4">支払方法</th>
                                            <th class="ps-4 pe-4">適塾記念会入会状況</th>
                                            <th class="ps-4 pe-4">申込日</th>
                                            <th class="w-170 ps-4 pe-4">アカウント承認設定</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-4 pe-4">1</td>
                                            <td class="ps-4 pe-4 text-nowrap">田中 翔太</td>
                                            <td class="ps-4 pe-4">タナカ ショウタ</td>
                                            <td class="ps-4 pe-4">大阪府</td>
                                            <td class="ps-4 pe-4">tanaka@gmail.com</td>
                                            <td class="ps-4 pe-4">07018271254</td>
                                            <td class="ps-4 pe-4"></td>
                                            <td class="ps-4 pe-4"></td>
                                            <td class="ps-4 pe-4">クレジット</td>
                                            <td class="ps-4 pe-4">入会済</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                            <td class="ps-4 pe-4">
                                                <select name=" category_id" class="form-control">
                                                    <option value=1>承認</option>
                                                    <option value=2>非承認</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">2</td>
                                            <td class="ps-4 pe-4">山田 健太</td>
                                            <td class="ps-4 pe-4">ヤマダ ケンタ</td>
                                            <td class="ps-4 pe-4">愛知県</td>
                                            <td class="ps-4 pe-4">yamada@gmail.com</td>
                                            <td class="ps-4 pe-4">09019991827</td>
                                            <td class="ps-4 pe-4"></td>
                                            <td class="ps-4 pe-4"></td>
                                            <td class="ps-4 pe-4">クレジット</td>
                                            <td class="ps-4 pe-4">入会済</td>
                                            <td class="ps-4 pe-4">2024/10/19</td>
                                            <td class="ps-4 pe-4">
                                                <select name="category_id" class="form-control">
                                                    <option value=1>承認</option>
                                                    <option value=2>非承認</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">3</td>
                                            <td class="ps-4 pe-4">中村 優衣</td>
                                            <td class="ps-4 pe-4">ナカムラ ユイ</td>
                                            <td class="ps-4 pe-4">三重県</td>
                                            <td class="ps-4 pe-4">nakamura@gmail.com</td>
                                            <td class="ps-4 pe-4">07019283712</td>
                                            <td class="ps-4 pe-4">中村 徹</td>
                                            <td class="ps-4 pe-4 text-nowrap">ナカムラ トオル</td>
                                            <td class="ps-4 pe-4">銀行振込</td>
                                            <td class="ps-4 pe-4">未入会</td>
                                            <td class="ps-4 pe-4"></td>
                                            <td class="ps-4 pe-4">
                                                <select name="category_id" class="form-control">
                                                    <option value=1>承認</option>
                                                    <option selected value=2>非承認</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main> -->
        </div>
    </div>
    <script src="/custom/admin/public/js/app.js"></script>
</body>

</html>