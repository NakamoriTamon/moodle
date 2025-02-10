<?php
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/management/ManagementController.php');
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
                    <div class="card min-70vh">
                        <div class="card-body p-0">
                            <div class="d-flex w-100 mt-3"><button id="submit" class=" btn btn-primary mt-3 mb-3 ms-auto">更新</button></div>
                            <div class="card m-auto mb-5 w-95">
                                <table class="table table-responsive table-striped table_list" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th class="ps-4 pe-4 min-130">ID</th>
                                            <th class="ps-4 pe-4 w-35">担当者名</th>
                                            <th class="ps-4 pe-4 w-35">メールアドレス</th>
                                            <th class="ps-4 pe-4 min-140">権限</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($admins as $admin): ?>
                                            <tr>
                                                <td class="ps-4 pe-4"><?= htmlspecialchars($admin['id']) ?></td>
                                                <td class="ps-4 pe-4"><?= htmlspecialchars($admin['lastname'] . $admin['firstname']) ?></td>
                                                <td class="ps-4 pe-4"><?= htmlspecialchars($admin['email']) ?></td>
                                                <td class="ps-4 pe-4">
                                                    <select name="category_id" class="form-control">
                                                        <?php foreach(ROLES as $key => $role): ?>
                                                            <option value=<?= htmlspecialchars($key) ?> <?php if($key == $admin['role_id']): ?>selected<?php endif; ?>><?= htmlspecialchars($role) ?></option>
                                                        <?php endforeach ?>
                                                    </select>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
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

<script>
    // モック用アラート　本番時は消してください
    $('#submit').on('click', function(event) {
        sessionStorage.setItem('alert', 'aaasss');
        setTimeout(() => {
            location.reload();
        }, 50);
    });
</script>