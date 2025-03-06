<?php
require_once('/var/www/html/moodle/config.php');
global $DB;

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["users"])) {
        // roleid=9の現在の件数を取得
        $admin_count = $DB->count_records_sql("SELECT COUNT(*) FROM {role_assignments} WHERE roleid = 9");

        // 変更後にroleid=10が0件になるか確認
        $new_admin_count = $admin_count;
        foreach ($_POST["users"] as $user) {
            $id = intval($user['id']);
            $role_id = intval($user['role_id']);

            if (!empty($id) && !empty($role_id)) {
                // roleid=10を削除する場合、カウントを減らす
                $current_role = $DB->get_field('role_assignments', 'roleid', ['userid' => $id]);
                if ($current_role == 9 && $role_id != 9) {
                    $new_admin_count--;
                }
            } else {
                $_SESSION['message_error'] = '更新に失敗しました。';
                redirect(new moodle_url('/custom/admin/app/Views/management/index.php'));
                exit;
            }
        }

        // roleid=10が0件になるならエラーを出して更新しない
        if ($new_admin_count <= 0) {
            $_SESSION['message_error'] = 'システム管理者は最低1人必要です。';
            redirect(new moodle_url('/custom/admin/app/Views/management/index.php'));
            exit;
        }

        foreach ($_POST["users"] as $user) {
            $id = intval($user['id']);
            $role_id = intval($user['role_id']);

            // role_assignments テーブルの更新
            $DB->execute("
                UPDATE mdl_role_assignments
                SET roleid = ? 
                WHERE userid = ?
            ", [$role_id, $id]);
        }

        $_SESSION['message_success'] = '登録が完了しました';
        // 更新完了後のリダイレクト
        redirect(new moodle_url('/custom/admin/app/Views/management/index.php'));
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['message_error'] = '登録に失敗しました';
    redirect(new moodle_url('/custom/admin/app/Views/management/index.php'));
    exit;
}
