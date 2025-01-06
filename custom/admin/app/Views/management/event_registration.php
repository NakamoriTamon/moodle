<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php'); ?>

<body id="management" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
    <div class="wrapper">
        <?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <div class="navbar-collapse collapse">
                    <p class="title ms-4 fs-4 fw-bold mb-0">イベント登録情報一覧</p>
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

            <main class="content">
                <div class="col-12 col-lg-12">
                    <div class="card min-70vh">
                        <div class="card-body p-0">
                            <div class="d-flex w-100 align-items-center justify-content-between mt-3">
                                <select name="event_id" class="form-control w-25 search-select">
                                    <option value="">未選択</option>
                                    <option value=1 selected>タンパク質の精製技術の基礎</option>
                                    <option value=2>AIと機械学習の基礎講座</option>
                                    <option value=3>量子コンピュータ入門: 次世代計算技術の扉を開く</option>
                                    <option value=4>気候変動と持続可能なエネルギーソリューション</option>
                                    <option value=5>心理学で学ぶ意思決定と行動経済学</option>
                                </select>
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
                                            <th class="ps-4 pe-4">ユーザー名</th>
                                            <th class="ps-4 pe-4">メールアドレス</th>
                                            <th class="ps-4 pe-4">決済方法</th>
                                            <th class="ps-4 pe-4">決済状況</th>
                                            <th class="ps-4 pe-4">決済日</th>
                                            <th class="ps-4 pe-4">申込日</th>
                                            <th class="w-170 ps-4 pe-4">アカウント承認設定</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-4 pe-4">1</td>
                                            <td class="ps-4 pe-4">田中 翔太</td>
                                            <td class="ps-4 pe-4">tanaka@gmail.com</td>
                                            <td class="ps-4 pe-4">クレジット</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
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
                                            <td class="ps-4 pe-4">yamada@gmail.com</td>
                                            <td class="ps-4 pe-4">口座振替</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/6/5</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
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
                                            <td class="ps-4 pe-4">nakamura@gmail.com</td>
                                            <td class="ps-4 pe-4">クレジット</td>
                                            <td class="ps-4 pe-4 text-danger">未決済</td>
                                            <td class="ps-4 pe-4">2024/10/21</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                            <td class="ps-4 pe-4">
                                                <select name="category_id" class="form-control">
                                                    <option value=1>承認</option>
                                                    <option selected value=2>非承認</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">4</td>
                                            <td class="ps-4 pe-4">佐藤 夢</td>
                                            <td class="ps-4 pe-4">sato@gmail.com</td>
                                            <td class="ps-4 pe-4">口座振替</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/4/1</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                            <td class="ps-4 pe-4">
                                                <select name="category_id" class="form-control">
                                                    <option value=1>承認</option>
                                                    <option value=2>非承認</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">5</td>
                                            <td class="ps-4 pe-4">高橋 美咲</td>
                                            <td class="ps-4 pe-4">takahashi@gmail.com</td>
                                            <td class="ps-4 pe-4">口座振替</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/4/1</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                            <td class="ps-4 pe-4">
                                                <select name="category_id" class="form-control">
                                                    <option value=1>承認</option>
                                                    <option value=2>非承認</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">6</td>
                                            <td class="ps-4 pe-4">伊藤 大輔</td>
                                            <td class="ps-4 pe-4">ito@gmail.com</td>
                                            <td class="ps-4 pe-4">コンビニ決済</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/4/1</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                            <td class="ps-4 pe-4">
                                                <select name="category_id" class="form-control">
                                                    <option value=1>承認</option>
                                                    <option value=2>非承認</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">7</td>
                                            <td class="ps-4 pe-4">清水 由佳</td>
                                            <td class="ps-4 pe-4">shimizu@gmail.com</td>
                                            <td class="ps-4 pe-4">コンビニ決済</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/4/1</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                            <td class="ps-4 pe-4">
                                                <select name="category_id" class="form-control">
                                                    <option value=1>承認</option>
                                                    <option value=2>非承認</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">8</td>
                                            <td class="ps-4 pe-4">加藤 拓也</td>
                                            <td class="ps-4 pe-4">kato@gmail.com</td>
                                            <td class="ps-4 pe-4">クレジット</td>
                                            <td class="ps-4 pe-4">決済済</td>
                                            <td class="ps-4 pe-4">2024/4/1</td>
                                            <td class="ps-4 pe-4">2024/12/20</td>
                                            <td class="ps-4 pe-4">
                                                <select name="category_id" class="form-control">
                                                    <option value=1>承認</option>
                                                    <option value=2>非承認</option>
                                                </select>
                                            </td>
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