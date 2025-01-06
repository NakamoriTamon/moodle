<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php'); ?>

<body id="login" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
    <main class="d-flex w-100 h-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">
                        <div class="text-center mt-4 mb-3">
                            <h1 class="h2">大阪大学 知の広場</h1>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="m-sm-3">
                                    <div class="row">
                                    </div>
                                    <form>
                                        <div class="mb-3">
                                            <label class="form-label">メールアドレス</label>
                                            <input class="form-control form-control-lg" type="email" name="email" placeholder="Enter your email" />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">パスワード</label>
                                            <input class="form-control form-control-lg" type="password" name="password" placeholder="Enter your password" />
                                            <small>
                                                <a href="recipient.php">パスワードを忘れましたか?</a>
                                            </small>
                                        </div>
                                        <div class="d-grid gap-2 mt-3">
                                            <a href="/custom/admin/app/Views/management/index.php" class="btn btn-lg btn-primary">ログイン</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mb-3">
                            はじめての方はこちらから <a href="sign_up.php">アカウント作成</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="/custom/admin/public/js/app.js"></script>
</body>

</html>