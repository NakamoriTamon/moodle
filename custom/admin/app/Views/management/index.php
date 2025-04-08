<?php

require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
include($CFG->dirroot . '/custom/admin/app/Views/common/header.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/management/ManagementController.php');

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
                    <p class="title header-title ms-4 fs-4 fw-bold mb-0">管理者一覧</p>
                    <p class="title mb-0"></p>
                    <ul class="navbar-nav navbar-align">
                        <li class="nav-item dropdown">
                            <a class="nav-icon pe-md-0 dropdown-toggle d-flex" href="#" data-bs-toggle="dropdown">
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
                                    <form id="form" method="POST" action="/custom/admin/app/Views/management/index.php" class="w-100">
                                        <div id="keyword_div" class="mb-4 w-100">
                                            <label class="form-label" for="notyf-message">フリーワード</label>
                                            <input id="keyword" name="keyword" type="text" class="form-control" placeholder="田中 翔太">
                                        </div>
                                        <div class="d-flex justify-content-end ms-auto">
                                            <button class="btn btn-primary me-0 search-button" type="submit" name="search" value="1">検索</button>
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
                            <form method="POST" action="/custom/admin/app/Controllers/management/RoleUpdateController.php" onsubmit="return confirmUpdate()">
                                <div class="d-flex w-100 mt-3"><button id="submit" class=" btn btn-primary mt-3 mb-3 ms-auto">更新</button></div>
                                <div class="card m-auto mb-5 w-95">
                                    <table class="table table-responsive table-striped table_list" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="ps-4 pe-4 min-130">ID</th>
                                                <th class="ps-4 pe-4 w-25">担当者名</th>
                                                <th class="ps-4 pe-4 w-25">所属部局</th>
                                                <th class="ps-4 pe-4 w-35">メールアドレス</th>
                                                <th class="ps-4 pe-4 w-35 min-200">権限</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($admins as $index => $admin): ?>
                                                <tr>
                                                    <td class="ps-4 pe-4"><?= htmlspecialchars($admin['id']) ?></td>
                                                    <td class="ps-4 pe-4"><?= htmlspecialchars($admin['name']) ?></td>
                                                    <td class="ps-4 pe-4"><?= htmlspecialchars($admin['department']) ?></td>
                                                    <td class="ps-4 pe-4"><?= htmlspecialchars($admin['email']) ?></td>
                                                    <td class="ps-4 pe-4">
                                                        <input type="hidden" name="users[<?= $index ?>][id]" value="<?= htmlspecialchars($admin['id']) ?>">
                                                        <select name="users[<?= $index ?>][role_id]" class="form-control">
                                                            <?php foreach (ROLES as $key => $role): ?>
                                                                <option value=<?= htmlspecialchars($key) ?> <?php if ($key == $admin['role_id']): ?>selected<?php endif; ?>><?= htmlspecialchars($role) ?></option>
                                                            <?php endforeach ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex">
                                    <div class="dataTables_paginate paging_simple_numbers ms-auto mr-025" id="datatables-buttons_paginate">
                                        <ul class="pagination">
                                            <?php if ($currentPage >= 1 && $totalCount > 10): ?>
                                                <li class="paginate_button page-item previous" id="datatables-buttons_previous"><a href="?page=<?= intval($currentPage) - 1 ?>" aria-controls="datatables-buttons" class="page-link">Previous</a></li>
                                            <?php endif; ?>
                                            <?php for ($i = 1; $i <= ceil($totalCount / 10); $i++): ?>
                                                <li class="paginate_button page-item <?= $i == $currentPage ? 'active' : '' ?>"><a href="?page=<?= $i ?>" aria-controls="datatables-buttons" class="page-link"><?= $i ?></a></li>
                                            <?php endfor; ?>
                                            <?php if ($currentPage >= 0 && $totalCount > 10): ?>
                                                <li class="paginate_button page-item next" id="datatables-buttons_next"><a href="?page=<?= intval($currentPage) + 1 ?>" aria-controls="datatables-buttons" class="page-link">Next</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </form>
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
    function confirmUpdate() {
        return confirm("権限を更新します。本当によろしいですか？");
    }

    // モック用アラート　本番時は消してください
    $('#submit').on('click', function(event) {
        sessionStorage.setItem('alert', 'aaasss');
        setTimeout(() => {
            location.reload();
        }, 50);
    });
</script>