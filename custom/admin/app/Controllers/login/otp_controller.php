<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once($CFG->libdir . '/authlib.php');

$userid = required_param('userid', PARAM_INT);
$otp = required_param('otp', PARAM_INT);

global $DB, $SESSION;

// DBからOTPを取得
$record = $DB->get_record('local_otp', ['userid' => $userid]);
// ワンタイムパスワードが違っていないか
if ($record && $record->otp != $otp) {
    $SESSION->login_error = '無効なワンタイムパスワードです。';
    redirect(new moodle_url('/custom/admin/app/Views/login/otp.php', ['userid' => $userid]));
    exit;
}
// 有効時間を過ぎていないか
if (time() >= $record->expires) {
    // 期限切れはログイン画面へ
    $SESSION->login_error = 'ワンタイムパスワードの期限切れです。';
    $DB->delete_records('local_otp', ['userid' => $userid]); // 有効期間切れはOTP削除
    redirect(new moodle_url('/custom/admin/app/Views/login/login.php'));
    exit;
}
// OTPが一致＆有効期限内ならログイン
$user = $DB->get_record('user', ['id' => $userid]);

if ($user) {
    complete_user_login($user);
    $DB->delete_records('local_otp', ['userid' => $userid]); // OTP削除
    redirect(new moodle_url('/custom/admin/app/Views/management/index.php'));
    exit;
}
?>
