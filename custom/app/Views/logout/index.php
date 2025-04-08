<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_logout();

// ログアウト画面の表示はせずトップ画面へ飛ばす
redirect('/custom/app/Views/index.php');
?>