<?php include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');

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
                            <h1 class="h2">アカウント作成</h1>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="m-sm-3">
                                    <form action="/custom/admin/app/Controllers/login/sign_up_controller.php" method="post">
                                        <div class="mb-3">
                                            <label class="form-label">担当者名</label>
                                            <div class="input-group">
                                                <input class="form-control form-control-lg" type="text" name="lastname" placeholder="苗字"
                                                    value="<?= htmlspecialchars($old_input['lastname'] ?? '') ?>" />
                                                <input class="form-control form-control-lg" type="text" name="firstname" placeholder="名前"
                                                    value="<?= htmlspecialchars($old_input['firstname'] ?? '') ?>" />
                                            </div>
                                            <?php if (!empty($errors['lastname'])): ?>
                                                <div class="text-danger mt-2"><?= htmlspecialchars($errors['lastname']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($errors['firstname'])): ?>
                                                <div class="text-danger mt-2"><?= htmlspecialchars($errors['firstname']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">メールアドレス</label>
                                            <input class="form-control form-control-lg" type="email" name="email" placeholder="Enter your email"
                                                value="<?= htmlspecialchars($old_input['email'] ?? '') ?>" />
                                            <?php if (!empty($errors['email'])): ?>
                                                <div class="text-danger mt-2"><?= htmlspecialchars($errors['email']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">パスワード</label>
                                            <input class="form-control form-control-lg" type="password" name="password" placeholder="Enter password" />
                                            <?php if (!empty($errors['password'])): ?>
                                                <div class="text-danger mt-2"><?= htmlspecialchars($errors['password']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-grid gap-2 mt-3">
                                            <button type="submit" class="btn btn-lg btn-primary">サインアップ</button>
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
</body>

</html>