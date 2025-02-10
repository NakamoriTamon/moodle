<?php
require_once('/var/www/html/moodle/config.php');
// ユーザーがログインしていない場合、自動リダイレクト
if (!isloggedin() || isguestuser()) {
    redirect('/custom/admin/app/Views/login/login.php'); // カスタムログインページへリダイレクト
    exit;
}
require_login();

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

// コースクリエーター (ID:2) は `/event/` 以外の `/custom/admin/` へアクセス不可
if (in_array('coursecreator', $roles) && strpos($request_uri, '/custom/admin/') === 0 && strpos($request_uri, '/custom/admin/app/Views/event/') === false && strpos($request_uri, '/custom/admin/app/Controllers/event/') === false) {
    redirect('/custom/admin/app/Views/event/index.php'); // 一般画面へリダイレクト
    exit;
} else if(!in_array('admin', $roles) && !in_array('coursecreator', $roles)) {
    redirect('/custom/app/Views/front/index.php');
}
?>