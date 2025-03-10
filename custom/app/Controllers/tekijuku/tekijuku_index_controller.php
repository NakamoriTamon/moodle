<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/lib/classes/context/system.php');
require '/var/www/vendor/autoload.php';

use Dotenv\Dotenv;
use core\context\system;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * 適塾画面の初期描写及び「入会する」ボタン押下処理
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $post_kbn = isset($_POST['post_kbn']) ? $_POST['post_kbn'] : '';
    // コントローラーをインスタンス化
    $controller = new TekijukuIndexController();
    switch ($post_kbn) {
        case 'tekijuku_route':
            $controller->redirect();
            break;
        case 'tekijuku_delete':
            $controller->disableAccount();
            break;
        default:
            break;
    }
}

/**
 * 適塾画面の初期描写
 */
class TekijukuIndexController {
    private $DB;
    private $USER;
    private $dotenv;


    public function __construct() {
        global $DB, $USER;
        $this->DB = $DB;
        $this->USER = $USER;
        
        $this->dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
        $this->dotenv->load();
    }

    /**
     * retrun bool ログインかつ適塾情報が有ればtrue
     */
    public function isTekijukuCommemorationMember() {
        if (isloggedin() && isset($_SESSION['USER'])) {
            $record = $this->DB->get_record(
                'tekijuku_commemoration',
                ['fk_user_id' => $this->USER->id],
                'number, name, is_delete'
            );
            if ($record !== false && (int)$record->is_delete === TEKIJUKU_COMMEMORATION_IS_DELETE['ACTIVE']) {
                // 未退会
                return 'isActive';
            } else if ($record !== false && (int)$record->is_delete === TEKIJUKU_COMMEMORATION_IS_DELETE['INACTIVE']) {
                // 退会
                return 'isInactive';
            } else {
                return 'isNotMember';
            }
        } else {
            return 'isNotMember';
        }
    }

    /**
     * リダイレクト先のURLを決定
     */
    private function getRedirectionUrl() {
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

    /**
     * リダイレクト処理を実行
     */
    public function redirect() {
        $redirectUrl = $this->getRedirectionUrl();
        header("Location: $redirectUrl");
        exit;
    }

    /**
     * ログインユーザーの適塾IDを返し
     * ログアウト中の場合はnullを返す
     */
    public function getTekijukuCommemoration() {
        try {
            return isloggedin() && !empty($this->USER->id) 
                ? $this->DB->get_record('tekijuku_commemoration', ['fk_user_id' => $this->USER->id], 'id, is_delete')
                : null;
        } catch (Exception $e) {
            error_log('Error in getTekijukuCommemoration: ' . $e->getMessage());
            return null;  // エラー発生時はnullを返す
        }
    }

    /**
     * 適塾アカウント停止
     * メールを送る
     */
    public function disableAccount() {
        $id = $_POST['id'] ?? null;
        if ($id === 0 && is_null($id)) {
            // IDが無効な場合、エラーを返す
            header('Content-Type: application/json');
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'IDが指定されていません']);
            exit;
        }
        try {
            if (isloggedin() && isset($_SESSION['USER'])) {
                $transaction = $this->DB->start_delegated_transaction();
        
                $tekijuku_commemoration = $this->tekijukuCommemoration();
                $data = new stdClass();
                $data->id = (int)$id;
                $data->is_delete = 1;
                // DB更新       
                $this->DB->update_record_raw('tekijuku_commemoration', $data);
                $name = $tekijuku_commemoration->name ?? '';
                $email = $tekijuku_commemoration->email;
                if (empty($email)) {
                    throw new Exception('メールアドレスが見つかりませんでした。');
                }
                // メール送信処理
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host        = $_ENV['MAIL_HOST'];
                $mail->SMTPAuth    = true;
                $mail->Username    = $_ENV['MAIL_USERNAME'];
                $mail->Password    = $_ENV['MAIL_PASSWORD'];
                $mail->SMTPSecure  = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->CharSet     = PHPMailer::CHARSET_UTF8;
                $mail->Port        = $_ENV['MAIL_PORT'];

                $mail->setFrom($_ENV['MAIL_FROM_ADRESS'], 'Sender Name');
                $mail->addAddress($email, 'Recipient Name');
                $mail->addReplyTo('no-reply@example.com', 'No Reply');
                $mail->isHTML(true);
                // メール本文（確認URLを表示）
                $currentDate = new DateTime();
                $fiscalYear = null;
                // 年度を計算するための条件
                $year = $currentDate->format('Y');  // 現在の西暦年
                $month = $currentDate->format('m'); // 現在の月

                // 4月1日から年度が始まるので、3月以前の場合は前年年度、それ以降は今年度
                if ($month >= 4) {
                    $fiscalYear = $year + 1; // 4月以降は次年度
                } else {
                    $fiscalYear = $year; // 3月以前は前年度
                }

                $htmlBody = "
                <div style=\"text-align: center; font-family: Arial, sans-serif;\">
                    <p style=\"text-align: left; font-weight:bold;\">{$name}様</p><br>
                    <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">この度は適塾記念会をご利用いただき、誠にありがとうございます。</p><br>
                    <p style=\"text-align: left; font-size: 13px; margin:0; \">ご申請いただきました退会手続きを受け付けましたので、お知らせいたします。</p><br>
                    <p style=\"text-align: left; font-size: 13px; margin:0; \">退会日は{$fiscalYear}年3月31日となり、それまでの期間は引き続きログインが可能です。</p><br>
                    <p style=\"text-align: left; font-size: 13px; margin:0; \">退会日を過ぎると、AA記念会のサービスおよび会員専用ページへのアクセスができなくなりますので、ご注意ください。</p><br>
                    <p style=\"margin-top: 30px; font-size: 13px; text-align: left;\">
                        このメールは、配信専用アドレスから送信されています。<br>
                        このメールに返信いただいても、返信内容の確認及びご返信はできません。<br>
                    </p>
                </div>
                ";
                $mail->Subject = '【適塾記念会】退会案内のお知らせ';
                $mail->Body = $htmlBody;

                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true
                    )
                );
                $mail->send();
                // コミット
                $transaction->allow_commit();
                exit;
            } else {
                // ログインしていない場合の処理
                header('Content-Type: application/json');
                http_response_code(401); // Unauthorized
                echo json_encode(['error' => 'ログインが必要です']);
                exit;
            }
        } catch (Exception $e) {
            $transaction->rollback($e);
            header('Content-Type: application/json');
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => '退会処理中にエラーが発生しました。']);
            exit;
        }
    }

        /**
     * retrun bool ログインかつ適塾情報が有ればtrue
     */
    private function tekijukuCommemoration() {
        if (isloggedin() && isset($_SESSION['USER'])) {
            return $this->DB->get_record(
                'tekijuku_commemoration',
                ['fk_user_id' => $this->USER->id, 'is_delete' => 0],
                'email, name'
            );
        }
        return null;
    }
}
?>