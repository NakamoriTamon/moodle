<?php
require_once('/var/www/html/moodle/config.php');
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/management/membership_fee_registration_controller.php');
$membership_fee_registration_controller = new MembershipFeeRegistrationController();
$result_list = $membership_fee_registration_controller->index($_GET['id'] ?? null);

// ページネーション
$total_count = $result_list['total_count'];
$per_page = $result_list['per_page'];
$current_page = $result_list['current_page'];
$page = $result_list['page'];
$email_send_setting = $result_list['email_send_setting'];
$subject_ids = "";

$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input'], $_SESSION['errors']);
?>

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
                                <div class="fs-5 me-4 text-decoration-underline"><?= htmlspecialchars($USER->name) ?></div>
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
                            <form method="POST" id="search-form" action="/custom/admin/app/Views/management/membership_fee_registration.php">
                                <input type="hidden" name="page" value="<?= $page ?>">
                                <div class="d-flex sp-block justify-content-between">
                                    <div class="mb-3 w-100">
                                        <label class="form-label" for="notyf-message">会名</label>
                                        <select name="category_id" class="form-control">
                                            <option value=1>適塾記念会</option>
                                        </select>
                                    </div>
                                    <div class="ms-3 sp-ms-0 mb-3 w-100">
                                        <label class="form-label" for="notyf-message">支払年度</label>
                                        <select name="year" class="form-control">
                                            <option value="" selected disabled>未選択</option>
                                            <?php for ($i = 2024; $i < 2031; $i++) { ?>
                                                <option value=<?= htmlspecialchars($i) ?> <?php if (isset($old_input['year']) && $old_input['year'] == $i) { ?>selected<?php } ?>>
                                                    <?= htmlspecialchars($i) . "年度" ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex sp-block justify-content-between">
                                    <div class="mb-3 w-100">
                                        <label class="form-label" for="notyf-message">決済状態</label>
                                        <select name="payment_status" class="form-control">
                                            <option value=>未選択</option>
                                            <option value='決済済' <?php if (isset($old_input['payment_status']) && $old_input['payment_status'] == '決済済') { ?>selected<?php } ?>>決済済</option>
                                            <option value='未決済' <?php if (isset($old_input['payment_status']) && $old_input['payment_status'] == '未決済') { ?>selected<?php } ?>>未決済</option>
                                        </select>
                                    </div>
                                    <div class="ms-3 sp-ms-0 mb-3 w-100">
                                        <label class="form-label" for="notyf-message">フリーワード</label>
                                        <input id="notyf-message" name="keyword" type="text" class="form-control" placeholder="田中 翔太" value="<?= isset($old_input['keyword']) ? $old_input['keyword'] : '' ?>">
                                    </div>
                                </div>
                                <!-- <hr> -->
                                <div class="d-flex w-100">
                                    <button id="search-button" class="btn btn-primary mb-3 me-0 ms-auto">検索</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php if (!empty($result_list['tekijuku_commemoration_list'])) { ?>
                        <div class="card min-70vh">
                            <div class="card-body p-0">
                                <div class="d-flex w-100 align-items-center justify-content-end mt-3 mb-3">
                                    <div></div>
                                    <div class="d-flex ms-auto  button-div mr-025">
                                        <button id="csv_button" class="btn btn-primary mt-3 d-flex justify-content-center align-items-center">
                                            <i class="align-middle me-1" data-feather="download"></i>CSV出力
                                        </button>
                                    </div>
                                </div>
                                <!-- 非表示のCSVエクスポートフォーム -->
                                <form id="csvExportForm" method="POST" action="/custom/admin/app/Controllers/management/membership_fee_export_controller.php">
                                    <input type="hidden" name="year" value="<?= $old_input['year'] ?? '' ?>">
                                    <input type="hidden" name="keyword" value="<?= $old_input['keyword'] ?? '' ?>">
                                    <input type="hidden" name="payment_status" value="<?= $old_input['payment_status'] ?? '' ?>">
                                </form>
                                <div class="card m-auto mb-5 w-95">
                                    <table class="table table-responsive table-striped table_list" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="ps-4 pe-4 text-nowrap">会員番号</th>
                                                <th class="ps-4 pe-4 text-nowrap">ユーザー名</th>
                                                <th class="ps-4 pe-4 text-nowrap">メールアドレス</th>
                                                <th class="ps-4 pe-4 text-nowrap">郵便番号</th>
                                                <th class="ps-4 pe-4 text-nowrap">住所</th>
                                                <th class="ps-4 pe-4 text-nowrap">メニュー</th>
                                                <th class="ps-4 pe-4 text-nowrap">口数</th>
                                                <th class="ps-4 pe-4 text-nowrap">所属部局</th>
                                                <th class="ps-4 pe-4 text-nowrap">部課・専攻名</th>
                                                <th class="ps-4 pe-4 text-nowrap">職名・学年</th>
                                                <th class="ps-4 pe-4 text-nowrap">決済状況</th>
                                                <th class="ps-4 pe-4 text-nowrap">決済方法</th>
                                                <th class="ps-4 pe-4 text-nowrap">支払日</th>
                                                <th class="ps-4 pe-4 text-nowrap">申込日</th>
                                                <th class="ps-4 pe-4 text-nowrap">旧会員番号</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($result_list['tekijuku_commemoration_list'] as $result) { ?>
                                                <tr>
                                                    <?php
                                                    $number = str_pad($result['number'], 8, '0', STR_PAD_LEFT);
                                                    $menu = $result['type_code'] === 1 ? '普通会員' : '賛助会員';
                                                    $created_date = new DateTime($result['created_at']);
                                                    $paid_date = '';

                                                    if (empty($subject_ids)) {
                                                        $subject_ids = $result['id'];
                                                    } else {
                                                        $subject_ids .= "," . $result['id'];
                                                    }
                                                    if (!empty($result['paid_date_history'])) {
                                                        $paid_date = new DateTime($result['paid_date_history']);
                                                        $paid_date = $paid_date->format("Y年n月j日");
                                                    }
                                                    ?>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars(substr_replace($number, ' ', 4, 0)) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($result['name']) ?>
                                                        <?php if ($result['is_delete']) {
                                                            echo '(退会済)';
                                                        } ?>
                                                    </td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($result['email']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($result['post_code']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($result['address']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($menu) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($result['unit']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($result['department']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($result['major']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($result['official']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap <?php if ($result['payment_status'] == '未決済') { ?>text-danger<?php } ?>">
                                                        <?= htmlspecialchars($result['payment_status']) ?>
                                                    </td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($payment_select_list[$result['payment_method']]) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($paid_date) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($created_date->format("Y年n月j日")) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($result['old_number'] ?? '') ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex">
                                    <div class="dataTables_paginate paging_simple_numbers ms-auto mr-025" id="datatables-buttons_paginate">
                                        <ul class="pagination">
                                            <?php
                                            $total_pages = ceil($total_count / $per_page);
                                            $start_page = max(1, $current_page - 1); // 最小1
                                            $end_page = min($total_pages, $start_page + 2); // 最大3つ

                                            // 前のページボタン
                                            if ($current_page > 1): ?>
                                                <li class="paginate_button page-item previous">
                                                    <a data-page="<?= $current_page - 1 ?>" aria-controls="datatables-buttons" class="page-link">Previous</a>
                                                </li>
                                            <?php endif; ?>

                                            <?php
                                            // ページ番号の表示
                                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                                <li class="paginate_button page-item <?= $i == $current_page ? 'active' : '' ?>">
                                                    <a data-page="<?= $i ?>" aria-controls="datatables-buttons" class="page-link"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php
                                            // 次のページボタン
                                            if ($current_page < $total_pages): ?>
                                                <li class="paginate_button page-item next">
                                                    <a data-page="<?= $current_page + 1 ?>" aria-controls="datatables-buttons" class="page-link">Next</a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php if (!empty($result_list['tekijuku_commemoration_list'])) { ?>
                    <div class="card">
                        <div class="card-header ml-025">
                            <h5 class="card-title mb-0 mt-3">メール送信設定</h5>
                        </div>
                        <form method="POST" id="search-form" action="/custom/admin/app/Controllers/management/membership_fee_upsert_controller.php">
                            <input type="hidden" id="email_send_setting_id" name="email_send_setting_id" value="<?= htmlspecialchars(empty($email_send_setting) ? '' : $email_send_setting['id']) ?>">
                            <input type="hidden" id="subject_ids" name="subject_ids" value="<?= htmlspecialchars($subject_ids) ?>">
                            <input type="hidden" name="select_category_id" value="<?= isset($old_input['category_id']) ? $old_input['category_id'] : '' ?>">
                            <input type="hidden" name="select_year" value="<?= isset($old_input['year']) ? $old_input['year'] : '' ?>">
                            <input type="hidden" name="select_keyword" value="<?= isset($old_input['keyword']) ? $old_input['keyword'] : '' ?>">
                            <input type="hidden" name="select_payment_status" value="<?= isset($old_input['payment_status']) ? $old_input['payment_status'] : '' ?>">
                            <div class="card-body ml-025">
                                <div class="mb-3">
                                    <label class="form-label">請求メール送信日時</label>
                                    <div class="d-flex align-items-center">
                                        <input type="number" name="requert_month" class="form-control sp-w-35 w-25" value="<?= htmlspecialchars(empty($email_send_setting) ? '' : $email_send_setting['requert_month']) ?>"><span class="ps-2 pe-2">月</span>
                                        <input type="number" name="requert_day" class="form-control sp-w-35 w-25" value="<?= htmlspecialchars(empty($email_send_setting) ? '' : $email_send_setting['requert_month']) ?>"><span class="ps-2 pe-2">日</span>
                                    </div>
                                    <?php if (!empty($errors['requert_month'])): ?>
                                        <div class="text-danger mt-2"><?= htmlspecialchars($errors['requert_month']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($errors['requert_day'])): ?>
                                        <div class="text-danger mt-2"><?= htmlspecialchars($errors['requert_day']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">督促メール送信日時( 1回目 )</label>
                                    <div class="d-flex align-items-center">
                                        <input type="number" name="first_reminder_month" class="form-control sp-w-35 w-25" value="<?= htmlspecialchars(empty($email_send_setting) ? '' : $email_send_setting['first_reminder_month']) ?>"><span class="ps-2 pe-2">月</span>
                                        <input type="number" name="first_reminder_day" class="form-control sp-w-35 w-25" value="<?= htmlspecialchars(empty($email_send_setting) ? '' : $email_send_setting['first_reminder_day']) ?>"><span class="ps-2 pe-2">日</span>
                                    </div>
                                    <?php if (!empty($errors['first_reminder_month'])): ?>
                                        <div class="text-danger mt-2"><?= htmlspecialchars($errors['first_reminder_month']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($errors['first_reminder_day'])): ?>
                                        <div class="text-danger mt-2"><?= htmlspecialchars($errors['first_reminder_day']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">督促メール送信日時( 2回目 )</label>
                                    <div class="d-flex align-items-center">
                                        <input type="number" name="second_reminder_month" class="form-control sp-w-35 w-25" value="<?= htmlspecialchars(empty($email_send_setting) ? '' : $email_send_setting['second_reminder_month']) ?>"><span class="ps-2 pe-2">月</span>
                                        <input type="number" name="second_reminder_day" class="form-control sp-w-35 w-25" value="<?= htmlspecialchars(empty($email_send_setting) ? '' : $email_send_setting['second_reminder_day']) ?>"><span class="ps-2 pe-2">日</span>
                                    </div>
                                    <?php if (!empty($errors['second_reminder_month'])): ?>
                                        <div class="text-danger mt-2"><?= htmlspecialchars($errors['second_reminder_month']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($errors['second_reminder_day'])): ?>
                                        <div class="text-danger mt-2"><?= htmlspecialchars($errors['second_reminder_day']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">除名期日</label>
                                    <div class="d-flex align-items-center">
                                        <input type="number" name="expulsion_month" class="form-control sp-w-35 w-25" value="<?= htmlspecialchars(empty($email_send_setting) ? '' : $email_send_setting['expulsion_month']) ?>"><span class="ps-2 pe-2">月</span>
                                        <input type="number" name="expulsion_day" class="form-control sp-w-35 w-25" value="<?= htmlspecialchars(empty($email_send_setting) ? '' : $email_send_setting['expulsion_day']) ?>"><span class="ps-2 pe-2">日</span>
                                    </div>
                                    <?php if (!empty($errors['expulsion_month'])): ?>
                                        <div class="text-danger mt-2"><?= htmlspecialchars($errors['expulsion_month']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($errors['expulsion_day'])): ?>
                                        <div class="text-danger mt-2"><?= htmlspecialchars($errors['expulsion_day']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex w-100 align-items-center justify-content-end">
                                    <button id="submit" class="btn btn-primary mt-3 mb-3 ms-auto">更新</button>
                                </div>
                                <div class="d-flex w-100 align-items-center justify-content-end">
                                    <button id="request" class="btn btn-secondary mt-3 mb-3 email-button">[テスト送信]請求メール</button>
                                    <button id="reminder1" class="btn btn-secondary mt-3 mb-3 email-button">[テスト送信]催促1</button>
                                    <button id="reminder2" class="btn btn-secondary mt-3 mb-3 email-button">[テスト送信]催促2</button>
                                    <button id="expulsion" class="btn btn-secondary mt-3 mb-3 email-button">[テスト送信]除名</button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php } ?>
            </main>
        </div>
    </div>
    <script src="/custom/admin/public/js/app.js"></script>
</body>

</html>
<script>
    // 検索フォームから検索時URLを動的に変更
    $(document).ready(function() {
        const params = new URLSearchParams(window.location.search);
        const currentPage = $('input[name="page"]').val();
        params.set('page', currentPage);
        history.replaceState(null, '', window.location.pathname + '?' + params.toString());

        // CSV出力ボタンのクリックイベント
        $('#csv_button').on('click', function(event) {
            $('#csvExportForm').submit();
        });
    });
    $('#search-button').on('click', function(event) {
        $('input[name="page"]').val(1);
    });
    // ページネーション押下時
    $(document).on("click", ".paginate_button a", function(e) {
        e.preventDefault();
        const nextPage = $(this).data("page");
        $('input[name="page"]').val(nextPage);
        $('#search-form').submit();
    });
    //ダミーメール送付
    $(document).ready(function() {
        $('.email-button').click(function() {
            let action = $(this).attr('id');
            sendEmail(action, $(this));
        });

        function sendEmail(action, button) {
            button.prop('disabled', true);
            let originalText = button.text();
            button.text('送信中...');
            $.ajax({
                url: '/custom/admin/app/Views/management/send_mail.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    action: action
                }),
                success: function(response) {
                    alert('メールが送信されました: ' + action);
                },
                error: function(xhr, status, error) {
                    alert('エラーが発生しました。');
                },
                complete: function() {
                    button.prop('disabled', false);
                    button.text(originalText);
                }
            });
        }
    });
</script>