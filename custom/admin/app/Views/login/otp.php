<?php
include('/var/www/html/moodle/custom/admin/app/Views/common/logon_header.php');
require_once('/var/www/html/moodle/config.php');
require_once($CFG->libdir . '/formslib.php');

$userid = required_param('userid', PARAM_INT);
?>
<body id="otp" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
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
                                        <!-- エラーメッセージの表示 -->
                                        <?php
                                        require_once(__DIR__ . '/../../../../../config.php');
                                        global $SESSION;
                                        if (!empty($SESSION->login_error)) {
                                            echo '<p style="color: red;">' . $SESSION->login_error . '</p>';
                                            unset($SESSION->login_error); // メッセージを一度表示したら削除
                                        }
                                        ?>
                                    </div>
                                    <form name="loginForm" action="/custom/admin/app/Controllers/login/otp_controller.php" method="post">
                                        <input type="hidden" name="userid" value="<?= $userid ?>">
                                        <div class="mb-3">
                                            <label class="form-label">ワンタイムパスワード</label>
                                            <input class="form-control form-control-lg" type="text" name="otp" required />
                                        </div>
                                        <div class="d-grid gap-2 mt-3">
                                        <button type="submit" class="btn btn-lg btn-primary">送信</button>
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