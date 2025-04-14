<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/UserModel.php');

global $DB;
global $USER;

$userModel = new UserModel();

$current_page = $_GET['page'] ?? 1;
if ($_GET['page'] < 0) {
    $current_page = 1;
}
$per_page = 15;

$admins = $userModel->getAdminUsers([], $current_page, $per_page);

// 総件数
$admin_count = $userModel->getAdminUserCount();
$total_count = count($admin_count);

// フォーム送信（POST）でコントローラーを呼び出す処理
$action = optional_param('action', '', PARAM_ALPHA); // アクションパラメータを取得

if ($action === 'index') {
    $_SESSION['old_input'] = $_POST; // 入力内容も保持
    include '/var/www/html/moodle/custom/admin/app/Views/management/index.php';
}
