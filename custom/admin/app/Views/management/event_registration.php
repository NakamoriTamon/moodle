<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/management/event_registration_controller.php');
include($CFG->dirroot . '/custom/admin/app/Views/common/header.php');

$event_registration_controller = new EventRegistrationController();
$result_list = $event_registration_controller->index();

// バリデーションエラー
$errors   = $_SESSION['errors']   ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

// 情報取得
$count = 0;
$category_list = $result_list['category_list'] ?? [];
$event_list = $result_list['event_list']  ?? [];
$application_list = $result_list['application_list'];
$course_list = $result_list['course_list'] ?? [];

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
                    <p class="title header-title ms-4 fs-4 fw-bold mb-0">イベント登録情報一覧</p>
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
                        <div class="card-body p-025 p-055">
                            <form id="form" method="POST" action="/custom/admin/app/Views/management/event_registration.php" class="w-100">
                                <input type="hidden" name="page" value="<?= $page ?>">
                                <div class="d-flex sp-block justify-content-between">
                                    <div class="mb-3 w-100">
                                        <label class="form-label" for="notyf-message">カテゴリー</label>
                                        <select name="category_id" class="form-control">
                                            <option value="">すべて</option>
                                            <?php foreach ($category_list as $category) { ?>
                                                <option value="<?= $category['id'] ?>" <?= isSelected($category['id'], $old_input['category_id'] ?? null, null) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="ms-3 sp-ms-0 mb-3 w-100">
                                        <label class="form-label" for="notyf-message">開催ステータス</label>
                                        <select name="event_status_id" class="form-control">
                                            <option value="">すべて</option>
                                            <?php foreach ($display_status_list as $key => $event_status) { ?>
                                                <option value=<?= $key ?> <?= isSelected($key, $old_input['event_status_id'] ?? null, null) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($event_status) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex sp-block justify-content-between">
                                    <div class="mb-3 w-100">
                                        <label class="form-label" for="notyf-message">イベント名</label>
                                        <select name="event_id" class="form-control">
                                            <option value="" selected disabled>未選択</option>
                                            <?php foreach ($event_list as $event): ?>
                                                <option value="<?= htmlspecialchars($event['id'], ENT_QUOTES, 'UTF-8') ?>"
                                                    <?= isSelected($event['id'], $old_input['event_id'] ?? null, null) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="sp-ms-0 ms-3 mb-3 w-100">
                                        <label class="form-label" for="notyf-message">回数</label>
                                        <div class="d-flex align-items-center">
                                            <select name="course_no" class="form-control w-100" <?= $result_list['is_single'] ? 'disabled' : '' ?>>
                                                <option value="">未選択</option>
                                                <?php foreach ($course_list as $course) { ?>
                                                    <option value=<?= $course['no'] ?>
                                                        <?= isSelected($course['no'], $old_input['course_no'] ?? null, null) ? 'selected' : '' ?>>
                                                        <?= "第" . $course['no'] . "回" ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex sp-block justify-content-between">
                                    <div class="mb-3 w-100">
                                        <label class="form-label" for="notyf-message">フリーワード</label>
                                        <input id="notyf-message" name="keyword" type="text" class="form-control" value="<?= htmlspecialchars(isset($old_input['keyword']) ? $old_input['keyword'] : '', ENT_QUOTES, 'UTF-8') ?>" placeholder="田中 翔太">
                                    </div>
                                    <div class="mb-3 w-100"></div>
                                </div>
                                <!-- <hr> -->
                                <div class="d-flex w-100">
                                    <button id="search-button" class="btn btn-primary mb-3 me-0 ms-auto">検索</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php if (!empty($application_list)) { ?>
                        <div id="result_card" class="col-12 col-lg-12">
                            <div class="card min-70vh">
                                <div class="card-body p-0">
                                    <form method="POST" action="/custom/admin/app/Controllers/management/event_registration_upsert_controller.php">
                                        <div class="d-flex w-100 align-items-center justify-content-between mt-3">
                                            <div></div>
                                            <div class="d-flex align-items-center button-div mr-025">
                                                <button type="submit" class="btn btn-primary mt-3 mb-3 me-3 d-flex justify-content-center align-items-center">
                                                    更新
                                                </button>
                                                <button id="csv_button" type="button" class="btn btn-primary mt-3 mb-3 d-flex justify-content-center align-items-center">
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
                                                        <?php if(!$result_list['is_single']): ?><th class="ps-4 pe-4">講座回数</th><?php endif; ?>
                                                        <th class="ps-4 pe-4">会員番号</th>
                                                        <th class="ps-4 pe-4">ユーザー名</th>
                                                        <th class="ps-4 pe-4">メールアドレス</th>
                                                        <th class="ps-4 pe-4">年齢</th>
                                                        <th class="ps-4 pe-4">備考</th>
                                                        <th class="ps-4 pe-4">決済方法</th>
                                                        <th class="ps-4 pe-4">決済状況</th>
                                                        <th class="ps-4 pe-4">決済日</th>
                                                        <th class="ps-4 pe-4">申込日</th>
                                                        <th class="ps-4 pe-4">参加状態</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($application_list as $key => $application) { ?>
                                                        <tr>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($application['id']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($application['event_name']) ?></td>
                                                            <?php if(!$result_list['is_single']): ?><td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($application['no']) ?></td><?php endif; ?>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($application['user_id']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($application['name']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($application['email']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($application['age'] ?? '') ?></td>
                                                            <td class="ps-4 pe-4 text-wrap break-cell"><?= htmlspecialchars($application['note']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($application['payment_type']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap <?= $application['is_paid'] == '未決済' ? 'text-danger' : '' ?>">
                                                                <?= htmlspecialchars($application['is_paid']) ?>
                                                            </td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($application['payment_date']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($application['application_date']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap">
                                                                <select name="participation_kbn[<?= htmlspecialchars($application['id']) ?>]" class="form-control min-100">
                                                                    <option value="">参加前</option>
                                                                    <?php foreach ($is_participation_list as $key => $is_participation) { ?>
                                                                        <option value=<?= $key ?>
                                                                            <?= $key == $application['participation_kbn'] ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($is_participation) ?>
                                                                        </option>
                                                                    <?php } ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                    
                                    <!-- 非表示のform（CSV出力用） -->
                                    <form id="csvExportForm" method="POST" action="/custom/admin/app/Controllers/management/event_registration_export_controller.php">
                                        <input type="hidden" name="keyword" value="<?= htmlspecialchars(isset($old_input['keyword']) ? $old_input['keyword'] : '', ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="category_id" value="<?= $old_input['category_id'] ?? '' ?>">
                                        <input type="hidden" name="event_status_id" value="<?= $old_input['event_status_id'] ?? '' ?>">
                                        <input type="hidden" name="event_id" value="<?= $old_input['event_id'] ?? '' ?>">
                                        <input type="hidden" name="course_no" value="<?= $old_input['course_no'] ?? '' ?>">
                                    </form>
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

        // 検索
        $('select[name="category_id"], select[name="event_status_id"], select[name="event_id"], select[name="course_no"]').change(function() {
            $("#form").submit();
        });
        $('#search-button').on('click', function(event) {
            $('input[name="page"]').val(1);
        });
        // ページネーション押下時
        $(document).on("click", ".paginate_button a", function(e) {
            e.preventDefault();
            const nextPage = $(this).data("page");
            $('input[name="page"]').val(nextPage);
            $('#form').submit();
        });
        
        // CSV出力ボタン押下時
        $('#csv_button').on('click', function(event) {
            $('#csvExportForm').submit();
        });
    });
</script>