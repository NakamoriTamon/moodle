<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php'); ?>

<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
    <main class="d-flex w-100 h-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">

                        <div class="text-center mt-4">
                            <h1 class="h2">アカウント作成</h1>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="m-sm-3">
                                    <form action="/custom/admin/app/Controllers/login/sign_up_controller.php" method="post">
                                        <div class="mb-3">
                                            <label class="form-label">担当者名</label>
                                            <input class="form-control form-control-lg" type="text" name="lastname" placeholder="苗字" />
                                            <input class="form-control form-control-lg" type="text" name="firstname" placeholder="名前" />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">メールアドレス</label>
                                            <input class="form-control form-control-lg" type="email" name="email" placeholder="Enter your email" />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">パスワード</label>
                                            <input class="form-control form-control-lg" type="password" name="password" placeholder="Enter password" />
                                        </div>
                                        <div class="d-grid gap-2 mt-3">
                                            <button type="submit" class="btn btn-lg btn-primary">サインアップ</button>
                                            <a href="/custom/admin/app/Views/login/login.php" class="btn btn-lg btn-primary">ログイン画面に戻る</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>