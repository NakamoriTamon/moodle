<?php
require_once('/var/www/html/moodle/config.php');
// ユーザーがログインしていない場合、自動リダイレクト
if (!isloggedin() || isguestuser()) {
    redirect('/custom/admin/app/Views/login/login.php'); // カスタムログインページへリダイレクト
    exit;
}
// require_login();

global $USER, $DB;

// ユーザーのロールを取得
$userRoles = $DB->get_records_sql("
    SELECT r.shortname 
    FROM {role_assignments} ra
    JOIN {role} r ON ra.roleid = r.id
    WHERE ra.userid = ?
", [$USER->id]);

$roles = array_map(fn($role) => $role->shortname, $userRoles);

$request_uri = $_SERVER['REQUEST_URI'];

$redirect_url_list = [
    '/custom/admin/app/Views/management/index.php',
    '/custom/admin/app/Views/management/user_registration.php',
    '/custom/admin/app/Views/management/membership_fee_registration.php',
    '/custom/admin/app/Views/message/index.php',
];

// コースクリエーター (ID:2) は `一部機能` へアクセス不可
if (in_array('coursecreator', $roles) && in_array(parse_url($request_uri, PHP_URL_PATH), $redirect_url_list)) {
    redirect('/custom/admin/app/Views/event/index.php'); // イベント画面へリダイレクト
    exit;
} else if (!in_array('admin', $roles) && !in_array('coursecreator', $roles)) {
    redirect('/custom/admin/app/Views/login/login.php');
}
