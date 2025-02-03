<?php
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once('/var/www/html/moodle/custom/app/Controllers/TutorController.php');

$tutorController = new TutorController();
$tutors = $tutorController->getToturs();
?>

<body id="management" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
    <div class="wrapper">
        <?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <div class="navbar-collapse collapse">
                    <a class="sidebar-toggle js-sidebar-toggle">
                        <i class="hamburger align-self-center"></i>
                    </a>
                    <p class="title header-title ms-4 fs-4 fw-bold mb-0">管理者一覧</p>
                    <p class="title mb-0"></p>
                    <ul class="navbar-nav navbar-align">
                        <li class="nav-item dropdown">
                            <a class="nav-icon pe-md-0 dropdown-toggle d-flex" href="#" data-bs-toggle="dropdown">
                                <div class="fs-5 me-4 text-decoration-underline">システム管理者</div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="/custom/admin/app/Views/login/login.php">Log out</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="content">
                <div class="col-12 col-lg-12">
                    <div class="card min-70vh">
                        <div class="card-body p-0">
                            <div class="d-flex w-100 mt-3">
                                <button onclick="window.location.href='/custom/admin/app/Views/master/tutor/upsert.php'" class="btn btn-primary mt-3 mb-3 ms-auto">新規登録</button>
                            </div>
                            <div class="card m-auto mb-5 overflow-auto w-95">
                                <table class="table table-responsive table-striped table_list">
                                    <thead>
                                        <tr>
                                            <th class="ps-4 pe-4 min-130">講師ID</th>
                                            <th class="ps-4 pe-4 w-35">講師名</th>
                                            <th class="ps-4 pe-4 w-35">メールアドレス</th>
                                            <th class="ps-4 pe-4 w-35">講師概要</th>
                                            <th class="text-center ps-4 pe-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tutors as $totur): ?>
                                            <tr>
                                                <td class="ps-4 pe-4"><?= htmlspecialchars($totur['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td class="ps-4 pe-4"><?= htmlspecialchars($totur['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td class="ps-4 pe-4"><?= htmlspecialchars($totur['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td class="ps-4 pe-4"><?= htmlspecialchars(mb_strimwidth($totur['overview'], 0, 100, '...', 'UTF-8')) ?></td>
                                                <td class="text-center ps-4 pe-4 text-nowrap"><a href="/custom/admin/app/Views/master/tutor/upsert.php?id=<?= htmlspecialchars($totur['id'], ENT_QUOTES, 'UTF-8') ?>" class=" me-3"><i class="align-middle" data-feather="edit-2"></i></a><a class="delete-link" data-id="<?= htmlspecialchars($totur['id'], ENT_QUOTES, 'UTF-8') ?>" data-name="<?= htmlspecialchars($totur['name'], ENT_QUOTES, 'UTF-8') ?>"><i class="align-middle" data-feather="trash"></i></a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- 削除確認モーダル -->
                            <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form id="deleteForm" action="/custom/admin/app/Controllers/tutor/tutor_delete_controller.php" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="id" value="">
                                            <div class="modal-header">
                                                <h5 class="modal-title">削除確認</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="mt-3"><span id="deleteTutorName"></span> を削除します。本当によろしいですか？</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                                                <button type="button" class="btn btn-danger" id="confirmDeleteButton">削除</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="/custom/admin/public/js/app.js"></script>
</body>

</html>

<script>
    $(document).ready(function() {
        let selectedId;
        // 削除リンクがクリックされたとき
        $('.delete-link').on('click', function(event) {
            event.preventDefault();
            selectedId = $(this).data('id');
            let tutorName = $(this).data('name');

            $('input[name="id"]').val(selectedId);
            $('#deleteTutorName').text(tutorName);
            $('#confirmDeleteModal').modal('show');
        });
        // モーダル内の削除ボタンがクリックされたとき
        $('#confirmDeleteButton').on('click', function() {
            $('#confirmDeleteModal').modal('hide');
            $('#deleteForm').submit();
        });
    });
</script>