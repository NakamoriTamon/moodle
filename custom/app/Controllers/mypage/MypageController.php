<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');

$lastname_kana = "";
$firstname_kana = "";
if (isloggedin() && isset($_SESSION['USER'])) {
    global $DB, $USER;

    // 必要な情報を取得
    $userData = $DB->get_record('user', ['id' => $USER->id], 'lastname, firstname, email, phone1, city, lastname_kana, firstname_kana, guardian_kbn, birthday
    , guardian_lastname, guardian_firstname, guardian_lastname_kana, guardian_firstname_kana, guardian_email, note, notification_kbn');
    // 都道府県
    $prefectures = PREFECTURES;

    $eventApplicationModel = new EventApplicationModel();
    $eventApplicationList = $eventApplicationModel->getEventApplicationByUserId($_SESSION['USER']->id);
    $oldEventApplicationList = $eventApplicationModel->getOldEventApplicationByUserId($_SESSION['USER']->id);
}
?>