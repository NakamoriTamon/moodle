<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/lib/classes/context/system.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require '/var/www/vendor/autoload.php';

use Dotenv\Dotenv;
use core\context\system;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $post_kbn = isset($_POST['post_kbn']) ? $_POST['post_kbn'] : '';
    // コントローラーをインスタンス化
    $controller = new MypageController();
    switch ($post_kbn) {
        case 'user_delete':
            $controller->disableAccount();
            break;
        default:
            break;
    }
}

class MypageController
{
    private $DB;
    private $USER;
    private $eventApplicationModel;
    private $dotenv;

    public function __construct()
    {
        global $DB, $USER;
        $this->DB = $DB;
        $this->USER = $USER;
        $this->eventApplicationModel = new EventApplicationModel();

        $this->dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
        $this->dotenv->load();
    }

    // $lastname_kana = "";
    // $firstname_kana = "";
    // ユーザー情報を取得
    public function getUser()
    {
        return $this->DB->get_record(
            'user',
            ['id' => $this->USER->id],
            'id, name, name_kana, email, phone1, city, guardian_kbn, birthday, guardian_name, guardian_email, description, notification_kbn, child_name',
        );
    }

    // 適塾記念情報を取得
    public function getTekijukuCommemoration()
    {
        return $this->DB->get_record(
            'tekijuku_commemoration',
            ['fk_user_id' => $this->USER->id],
            'id, number, type_code, name, kana, post_code, address, tell_number, email, payment_method, note, is_published, is_subscription, is_delete, department, major, official, is_university_member'
        );
    }

    // イベント申し込み情報を取得
    public function getEventApplications($offset = 0, $limit = 1, $page = 1, $get_application = 'booking')
    {
        try {

            // limit と offset を整数にキャスト
            $limit = intval($limit);
            $offset = intval($offset);
            $page = intval($page); // 現在のページ番号

            // ページネーションの設定
            $perPage = $limit; // 1ページあたりのアイテム数

            // SQLクエリ（ページネーション対応）
            $sql = $get_application == 'booking' ? "
                WITH ranked_courses AS (
                    SELECT 
                        ci.id AS course_id,
                        ci.no,
                        ci.course_date,
                        eaci.event_id,
                        eaci.event_application_id,
                        ROW_NUMBER() OVER (PARTITION BY eaci.event_id ORDER BY ci.course_date ASC) AS rn
                    FROM 
                        {course_info} ci
                    JOIN 
                        {event_application_course_info} eaci ON ci.id = eaci.course_info_id
                    WHERE 
                        ci.course_date >= CURDATE()
                ),
                filtered_applications AS (
                    SELECT 
                        ea.*,
                        ROW_NUMBER() OVER (PARTITION BY ea.event_id ORDER BY ea.application_date ASC) AS app_rn
                    FROM 
                        {event_application} ea
                )
                SELECT 
                    fa.id AS event_application_id,
                    fa.event_id,
                    fa.user_id,
                    fa.price,
                    fa.ticket_count,
                    fa.payment_date,
                    e.id AS event_id,
                    e.name AS event_name,
                    e.venue_name AS venue_name,
                    rc.course_id,
                    rc.no,
                    rc.course_date
                FROM 
                    filtered_applications fa
                JOIN 
                    {event} e ON fa.event_id = e.id
                JOIN 
                    ranked_courses rc 
                    ON fa.event_id = rc.event_id
                    AND rc.rn = 1
                WHERE 
                    fa.user_id = :user_id
                    AND fa.app_rn = 1
                ORDER BY 
                    fa.event_id, rc.course_date
                LIMIT $limit OFFSET $offset;
            " : "
            WITH ranked_courses AS (
                SELECT 
                    ci.id AS course_id,
                    ci.no,
                    ci.course_date,
                    eaci.event_id,
                    eaci.event_application_id
                FROM 
                    {course_info} ci
                JOIN 
                    {event_application_course_info} eaci ON ci.id = eaci.course_info_id
                WHERE 
                    ci.course_date < CURDATE()
            ),
            filtered_applications AS (
                SELECT 
                    ea.*
                FROM 
                    {event_application} ea
            )
            SELECT 
                fa.id AS event_application_id,
                fa.event_id,
                fa.user_id,
                fa.price,
                fa.ticket_count,
                fa.payment_date,
                e.id AS event_id,
                e.name AS event_name,
                e.venue_name AS venue_name,
                rc.course_id,
                rc.no,
                rc.course_date
            FROM 
                filtered_applications fa
            JOIN 
                {event} e ON fa.event_id = e.id
            JOIN 
                ranked_courses rc 
                ON fa.id = rc.event_application_id
            WHERE 
                fa.user_id = :user_id
            ORDER BY 
                fa.event_id, rc.course_date
            LIMIT $limit OFFSET $offset;
        ";

            // パラメータ設定
            $params = [
                'user_id' => $this->USER->id,
            ];

            // トータルカウントの取得
            $totalCount = (int) $this->getTotalEventApplicationsCount($get_application);
            // トータルページ数の計算
            $totalPages = ceil($totalCount / $perPage);

            // SQLクエリを実行してデータを取得
            $data = $this->DB->get_records_sql($sql, $params);

            // ページネーション情報とデータをまとめて返す
            $pagenete_data = [
                'data' => $data,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'per_page' => $perPage,
                    'total_count' => $totalCount
                ]
            ];
        } catch (Exception $e) {
            var_dump($e);
        }

        return $pagenete_data;
    }

    private function getTotalEventApplicationsCount($get_application)
    {
        try {
            $sql = $get_application == 'booking' ? "
                WITH filtered_applications AS (
                SELECT 
                    ea.*,
                    ROW_NUMBER() OVER (PARTITION BY ea.event_id ORDER BY ea.application_date ASC) AS app_rn
                FROM 
                    {event_application} ea
                JOIN 
                    {event} e ON ea.event_id = e.id
                JOIN 
                    {event_application_course_info} eaci ON ea.id = eaci.event_application_id
                JOIN 
                    {course_info} ci ON eaci.course_info_id = ci.id  -- course_info とイベントの関連付け
                WHERE 
                    ci.course_date >= CURDATE()  -- 未来のコースのみ
                )
                SELECT COALESCE(COUNT(*), 0) AS count
                FROM filtered_applications fa
                WHERE fa.user_id = :user_id
                AND fa.app_rn = 1;
            " : "
                WITH filtered_applications AS (
                SELECT 
                    ea.*
                FROM 
                    {event_application} ea
                JOIN 
                    {event} e ON ea.event_id = e.id
                JOIN 
                    {event_application_course_info} eaci ON ea.id = eaci.event_application_id
                JOIN 
                    {course_info} ci ON eaci.course_info_id = ci.id  -- course_info とイベントの関連付け
                WHERE 
                    ci.course_date < CURDATE()  -- 未来のコースのみ
                )
                SELECT COALESCE(COUNT(*), 0) AS count
                FROM filtered_applications fa
                WHERE fa.user_id = :user_id;
            ";

            $params = ['user_id' => $this->USER->id];
            $count = $this->DB->get_record_sql($sql, $params)->count;
        } catch (Exception $e) {
            var_dump($e);
        }
        return $count;
    }


    /**
     * マイページアカウント停止
     * 適塾アカウント停止
     * メールを送る
     */
    public function disableAccount()
    {
        $id = $this->USER->id;
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

                $user = $this->getUser();
                $name = $user->name;
                $email = $user->email;
                $data = new stdClass();
                $data->id = (int)$id;
                $data->deleted = 1;
                // DB更新       
                $this->DB->update_record_raw('user', $data);
                $tekijuku_commemoration = $this->tekijukuCommemoration();

                if ($tekijuku_commemoration) { // 適塾側DB更新
                    $tekijuku_data = new stdClass();
                    $tekijuku_data->id = (int)$tekijuku_commemoration->id;
                    $tekijuku_data->is_delete = 1;
                    $this->DB->update_record_raw('tekijuku_commemoration', $tekijuku_data);
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

                $htmlBody = "
                <div style=\"text-align: center; font-family: Arial, sans-serif;\">
                    <p style=\"text-align: left; font-weight:bold;\">{$name}様</p><br>
                    <p style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">この度は知の広場をご利用いただき、誠にありがとうございました。</p><br>
                    <p style=\"text-align: left; font-size: 13px; margin:0; \">ご申請いただきました退会手続きが完了しましたので、お知らせいたします。</p><br>
                    <br>
                    <p style=\"text-align: left; font-size: 13px; margin:0; \">これに伴い、知の広場の会員専用ページへのアクセスができなくなります。</p><br>
                    <p style=\"text-align: left; font-size: 13px; margin:0; \">また、登録情報は当会の規定に基づき適切に処理されます。</p><br>
                    <br>
                    <p style=\"text-align: left; font-size: 13px; margin:0; \">なお、再度ご利用をご希望される場合は、新規登録が必要となります。</p><br>
                    <p style=\"margin-top: 30px; font-size: 13px; text-align: left;\">
                        このメールは、配信専用アドレスから送信されています。<br>
                        このメールに返信いただいても、返信内容の確認及びご返信はできません。<br>
                    </p>
                </div>
                ";
                $mail->Subject = '【マイページ】退会完了のお知らせ';
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
                require_logout(); // 退会後はログアウト
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
    private function tekijukuCommemoration()
    {
        if (isloggedin() && isset($_SESSION['USER'])) {
            return $this->DB->get_record(
                'tekijuku_commemoration',
                ['fk_user_id' => $this->USER->id, 'is_delete' => 0],
                'id, email, name'
            );
        }
        return null;
    }
}
