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
    $errors = $_SESSION['result_message'] ?? [];
    unset($_SESSION['result_message']); // 一度表示したら削除
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
                                                <div class="list_field f_txt">
                                                    <div class="input-container" style="position: relative;">
                                                        <input class="form-control form-control-lg" type="password" id="password" name="password" />
                                                        <i class="fa fa-eye-slash toggle-password" data-toggle="#password"
                                                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                                                    </div>
                                                    <?php if (!empty($errors)): ?>
                                                        <div class="text-danger mt-2"><?= htmlspecialchars($errors); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">パスワード(確認)</label>
                                                    <div class="input-container" style="position: relative;">
                                                        <input class="form-control form-control-lg" type="password" id="password_confirm" name="password_confirm" onpaste="return false" autocomplete="off" />
                                                        <i class="fa fa-eye-slash toggle-password" data-toggle="#password_confirm"
                                                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                                                    </div>
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

<script>
    $(document).ready(function() {
        $('.toggle-password').click(function() {
            var input = $($(this).attr('data-toggle'));
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                $(this).removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                input.attr('type', 'password');
                $(this).removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });
    });
</script>

</html>