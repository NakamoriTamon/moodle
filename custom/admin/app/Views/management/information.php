<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/management/information_controller.php');
include($CFG->dirroot . '/custom/admin/app/Views/common/header.php');

$information_controller = new InformationController();
$result_list = $information_controller->index();
$sample_list = $result_list['sample_list'];

// バリデーションエラー
$errors   = $_SESSION['errors']   ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

// ページネーション
$total_count = $result_list['total_count'] ?? 0;
$per_page = $result_list['per_page'] ?? 1;
$current_page = $_POST['page'] ?? $_GET['page'] ?? 1;
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
                    <p class="title header-title ms-4 fs-4 fw-bold mb-0">お知らせ一覧</p>
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

            <main class="content">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="card">
                                <div class="card-body p-055 p-025 sp-block d-flex align-items-bottom">
                                    <form id="form" method="POST" action="/custom/admin/app/Views/management/information.php" class="w-100">
                                        <input type="hidden" name="page" id="page" value="<?= htmlspecialchars($current_page) ?>">
                                        <div class="d-flex sp-block justify-content-between">
                                            <div class="mb-3 w-100">
                                                <label class="form-label" for="notyf-message">フリーワード</label>
                                                <input id="notyf-message" name="keyword" type="text" class="form-control" value="<?= htmlspecialchars(isset($old_input['keyword']) ? $old_input['keyword'] : '', ENT_QUOTES, 'UTF-8') ?>" placeholder="田中 翔太">
                                            </div>
                                            <div class="mb-3 w-100"></div>
                                        </div>
                                        <div class="d-flex justify-content-end ms-auto">
                                            <button class="btn btn-primary me-0 search-button" type="submit" name="search" value="1" onclick="document.getElementById('page').value='1';">検索</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card min-70vh">
                    <div class="col-12 col-lg-12">
                        <div class="card-body p-0">

                            <div class="card-body p-0">
                                <div class="d-flex w-100 align-items-center justify-content-between mt-3">
                                    <div></div>
                                    <div class="d-flex align-items-center button-div mr-025">
                                        <div></div>
                                        <button type="button" id="upsert_button" class="btn btn-primary mt-3 mb-3">新規登録</button>
                                    </div>
                                </div>
                                <div class="card m-auto mb-5 w-95">
                                    <table class="table table-responsive table-striped table_list">
                                        <thead>
                                            <tr>
                                                <th class="ps-4 pe-4 text-nowrap">ID</th>
                                                <th class="ps-4 pe-4 text-nowrap">件名</th>
                                                <th class="ps-4 pe-4 text-nowrap">本文</th>
                                                <th class="ps-4 pe-4 text-nowrap">掲載開始日時</th>
                                                <th class="ps-4 pe-4 text-nowrap">掲載終了日時</th>
                                                <th class="text-center ps-4 pe-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sample_list as $key => $sample) { ?>
                                                <tr>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($sample['id']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($sample['title']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($sample['body']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($sample['start_date']) ?></td>
                                                    <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($sample['end_date']) ?></td>
                                                    <td class="text-center ps-4 pe-4 text-nowrap">
                                                        <a href="/custom/admin/app/Views/management/information_upsert.php?id=<?= $sample['id'] ?>" class="me-3"><i class="align-middle" data-feather="edit-2"></i></a>
                                                        <a class="delete-link" data-id="<?= $sample['id'] ?>" data-name="<?= $sample['title'] ?>"><i class=" align-middle" data-feather="trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- 削除確認モーダル -->
                                <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
                                    <form id="deleteForm" method="POST" action="" enctype="multipart/form-data">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">削除確認</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mt-3">「<span id="del_event_name">お知らせ件名</span>」を削除します。本当によろしいですか</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <input type="hidden" id="del_event_id" name="del_event_id" value="">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                                                    <button type="button" class="btn btn-danger" id="confirmDeleteButton">削除</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="d-flex">
                                <div class="dataTables_paginate paging_simple_numbers ms-auto mr-025" id="datatables-buttons_paginate">
                                    <ul class="pagination">
                                        <?php
                                        $total_pages = ceil($total_count / $per_page);

                                        // 表示するページ範囲の計算
                                        if ($current_page <= 2) {
                                            // 1または2ページ目の場合は1から3まで表示
                                            $start_page = 1;
                                            $end_page = min($total_pages, 3);
                                        } elseif ($current_page >= $total_pages - 1) {
                                            // 最後または最後から2番目のページの場合は最後3ページを表示
                                            $start_page = max(1, $total_pages - 2);
                                            $end_page = $total_pages;
                                        } else {
                                            // それ以外は現在のページを中心に前後1ページずつ表示
                                            $start_page = $current_page - 1;
                                            $end_page = $current_page + 1;
                                        }

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
                </div>
            </main>
        </div>
    </div>
    <script src="/custom/admin/public/js/app.js"></script>
</body>

</html>
<script>
    $(document).ready(function() {
        // ページネーション押下時
        $('#upsert_button').on('click', function() {
            window.location.href = '/custom/admin/app/Views/management/information_upsert.php';
        });

        let selectedId;
        let selectedName;
        // 削除リンクがクリックされたとき
        $('.delete-link').on('click', function(event) {
            event.preventDefault();
            selectedId = $(this).data('id');
            selectedName = $(this).data('name');
            $('#del_event_id').val(selectedId);
            $('#del_event_name').text(selectedName);
            $('#confirmDeleteModal').modal('show');
        });
        // モーダル内の削除ボタンがクリックされたとき
        $('#confirmDeleteButton').on('click', function() {
            $('#deleteForm').submit();
            $('#confirmDeleteModal').modal('hide');
            $(`.delete-link[data-id="${selectedId}"]`).closest('li').remove();
        });
    });
</script>