<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/UserModel.php');

global $DB;
global $USER;

$userModel = new UserModel();

// 検索項目取得
$keyword     = $_POST['keyword'] ?? null;

$filters = array_filter([
    'keyword' => $keyword
]);

$admins = $userModel->getAdminUsers($filters, 1, 100000);
// 総件数
$totalCount = count($admins);
// フォーム送信（POST）でコントローラーを呼び出す処理
$action = optional_param('action', '', PARAM_ALPHA); // アクションパラメータを取得

if ($action === 'index') {
    $_SESSION['old_input'] = $_POST; // 入力内容も保持
    include '/var/www/html/moodle/custom/admin/app/Views/management/index.php';
}
