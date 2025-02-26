<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php'); ?>

<body id="management" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
    <div class="wrapper">
        <?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <div class="navbar-collapse collapse">
                    <a class="sidebar-toggle js-sidebar-toggle">
                        <i class="hamburger align-self-center"></i>
                    </a>
                    <p class="title header-title ms-4 fs-4 fw-bold mb-0">イベント登録情報一覧</p>
                    <p class="title mb-0"></p>
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
                                    <label class="form-label" for="notyf-message">カテゴリー</label>
                                    <select name="category_id" class="form-control">
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
                                    </select>
                                </div>
                                <div class="ms-3 sp-ms-0 mb-3 w-100">
                                    <label class="form-label" for="notyf-message">開催ステータス</label>
                                    <select name="category_id" class="form-control">
                                        <option value=1>未選択</option>
                                        <option value=1>開催前</option>
                                        <option value=2>開催中</option>
                                        <option value=3>開催終了</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex sp-block justify-content-between">
                                <div class="mb-3 w-100">
                                    <label class="form-label" for="notyf-message">イベント名</label>
                                    <select name="event_id" class="form-control">
                                        <option value="">未選択</option>
                                        <option value=1>タンパク質の精製技術の基礎</option>
                                        <option value=2>AIと機械学習の基礎講座</option>
                                        <option value=3>量子コンピュータ入門: 次世代計算技術の扉を開く</option>
                                        <option value=4>気候変動と持続可能なエネルギーソリューション</option>
                                        <option value=5>心理学で学ぶ意思決定と行動経済学</option>
                                    </select>
                                </div>
                                <div class="mb-4 ms-3 sp-ms-0 w-100">
                                    <label class="form-label" for="notyf-message">フリーワード</label>
                                    <input id="notyf-message" name="notyf-message" type="text" class="form-control" placeholder="田中 翔太">
                                </div>
                            </div>
                            <!-- <hr> -->
                            <div class="d-flex w-100">
                                <button id="search-button" class="btn btn-primary mb-3 me-0 ms-auto">検索</button>
                            </div>
                        </div>
                    </div>
                    <div id="result_card" class="col-12 col-lg-12 d-none">
                        <div class="card min-70vh">
                            <div class="card-body p-0">
                                <div class="d-flex w-100 align-items-center justify-content-between mt-3">
                                    <div></div>
                                    <div class="d-flex align-items-center button-div mr-025">
                                        <button class="btn btn-primary mt-3 mb-3 d-flex justify-content-center align-items-center">
                                            <i class="align-middle me-1" data-feather="download"></i>CSV出力
                                        </button>
                                    </div>
                                </div>
                                <div class="card m-auto mb-5 w-95">
                                    <table class="table table-responsive table-striped table_list">
                                        <thead>
                                            <tr>
                                                <th class="ps-4 pe-4">ID</th>
                                                <th class="ps-4 pe-4">イベント名</th>
                                                <th class="ps-4 pe-4">講座回数</th>
                                                <th class="ps-4 pe-4">ユーザーID</th>
                                                <th class="ps-4 pe-4">ユーザー名</th>
                                                <th class="ps-4 pe-4">メールアドレス</th>
                                                <th class="ps-4 pe-4">決済方法</th>
                                                <th class="ps-4 pe-4">決済状況</th>
                                                <th class="ps-4 pe-4">決済日</th>
                                                <th class="ps-4 pe-4">申込日</th>
                                                <th class="ps-4 pe-4">参加状態</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="ps-4 pe-4">1</td>
                                                <td class="ps-4 pe-4">タンパク質の精製技術の基礎</td>
                                                <td class="ps-4 pe-4">第1講座</td>
                                                <td class="ps-4 pe-4">1937 8274</td>
                                                <td class="ps-4 pe-4">田中 翔太</td>
                                                <td class="ps-4 pe-4">tanaka@gmail.com</td>
                                                <td class="ps-4 pe-4">クレジット</td>
                                                <td class="ps-4 pe-4">決済済</td>
                                                <td class="ps-4 pe-4">2024/12/20</td>
                                                <td class="ps-4 pe-4">2024/12/20</td>
                                                <td class="ps-4 pe-4">
                                                    <select class="form-control min-100">
                                                        <option value="">未参加</option>
                                                        <option value="" selected>参加済</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 pe-4">1</td>
                                                <td class="ps-4 pe-4">タンパク質の精製技術の基礎</td>
                                                <td class="ps-4 pe-4">第1講座</td>
                                                <td class="ps-4 pe-4">1628 7451</td>
                                                <td class="ps-4 pe-4">山田 健太</td>
                                                <td class="ps-4 pe-4">yamada@gmail.com</td>
                                                <td class="ps-4 pe-4">銀行振込</td>
                                                <td class="ps-4 pe-4">決済済</td>
                                                <td class="ps-4 pe-4">2024/6/5</td>
                                                <td class="ps-4 pe-4">2024/12/20</td>
                                                <td class="ps-4 pe-4">
                                                    <select class="form-control min-100">
                                                        <option value="">未参加</option>
                                                        <option value="" selected>参加済</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 pe-4">1</td>
                                                <td class="ps-4 pe-4">タンパク質の精製技術の基礎</td>
                                                <td class="ps-4 pe-4">第1講座</td>
                                                <td class="ps-4 pe-4">1524 8472</td>
                                                <td class="ps-4 pe-4">中村 優衣</td>
                                                <td class="ps-4 pe-4">nakamura@gmail.com</td>
                                                <td class="ps-4 pe-4">クレジット</td>
                                                <td class="ps-4 pe-4 text-danger">未決済</td>
                                                <td class="ps-4 pe-4">2024/10/21</td>
                                                <td class="ps-4 pe-4">2024/12/20</td>
                                                <td class="ps-4 pe-4">
                                                    <select class="form-control min-100">
                                                        <option value="">未参加</option>
                                                        <option value="" selected>参加済</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 pe-4">1</td>
                                                <td class="ps-4 pe-4">タンパク質の精製技術の基礎</td>
                                                <td class="ps-4 pe-4">第2講座</td>
                                                <td class="ps-4 pe-4 text-nowrap">0000 0821</td>
                                                <td class="ps-4 pe-4">佐藤 夢</td>
                                                <td class="ps-4 pe-4">sato@gmail.com</td>
                                                <td class="ps-4 pe-4">銀行振込</td>
                                                <td class="ps-4 pe-4">決済済</td>
                                                <td class="ps-4 pe-4">2024/4/1</td>
                                                <td class="ps-4 pe-4">2024/12/20</td>
                                                <td class="ps-4 pe-4">
                                                    <select class="form-control min-100">
                                                        <option value="">未参加</option>
                                                        <option value="">参加済</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 pe-4">1</td>
                                                <td class="ps-4 pe-4">タンパク質の精製技術の基礎</td>
                                                <td class="ps-4 pe-4">第2講座</td>
                                                <td class="ps-4 pe-4">1938 5756</td>
                                                <td class="ps-4 pe-4">高橋 美咲</td>
                                                <td class="ps-4 pe-4">takahashi@gmail.com</td>
                                                <td class="ps-4 pe-4">銀行振込</td>
                                                <td class="ps-4 pe-4">決済済</td>
                                                <td class="ps-4 pe-4">2024/4/1</td>
                                                <td class="ps-4 pe-4">2024/12/20</td>
                                                <td class="ps-4 pe-4">
                                                    <select class="form-control min-100">
                                                        <option value="">未参加</option>
                                                        <option value="" selected>参加済</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 pe-4">1</td>
                                                <td class="ps-4 pe-4">タンパク質の精製技術の基礎</td>
                                                <td class="ps-4 pe-4">第2講座</td>
                                                <td class="ps-4 pe-4">1231 9374</td>
                                                <td class="ps-4 pe-4">伊藤 大輔</td>
                                                <td class="ps-4 pe-4">ito@gmail.com</td>
                                                <td class="ps-4 pe-4">コンビニ決済</td>
                                                <td class="ps-4 pe-4">決済済</td>
                                                <td class="ps-4 pe-4">2024/4/1</td>
                                                <td class="ps-4 pe-4">2024/12/20</td>
                                                <td class="ps-4 pe-4">
                                                    <select class="form-control min-100">
                                                        <option value="">未参加</option>
                                                        <option value="" selected>参加済</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 pe-4">1</td>
                                                <td class="ps-4 pe-4">タンパク質の精製技術の基礎</td>
                                                <td class="ps-4 pe-4">第3講座</td>
                                                <td class="ps-4 pe-4">1827 4651</td>
                                                <td class="ps-4 pe-4">清水 由佳</td>
                                                <td class="ps-4 pe-4">shimizu@gmail.com</td>
                                                <td class="ps-4 pe-4">コンビニ決済</td>
                                                <td class="ps-4 pe-4">決済済</td>
                                                <td class="ps-4 pe-4">2024/4/1</td>
                                                <td class="ps-4 pe-4">2024/12/20</td>
                                                <td class="ps-4 pe-4">
                                                    <select class="form-control min-100">
                                                        <option value="">未参加</option>
                                                        <option value="">参加済</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 pe-4">1</td>
                                                <td class="ps-4 pe-4">タンパク質の精製技術の基礎</td>
                                                <td class="ps-4 pe-4">第3講座</td>
                                                <td class="ps-4 pe-4">1281 7362</td>
                                                <td class="ps-4 pe-4">加藤 拓也</td>
                                                <td class="ps-4 pe-4">kato@gmail.com</td>
                                                <td class="ps-4 pe-4">クレジット</td>
                                                <td class="ps-4 pe-4">決済済</td>
                                                <td class="ps-4 pe-4">2024/4/1</td>
                                                <td class="ps-4 pe-4">2024/12/20</td>
                                                <td class="ps-4 pe-4">
                                                    <select class="form-control min-100">
                                                        <option value="">未参加</option>
                                                        <option value="">参加済</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
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
    $(document).ready(function() {
        let selectedId;
        // 削除リンクがクリックされたとき
        $('#search-button').on('click', function(event) {
            $('#result_card').removeClass('d-none');
        });
    });
</script>