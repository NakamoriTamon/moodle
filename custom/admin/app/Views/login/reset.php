<?php include('/var/www/html/moodle/custom/admin/app/Views/common/logon_header.php');
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');

// トークンを取得
$token = $_GET['token'] ?? null;

if ($token) {
    global $DB;

    // トークンの有効性を確認
    $reset_data = $DB->get_record('user_password_resets', ['token' => $token]);

    if ($reset_data->timerequested + 3600 < time()) {
        echo "トークンの有効期限が切れています。再度リクエストしてください。";
        exit;
    }
?>

<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
    <main class="d-flex w-100 h-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">

                        <div class="text-center mt-4">
                            <h1 class="h2">パスワードリセット</h1>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="m-sm-3">
                                    <form action="/custom/admin/app/Controllers/login/password_update_controller.php" method="POST">
                                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                                        <div class="mb-3">
                                            <label class="form-label">パスワード</label>
                                            <input class="form-control form-control-lg" type="password" name="password" placeholder="新しいパスワード" required />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">パスワード(確認)</label>
                                            <input class="form-control form-control-lg" type="password" name="password_confirmation" placeholder="新しいパスワード(確認)" required />
                                        </div>
                                        <div class="d-grid gap-2 mt-3">
                                            <button type="submit" class="btn btn-lg btn-primary">パスワードリセット</button>
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
<?php
} else {
    echo "無効なまたは期限切れのトークンです。";
}
?>

</html>