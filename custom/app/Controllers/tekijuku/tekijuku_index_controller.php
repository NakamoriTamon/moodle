<?php
require_once('/var/www/html/moodle/config.php');

/**
 * 適塾画面の「入会する」ボタン押下処理
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // コントローラーをインスタンス化
    $controller = new TekijukuRouteController();
    
    // リダイレクト処理を呼び出す
    $controller->redirect();
}

/**
 * 適塾画面の「入会する」ボタン押下処理
 */
class TekijukuRouteController {
    private $DB;
    private $USER;

    public function __construct() {
        global $DB, $USER;
        $this->DB = $DB;
        $this->USER = $USER;
    }

    public function getRedirectionUrl() {
        if (isloggedin() && isset($_SESSION['USER'])) {
            $tekijuku_commemorations = $this->DB->get_records('tekijuku_commemoration', ['fk_user_id' => $this->USER->id], 'id');
            if (count($tekijuku_commemorations) > 0) {
                return '/custom/app/Views/mypage/index.php';
            } else {
                return '/custom/app/Views/tekijuku/registrate.php';
            }
        } else {
            return '/custom/app/Views/login/index.php';
        }
    }

    public function redirect() {
        $redirectUrl = $this->getRedirectionUrl();
        header("Location: $redirectUrl");
        exit;
    }
}

/**
 * 適塾画面の初期描写
 */
class TekijukuIndexController {
    private $DB;
    private $USER;

    public function __construct() {
        global $DB, $USER;
        $this->DB = $DB;
        $this->USER = $USER;
    }

    /**
     * retrun bool ログインかつ適塾情報が有ればtrue
     */
    public function isTekijukuCommemorationMember() {
        if (isloggedin() && isset($_SESSION['USER'])) {
            $record = $this->DB->get_record(
                'tekijuku_commemoration',
                ['fk_user_id' => $this->USER->id],
                'number, name'
            );
            if ($record !== false) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
?>