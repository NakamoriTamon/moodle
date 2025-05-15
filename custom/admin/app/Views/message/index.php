<?php
require_once('/var/www/html/moodle/config.php');
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/message/message_select_controller.php');

$message_select_controller = new MessageSelectController();
$all_result_list = $message_select_controller->index();

$result_list = $all_result_list['data'] ?? [];
$select_kbn_list = $all_result_list['kbn_id_list'] ?? [];
$kbn_id = $result_list['kbn_id'] ?? '';

// 情報取得
$category_list = $result_list['category_list'] ?? [];
$event_list = $result_list['event_list']  ?? [];
$user_list = $result_list['user_list']  ?? [];
$header_list = $result_list['header_list'] ?? [];
$course_list = $result_list['course_list'] ?? [];
$application_list = $result_list['application_list'] ?? [];
$tekijuku_commemoration_list = $result_list['tekijuku_commemoration_list'] ?? [];
$mail_to_list = $result_list['mail_to_list'] ?? [];
$event_kbn = $result_list['event_kbn'];

// ページネーション
$total_count = $result_list['total_count'] ?? 0;
$per_page = $result_list['per_page'] ?? 1;
$current_page = $result_list['current_page'] ?? 1;
$page = $result_list['page'] ?? 1;

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
                        <div class="card-body p-055">
                            <form id="form" method="POST" action="/custom/admin/app/Views/message/index.php" class="w-100">
                                <input type="hidden" name="page" value="<?= $page ?>">
                                <div class="mb-3">
                                    <label class="form-label" for="notyf-message">対象区分</label>
                                    <select id="kbn_id" name="kbn_id" class="form-control">
                                        <option value=''>未選択</option>
                                        <?php foreach ($select_kbn_list as $key => $kbn) { ?>
                                            <option value=<?= $key ?> <?= isSelected($key, $old_input['kbn_id'] ?? null, null) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($kbn) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div id="even-form" class="d-none">
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
                                        <div class="sp-ms-0 ms-3 mb-3 w-100">
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
                                                <option value=''>未選択</option>
                                                <?php foreach ($event_list as $event): ?>
                                                    <option value="<?= htmlspecialchars($event['id'], ENT_QUOTES, 'UTF-8') ?>"
                                                        <?= isSelected($event['id'], $old_input['event_id'] ?? null, null) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="sp-ms-0 mb-3 ms-3 w-100">
                                            <label class="form-label" for="notyf-message">回数</label>
                                            <select name="course_no" class="form-control w-100" <?= isset($result_list['is_single']) && $result_list['is_single'] ? 'disabled' : '' ?>>
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
                                <div id="keyword_div" class="mb-4 w-100">
                                    <label class="form-label" for="notyf-message">フリーワード</label>
                                    <input id="keyword" name="keyword" type="text" class="form-control" value="<?= $old_input['keyword']  ?>" placeholder="田中 翔太">
                                </div>
                                <!-- <hr> -->
                                <div class="d-flex w-100">
                                    <button id="search-button" class=" btn btn-primary mb-3 me-0 ms-auto">検索</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php if (!empty($user_list) || !empty($application_list) || !empty($tekijuku_commemoration_list)) { ?>
                        <form method="POST" action="/custom/admin/app/Controllers/message/message_controller.php">
                            <div class="card min-70vh">
                                <div class="card-body p-0">
                                    <div class="d-flex w-100 align-items-center justify-content-end mt-3">
                                        <div class="mt-4"></div>
                                    </div>
                                    <div class="card m-auto mb-5 w-95">
                                        <?php if ($old_input['kbn_id'] == DM_SEND_KBN_EVENT) { ?>
                                            <table class="table table-responsive table-striped table_list" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <?php foreach ($header_list as $header) { ?>
                                                            <th class="ps-4 pe-4 text-nowrap"><?= $header ?></th>
                                                        <?php } ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($application_list as $application): ?>
                                                        <tr>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= $application['id'] ?></td>
                                                            <td class="ps-4 pe-4"><?= htmlspecialchars($application['event_name']) ?></td>
                                                            <?php if ($event_kbn == PLURAL_EVENT) { ?>
                                                                <td class="ps-4 pe-4"><?= htmlspecialchars($application['no']) ?></td>
                                                            <?php } ?>
                                                            <td class="ps-4 pe-4"><?= htmlspecialchars($application['user_id']) ?></td>
                                                            <td class="ps-4 pe-4"><?= htmlspecialchars($application['name']) ?></td>
                                                            <td class="ps-4 pe-4"><?= htmlspecialchars($application['email']) ?></td>
                                                            <td class="ps-4 pe-4"><?= htmlspecialchars($application['payment_type']) ?></td>
                                                            <td class="ps-4 pe-4"><?= htmlspecialchars($application['is_paid']) ?></td>
                                                            <td class="ps-4 pe-4"><?= htmlspecialchars($application['payment_date'] ?? '') ?></td>
                                                            <td class="ps-4 pe-4"><?= htmlspecialchars($application['application_date']) ?>
                                                        </tr>
                                                        <input type="hidden" name="mail_to_list[]" value="<?= $application['email'] ?>">
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php } else if ($old_input['kbn_id'] == DM_SEND_KBN_TEKIJUKU) { ?>
                                            <table class="table table-responsive table-striped table_list" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <?php foreach ($header_list as $header) { ?>
                                                            <th class="ps-4 pe-4 text-nowrap"><?= $header ?></th>
                                                        <?php } ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($tekijuku_commemoration_list as $tekijuku_commemoration): ?>
                                                        <?php
                                                        $number = str_pad($tekijuku_commemoration['number'], 8, '0', STR_PAD_LEFT);
                                                        $number = substr($number, 0, 4) . ' ' . substr($number, 4);
                                                        $menu = $tekijuku_commemoration['type_code'] === 1 ? '普通会員' : '賛助会員';
                                                        $created_date = new DateTime($tekijuku_commemoration['created_at']);
                                                        $paid_date = '';
                                                        if (!empty($tekijuku_commemoration['paid_date_history'])) {
                                                            $paid_date = new DateTime($tekijuku_commemoration['paid_date_history']);
                                                            $paid_date = $paid_date->format("Y年n月j日");
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($number) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($tekijuku_commemoration['name']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($tekijuku_commemoration['email']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($menu) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($tekijuku_commemoration['unit']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($tekijuku_commemoration['department']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($tekijuku_commemoration['major']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($tekijuku_commemoration['official']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($tekijuku_commemoration['display_depo']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($payment_select_list[$tekijuku_commemoration['payment_method']]) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($paid_date) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($created_date->format("Y年n月j日")) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($tekijuku_commemoration['old_number'] ?? '') ?></td>
                                                        </tr>
                                                        <input type="hidden" name="mail_to_list[]" value="<?= $tekijuku_commemoration['email'] ?>">
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php } else if ($old_input['kbn_id'] == DM_SEND_KBN_ALL) { ?>
                                            <table class="table table-responsive table-striped table_list" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <?php foreach ($header_list as $header) { ?>
                                                            <th class="ps-4 pe-4 text-nowrap"><?= $header ?></th>
                                                        <?php } ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($user_list as $user): ?>
                                                        <tr>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= $user['user_id'] ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($user['name']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($user['kana']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($user['birthday']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($user['city']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($user['email']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($user['phone']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($user['gurdian_name']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($user['gurdian_email']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($user['gurdian_phone']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($user['is_tekijuku']) ?></td>
                                                            <td class="ps-4 pe-4 text-nowrap"><?= htmlspecialchars($user['pay_method']) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php } ?>
                                    </div>
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
                                            <textarea name="mail_title" class="form-control w-100" required><?= htmlspecialchars($mail_title) ?></textarea>
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
                            <?php foreach ($mail_to_list as $mail): ?>
                                <input type="hidden" name="mail_to_list[]" value="<?= htmlspecialchars($mail, ENT_QUOTES, 'UTF-8') ?>">
                            <?php endforeach; ?>
                        </form>
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
        let kbn_id = $('select[name="kbn_id"]').val();
        if (kbn_id == 1) {
            $('#even-form').removeClass('d-none');
        } else {
            $('#even-form').addClass('d-none');
        }

        $('select[name="kbn_id"]').on('change', function(event) {
            if ($(this).val() == 1) {
                $('#even-form').removeClass('d-none');
            } else {
                $('#even-form').addClass('d-none');
            }
        });
        // 検索フォームから検索時URLを動的に変更
        const params = new URLSearchParams(window.location.search);
        const currentPage = $('input[name="page"]').val();
        params.set('page', currentPage);
        history.replaceState(null, '', window.location.pathname + '?' + params.toString());

        // 検索
        $('select[name="kbn_id"], select[name="category_id"], select[name="event_status_id"], select[name="event_id"], select[name="course_no"]').change(function() {
            $('input[name="page"]').val(1);
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
        $('#csv_button').on('click', function(event) {
            $('#csvExportForm').submit();
        });
    });
</script>