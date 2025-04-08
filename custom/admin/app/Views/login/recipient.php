<?php include('/var/www/html/moodle/custom/admin/app/Views/common/logon_header.php');

// セッションからエラーメッセージを取得
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']); // 一度表示したら削除
 ?>

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
                                    <form action="/custom/admin/app/Controllers/login/password_reset_controller.php" method="POST">
                                        <div class="mb-3">
                                            <label class="form-label">メールアドレス</label>
                                            <input class="form-control form-control-lg" type="email" name="email" placeholder="Enter your email" value="<?= htmlspecialchars(isset($old_input['email']) ? $old_input['email'] : '') ?>" />
                                        </div>
										<?php if (!empty($errors['email'])): ?>
											<div class="text-danger mt-2"><?= htmlspecialchars($errors['email']); ?></div>
										<?php endif; ?>
                                        <div class="d-grid gap-2 mt-3">
                                            <button type="submit" class="btn btn-lg btn-primary">次へ</button>
                                            <a href="/custom/admin/app/Views/login/login.php" class="btn btn-lg btn-outline-dark">ログイン画面に戻る</a>
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

    <script src="/custom/admin/public/js/app.js"></script>

</body>

</html>