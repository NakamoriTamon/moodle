<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/management/user_registration_controller.php');
include($CFG->dirroot . '/custom/admin/app/Views/common/header.php');

$user_registration_controller = new UserRegistrationController();
$result_list = $user_registration_controller->index();
$data_list = $result_list['data_list'];

// バリデーションエラー
$errors   = $_SESSION['errors']   ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

// ページネーション
$total_count = $result_list['total_count'];
$per_page = $result_list['per_page'];
$current_page = $result_list['current_page'];
$page = $result_list['page'];
?>

<body id="management" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
    <div class="wrapper">
        <?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <div class="navbar-collapse collapse">
                    <a class="sidebar-toggle js-sidebar-toggle">
                        <i class="hamburger align-self-center"></i>
                    </a>
                    <p class="title header-title ms-4 fs-4 fw-bold mb-0">ユーザー情報一覧</p>
                    <p class="title mb-0"></p>
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

            <form id="form" method="POST" action="/custom/admin/app/Views/management/user_registration.php">
                <input type="hidden" name="page" value="<?= $page ?>">
            </form>

            <main class="content">
                <div class="col-12 col-lg-12">
                    <div class="card min-70vh">
                        <form method="POST" action="/custom/admin/app/Controllers/management/user_registration_upsert_controller.php" class="w-100">
                            <input type="hidden" name="page" value="<?= $page ?>">
                            <div class="card-body p-0">
                                <div class="d-flex w-100 align-items-center justify-content-between mt-3">
                                    <div></div>
                                    <div class="d-flex align-items-center button-div mr-025">
                                        <button class="btn btn-primary mt-3 mb-3 me-2 d-flex justify-content-center align-items-center">
                                            <i class="align-middle me-1" data-feather="download"></i>CSV出力
                                        </button>
                                        <button id="submit" class="btn btn-primary mt-3 mb-3">更新</button>
                                    </div>
                                </div>
                                <div class="card m-auto mb-5 w-95">
                                    <table class="table table-responsive table-striped table_list">
                                        <thead>
                                            <tr>
                                                <th class="ps-4 pe-4 text-nowrap">ユーザーID</th>
                                                <th class="ps-4 pe-4 text-nowrap">氏名</th>
                                                <th class="ps-4 pe-4 text-nowrap">フリガナ</th>
                                                <th class="ps-4 pe-4 text-nowrap">生年月日</th>
                                                <th class="ps-4 pe-4 text-nowrap">住所</th>
                                                <th class="ps-4 pe-4 text-nowrap">メールアドレス</th>
                                                <th class="ps-4 pe-4 text-nowrap">電話番号</th>
                                                <th class="ps-4 pe-4 text-nowrap">保護者氏名</th>
                                                <th class="ps-4 pe-4 text-nowrap">保護者メールアドレス</th>
                                                <th class="ps-4 pe-4 text-nowrap">保護者電話番号</th>
                                                <th class="ps-4 pe-4 text-nowrap">適塾記念会入会状況</th>
                                                <th class="ps-4 pe-4 text-nowrap">支払方法</th>
                                                <th class="w-170 ps-4 pe-4 text-nowrap">アカウント承認設定</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data_list as $key => $data) { ?>
                                                <tr>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($data['user_id']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($data['name']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($data['kana']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($data['birthday']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($data['city']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($data['email']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($data['phone']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($data['gurdian_name']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($data['gurdian_email']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($data['gurdian_phone']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($data['is_tekijuku']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($data['pay_method']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap">
                                                        <select name="is_apply[<?= htmlspecialchars($data['id']) ?>]" class="form-control">
                                                            <?php foreach ($is_apply_list as $key => $is_apply) { ?>
                                                                <option value=<?= $key ?> <?= $key == $data['is_apply'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($is_apply) ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
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
            </main>
        </div>
    </div>
    <script src="/custom/admin/public/js/app.js"></script>
</body>

</html>
<script>
    $(document).ready(function() {
        // 検索フォームから検索時URLを動的に変更
        const params = new URLSearchParams(window.location.search);
        const currentPage = $('input[name="page"]').val();
        params.set('page', currentPage);
        history.replaceState(null, '', window.location.pathname + '?' + params.toString());

        // ページネーション押下時
        $(document).on("click", ".paginate_button a", function(e) {
            e.preventDefault();
            const nextPage = $(this).data("page");
            $('input[name="page"]').val(nextPage);
            $('#form').submit();
        });
    });
</script>