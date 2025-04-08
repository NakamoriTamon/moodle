<?php
include('/var/www/html/moodle/custom/admin/app/Views/common/logon_header.php');

// メッセージが設定されていない場合のデフォルトメッセージ
$result_message = $_SESSION['result_message'] ?? '不明なエラーが発生しました。';

// メッセージを一度表示したら削除
unset($_SESSION['result_message']);
?>

<body id="login" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
    <main class="d-flex w-100 h-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">
                        <div class="text-center mt-4 mb-3">
                            <h1 class="h2"><?php echo htmlspecialchars($result_message, ENT_QUOTES, 'UTF-8'); ?></h1>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="m-sm-3">
                                    <div class="d-grid gap-2 mt-3">
                                        <a href="/custom/admin/app/Views/login/login.php" class="btn btn-lg btn-outline-dark">ログイン画面に戻る</a>
                                    </div></div>
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