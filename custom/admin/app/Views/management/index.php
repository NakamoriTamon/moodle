<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php'); ?>

<body id="management" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
    <div class="wrapper">
        <?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <div class="navbar-collapse collapse">
                    <p class="title ms-4 fs-4 fw-bold mb-0">管理者一覧</p>
                    <p class="title mb-0"></p>
                    <ul class="navbar-nav navbar-align">
                        <li class="nav-item dropdown">
                            <a class="nav-icon pe-md-0 dropdown-toggle d-flex" href="#" data-bs-toggle="dropdown">
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
                            <div class="d-flex w-100 mt-3"><button class="btn btn-primary mt-3 mb-3 ms-auto">更新</button></div>
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
                                        <tr>
                                            <td class="ps-4 pe-4">1</td>
                                            <td class="ps-4 pe-4">21世紀懐徳堂</td>
                                            <td class="ps-4 pe-4">example01@gmail.com</td>
                                            <td class="ps-4 pe-4">
                                                <select name="category_id" class="form-control">
                                                    <option value=1 selected="">管理者</option>
                                                    <option value=2>担当者</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">2</td>
                                            <td class="ps-4 pe-4">理学部</td>
                                            <td class="ps-4 pe-4">example02@gmail.com</td>
                                            <td class="ps-4 pe-4">
                                                <select name="category_id" class="form-control">
                                                    <option value=1>管理者</option>
                                                    <option value=2 selected>担当者</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 pe-4">3</td>
                                            <td class="ps-4 pe-4">博・適事務室</td>
                                            <td class="ps-4 pe-4">example03@gmail.com</td>
                                            <td class="ps-4 pe-4">
                                                <select name="category_id" class="form-control">
                                                    <option value=1>管理者</option>
                                                    <option value=2 selected>担当者</option>
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