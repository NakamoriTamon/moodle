<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');

class MypageController {
    private $DB;
    private $USER;
    private $eventApplicationModel;

    public function __construct() {
        global $DB, $USER; 
        $this->DB = $DB;
        $this->USER = $USER;
        $this->eventApplicationModel = new EventApplicationModel();
    }

    // $lastname_kana = "";
    // $firstname_kana = "";
    // ユーザー情報を取得
    public function getUserData() {
        return $this->DB->get_record(
            'user',
            ['id' => $this->USER->id],
            'id, name, name_kana, email, phone1, city, guardian_kbn, birthday, guardian_email, description'
        );
    }

    // 適塾記念情報を取得
    public function getTekijukuCommemoration() {
        return $this->DB->get_record(
            'tekijuku_commemoration',
            ['fk_user_id' => $this->USER->id],
            'number, name'
        );
    }

    // イベント申し込み情報を取得
    public function getEventApplications() {
        return [
            'current' => $this->eventApplicationModel->getEventApplicationByUserId($this->USER->id),
            'past' => $this->eventApplicationModel->getOldEventApplicationByUserId($this->USER->id)
        ];
    }
}

?>