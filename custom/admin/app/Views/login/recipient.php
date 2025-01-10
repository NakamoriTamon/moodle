<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php'); ?>

<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
    <main class="d-flex w-100 h-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">

                        <div class="text-center mt-4">
                            <h1 class="h2">アカウントの確認</h1>
                            <p class="">
                                再設定先メールアドレスを入力してください
                            </p>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="m-sm-3">
                                    <form action="/custom/admin/app/Controllers/password_reset_controller.php" method="POST">
                                        <div class="mb-3">
                                            <label class="form-label">メールアドレス</label>
                                            <input class="form-control form-control-lg" type="email" name="email" placeholder="Enter your email" />
                                        </div>
                                        <div class="d-grid gap-2 mt-3">
                                            <button type="submit" class="btn btn-lg btn-primary">次へ</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mb-3">
                            アカウントをお持ちでないですか? <a href="sign_up.php">アカウント作成</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="/custom/admin/public/js/app.js"></script>

</body>

</html>