<?php
require_once('/var/www/html/moodle/config.php');
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');

// 入力値の保持とエラーメッセージの取得
$mail_title = "";
$mail_body = "";

// old_inputがあれば値を取得
if (isset($_SESSION['old_input'])) {
    $old_input = $_SESSION['old_input'];
    $mail_title = $old_input['mail_title'] ?? '';
    $mail_body = $old_input['mail_body'] ?? '';
}

// エラーメッセージを取得
$errors = $_SESSION['errors'] ?? [];
$message_error = isset($_SESSION['message_error']) ? $_SESSION['message_error'] : null;

// セッション変数をクリア
unset($_SESSION['errors'], $_SESSION['old_input'], $_SESSION['message_error']);
?>

<body id="event" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
    <div class="wrapper">
        <?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
        <div class="main">
            <div id="alert-container" class="position-fixed top-0 start-50 translate-middle-x" style="z-index: 1050; margin-top: 20px;"></div>
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <div class="navbar-collapse collapse">
                    <a class="sidebar-toggle js-sidebar-toggle">
                        <i class="hamburger align-self-center"></i>
                    </a>
                    <p class="title header-title ms-4 fs-4 fw-bold mb-0">DM送信</p>
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
                        <div class="card-body p-055">
                            <div class="mb-3">
                                <label class="form-label" for="notyf-message">対象区分</label>
                                <select id="kbn_id" name="kbn_id" class="form-control">
                                    <option value=1>全体</option>
                                    <option value=2>イベント</option>
                                    <option value=3>適塾記念会</option>
                                    <option value=4>名誉教授会</option>
                                    <option value=5>同窓会</option>
                                </select>
                            </div>
                            <div id="even-form" class="d-none">
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
                                    <div class="sp-ms-0 ms-3 mb-3 w-100">
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
                                        <select name="category_id" class="form-control">
                                            <option value=''>未選択</option>
                                            <option value=1>イベントA</option>
                                            <option value=2>イベントB</option>
                                            <option value=3>イベントC</option>
                                            <option value=4>イベントD</option>
                                            <option value=5>イベントE</option>
                                        </select>
                                    </div>
                                    <div class="sp-ms-0 mb-3 ms-3 w-100">
                                        <label class="form-label" for="notyf-message">回数</label>
                                        <select name="category_id" class="form-control w-100">
                                            <option value=1>すべて</option>
                                            <option value=1>第1回</option>
                                            <option value=2>第2回</option>
                                            <option value=3>第3回</option>
                                            <option value=4>第4回</option>
                                            <option value=5>第5回</option>
                                            <option value=2>第6回</option>
                                            <option value=3>第7回</option>
                                            <option value=4>第8回</option>
                                            <option value=5>第9回</option>
                                        </select>
                                    </div>
                                </div>
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
                    <form method="POST" action="/custom/admin/app/Controllers/message/message_controller.php">
                        <div class="card min-70vh">
                            <div class="card-body p-0">
                                <div class="d-flex w-100 align-items-center justify-content-end mt-3">
                                    <div class="mt-4"></div>
                                    <!-- <button class="btn btn-primary mt-3 mb-3 d-flex justify-content-center align-items-center">
                                        <i class="align-middle me-1 mt-01" data-feather="send"></i>送信
                                    </button> -->
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
                                                <td class="ps-4 pe-4 text-nowrap">0000 0091</td>
                                                <td class="ps-4 pe-4">田中 翔太</td>
                                                <td class="ps-4 pe-4">tanaka@gmail.com</td>
                                                <td class="ps-4 pe-4">普通会員</td>
                                                <td class="ps-4 pe-4">クレジット</td>
                                                <td class="ps-4 pe-4">決済済</td>
                                                <td class="ps-4 pe-4">2024/4/1</td>
                                                <td class="ps-4 pe-4">2023/12/20</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 pe-4 text-nowrap">0090 8989</td>
                                                <td class="ps-4 pe-4">山田 健太</td>
                                                <td class="ps-4 pe-4">yamada@gmail.com</td>
                                                <td class="ps-4 pe-4">賛助会員</td>
                                                <td class="ps-4 pe-4">口座振替</td>
                                                <td class="ps-4 pe-4">決済済</td>
                                                <td class="ps-4 pe-4">2024/4/5</td>
                                                <td class="ps-4 pe-4">2023/10/15</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 pe-4">1100 7767</td>
                                                <td class="ps-4 pe-4">中村 優衣</td>
                                                <td class="ps-4 pe-4">nakamura@gmail.com</td>
                                                <td class="ps-4 pe-4">賛助会員</td>
                                                <td class="ps-4 pe-4">クレジット</td>
                                                <td class="ps-4 pe-4  text-danger">未決済</td>
                                                <td class="ps-4 pe-4">2023/4/7</td>
                                                <td class="ps-4 pe-4">2023/4/7</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 pe-4">1101 4334</td>
                                                <td class="ps-4 pe-4">佐藤 夢</td>
                                                <td class="ps-4 pe-4">sato@gmail.com</td>
                                                <td class="ps-4 pe-4">普通会員</td>
                                                <td class="ps-4 pe-4">口座振替</td>
                                                <td class="ps-4 pe-4">決済済</td>
                                                <td class="ps-4 pe-4">2024/4/1</td>
                                                <td class="ps-4 pe-4">2020/1/20</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 pe-4">1105 5545</td>
                                                <td class="ps-4 pe-4">高橋 美咲</td>
                                                <td class="ps-4 pe-4">takahashi@gmail.com</td>
                                                <td class="ps-4 pe-4">普通会員</td>
                                                <td class="ps-4 pe-4">口座振替</td>
                                                <td class="ps-4 pe-4">決済済</td>
                                                <td class="ps-4 pe-4">2024/4/1</td>
                                                <td class="ps-4 pe-4">2023/7/20</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 pe-4">1120 7768</td>
                                                <td class="ps-4 pe-4">伊藤 大輔</td>
                                                <td class="ps-4 pe-4">ito@gmail.com</td>
                                                <td class="ps-4 pe-4">普通会員</td>
                                                <td class="ps-4 pe-4">コンビニ決済</td>
                                                <td class="ps-4 pe-4">決済済</td>
                                                <td class="ps-4 pe-4">2024/4/1</td>
                                                <td class="ps-4 pe-4">2024/4/1</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 pe-4">1125 5454</td>
                                                <td class="ps-4 pe-4">清水 由佳</td>
                                                <td class="ps-4 pe-4">shimizu@gmail.com</td>
                                                <td class="ps-4 pe-4">賛助会員</td>
                                                <td class="ps-4 pe-4">コンビニ決済</td>
                                                <td class="ps-4 pe-4">決済済</td>
                                                <td class="ps-4 pe-4">2024/4/1</td>
                                                <td class="ps-4 pe-4">2024/4/1</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 pe-4">1135 6654</td>
                                                <td class="ps-4 pe-4">加藤 拓也</td>
                                                <td class="ps-4 pe-4">kato@gmail.com</td>
                                                <td class="ps-4 pe-4">賛助会員</td>
                                                <td class="ps-4 pe-4">クレジット</td>
                                                <td class="ps-4 pe-4">決済済</td>
                                                <td class="ps-4 pe-4">2024/4/1</td>
                                                <td class="ps-4 pe-4">2024/3/20</td>
                                            </tr>
                                            <!-- hiddenでメールアドレスを保持 -->
                                            <!-- <tr>
                                                <td class="ps-4 pe-4">0000 0000</td>
                                                <td class="ps-4 pe-4">テスト 太郎0</td>
                                                <td class="ps-4 pe-4">k.kawai@trans-it.net</td>
                                                <td class="ps-4 pe-4">賛助会員</td>
                                                <td class="ps-4 pe-4">クレジット</td>
                                                <td class="ps-4 pe-4">決済済</td>
                                                <td class="ps-4 pe-4">2024/4/1</td>
                                                <td class="ps-4 pe-4">2024/3/20</td>
                                                
                                                <input type="hidden" name="mail_to_list[]" value="test@trans-it.net">
                                            </tr> -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="d-flex pc-pagenation">
                                <div class="dataTables_paginate paging_simple_numbers ms-auto mr-025" id="datatables-buttons_paginate">
                                    <ul class="pagination">
                                        <li class="paginate_button page-item previous" id="datatables-buttons_previous"><a href="#" aria-controls="datatables-buttons" data-dt-idx="0" tabindex="0" class="page-link">Previous</a></li>
                                        <li class="paginate_button page-item active"><a href="#" aria-controls="datatables-buttons" data-dt-idx="1" tabindex="0" class="page-link">1</a></li>
                                        <li class="paginate_button page-item "><a href="#" aria-controls="datatables-buttons" data-dt-idx="2" tabindex="0" class="page-link">2</a></li>
                                        <li class="paginate_button page-item "><a href="#" aria-controls="datatables-buttons" data-dt-idx="3" tabindex="0" class="page-link">3</a></li>
                                        <li class="paginate_button page-item "><a href="#" aria-controls="datatables-buttons" data-dt-idx="4" tabindex="0" class="page-link">4</a></li>
                                        <li class="paginate_button page-item "><a href="#" aria-controls="datatables-buttons" data-dt-idx="5" tabindex="0" class="page-link">5</a></li>
                                        <li class="paginate_button page-item "><a href="#" aria-controls="datatables-buttons" data-dt-idx="6" tabindex="0" class="page-link">6</a></li>
                                        <li class="paginate_button page-item next" id="datatables-buttons_next"><a href="#" aria-controls="datatables-buttons" data-dt-idx="7" tabindex="0" class="page-link">Next</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="d-flex sp-pagenation">
                                <div class="dataTables_paginate paging_simple_numbers ms-auto mr-025" id="datatables-buttons_paginate">
                                    <ul class="pagination">
                                        <li class="paginate_button page-item previous" id="datatables-buttons_previous"><a href="#" aria-controls="datatables-buttons" data-dt-idx="0" tabindex="0" class="page-link">Previous</a></li>
                                        <li class="paginate_button page-item active"><a href="#" aria-controls="datatables-buttons" data-dt-idx="1" tabindex="0" class="page-link">1</a></li>
                                        <li class="paginate_button page-item "><a href="#" aria-controls="datatables-buttons" data-dt-idx="2" tabindex="0" class="page-link">2</a></li>
                                        <li class="paginate_button page-item "><a href="#" aria-controls="datatables-buttons" data-dt-idx="3" tabindex="0" class="page-link">3</a></li>
                                        <li class="paginate_button page-item next" id="datatables-buttons_next"><a href="#" aria-controls="datatables-buttons" data-dt-idx="4" tabindex="0" class="page-link">Next</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0 mt-3">メール送信内容</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($message_error)): ?>
                                    <div class="alert alert-danger">
                                        <?= htmlspecialchars($message_error); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <label class="form-label">メールタイトル</label>
                                    <span class="badge bg-danger">必須</span>
                                    <div class="align-items-center">
                                        <textarea name="mail_title" class="form-control w-100"  required><?= htmlspecialchars($mail_title) ?></textarea>
                                        <?php if (!empty($errors['mail_title'])): ?>
                                            <div class="text-danger mt-2">
                                                <?= htmlspecialchars($errors['mail_title']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-label d-flex align-items-center">
                                        <label class="me-2">メール本文</label>
                                        <span class="badge bg-danger">必須</span>
                                    </div>
                                    <div class="align-items-center">
                                        <textarea name="mail_body" class="form-control w-100" rows=5 required><?= htmlspecialchars($mail_body) ?></textarea>
                                        <?php if (!empty($errors['mail_body'])): ?>
                                            <div class="text-danger mt-2">
                                                <?= htmlspecialchars($errors['mail_body']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex w-100 align-items-center justify-content-end">
                                    <button type="submit" class="btn btn-primary mt-3 mb-3 me-0 d-flex justify-content-center align-items-center">
                                        <i class="align-middle me-1 mt-01" data-feather="send"></i>送信
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script src="/custom/admin/public/js/app.js"></script>
</body>

</html>

<script>
    $(document).ready(function() {
        $('select[name="kbn_id"]').on('change', function(event) {
            if ($(this).val() == 2) {
                $('#even-form').removeClass('d-none');
            } else {
                $('#even-form').addClass('d-none');
            }
        });
    });
</script>