<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/login/admin_registration_controller.php');
$admin_registration_controller = new adminRegistrationController();
$result = $admin_registration_controller->index($_GET['id'], $_GET['expiration_time']);
include('/var/www/html/moodle/custom/admin/app/Views/common/logon_header.php');
?>

<body id="login" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
    <main class="d-flex w-100 h-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">
                        <div class="text-center mt-4 mb-3">
                            <h1 class="h2"><?= $result ? ($result === 2 ? "既に本登録されています。" : "本登録が完了いたしました。") : "本登録に失敗しました。" ?></h1>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="m-sm-3">
                                    <div class="d-grid gap-2 mt-3">
                                        <a href="/custom/admin/app/Views/login/login.php" class="btn btn-lg btn-outline-dark">ログイン画面へ</a>
                                    </div>
                                </div>
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