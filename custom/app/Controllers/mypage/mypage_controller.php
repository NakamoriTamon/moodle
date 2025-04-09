<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/lib/classes/context/system.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require '/var/www/vendor/autoload.php';

use Dotenv\Dotenv;
use core\context\system;
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

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
    private $dotenv;

    public function __construct()
    {
        global $DB, $USER;
        $this->DB = $DB;
        $this->USER = $USER;

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
            'id, name, name_kana, email, phone1, city, guardian_kbn, birthday, guardian_name, guardian_email, description, notification_kbn, child_name, guardian_phone',
        );
    }

    /**
     * $user_id ログイン中のユーザーID(会員番号)
     * return bool 管理者か否か
     */
    public function isGeneralUser($user_id)
    {
        $role_assignments = $this->DB->get_record(
            'role_assignments',
            ['userid' => $user_id],
            'roleid',
        );
        return (int)$role_assignments->roleid == ROLE['USER'] ? true : false;
    }

    // 適塾記念情報を取得
    public function getTekijukuCommemoration()
    {
        // 標準で取得するカラム
        $columns = 'id, number, type_code, name, kana, post_code, address, tell_number, email, payment_method, paid_date, note, is_published, is_subscription, is_delete, department, major, official, is_university_member';

        // 現在の日付を取得
        $current_date = new DateTime();
        $current_year = (int)$current_date->format('Y');
        $current_month = (int)$current_date->format('n');

        // 年度の計算（4月1日を年度の始まりとする）
        $fiscal_year = $current_year;
        if ($current_month < 4) {
            $fiscal_year = $current_year - 1;
        }

        // 対象年度のカラムが存在し、かつ2031年度未満であるか確認
        if ($fiscal_year >= 2024 && $fiscal_year <= 2030) {
            // 指定の年度のis_deposit_YYYYカラムを追加
            $columns .= ", is_deposit_{$fiscal_year}";
            $next_year = $fiscal_year + 1;
            if ($next_year <= 2030) {
                $columns .= ", is_deposit_{$next_year}";
            }
        }

        return $this->DB->get_record(
            'tekijuku_commemoration',
            ['fk_user_id' => $this->USER->id],
            $columns
        );
    }

    public function getEventApplications($offset = 0, $limit = 4, $page = 1, $get_application = 'booking')
    {
        try {
            global $url_secret_key;

            // limit と offset を整数にキャスト
            $limit = intval($limit);
            $offset = intval($offset);
            $page = intval($page); // 現在のページ番号

            // ページネーションの設定
            $perPage = $limit; // 1ページあたりのアイテム数
            $current_date = date('Y-m-d');

            $comparison_operator = ($get_application === 'booking') ? '>=' : '<';

            // 安全な演算子が選択されたことを確認
            if (!in_array($comparison_operator, ['>=', '<'], true)) {
                // 無効な演算子が指定された場合、処理を終了するかエラーを返す
                throw new InvalidArgumentException('Invalid comparison operator');
            }

            // 本人用チケットのみを取得（TICKET_TYPE['SELF']）
            $self_ticket_type = TICKET_TYPE['SELF'];

            // コースごとのデータを取得するためのクエリ
            // イベント申し込みコース情報を中心に据えた設計
            $course_sql = "
                WITH elf AS (
                    SELECT *
                    FROM {event_lecture_format}

                    WHERE lecture_format_id = " . FACE_TO_FACE . " OR lecture_format_id = " . LIVE . "
                )
                SELECT DISTINCT
                    eaci.id AS event_application_course_info_id,
                    ea.id AS event_application_id,
                    ea.event_id,
                    ea.user_id,
                    ea.price,
                    ea.ticket_count,
                    ea.payment_date,
                    ea.event_application_package_types,
                    e.name AS event_name,
                    e.venue_name AS venue_name,
                    e.event_kbn,
                    e.start_event_date,
                    e.end_event_date,
                    ci.id AS course_id,
                    ci.no,
                    ci.course_date,
                    eaci.participation_kbn,
                    eaci.ticket_type,
                    elf.lecture_format_id
                FROM 
                    {event_application_course_info} eaci
                JOIN 
                    {course_info} ci ON ci.id = eaci.course_info_id
                JOIN 
                    {event_application} ea ON ea.id = eaci.event_application_id
                JOIN 
                    {event} e ON e.id = ea.event_id
                JOIN 
                    elf ON elf.event_id = e.id
                WHERE 
                    ea.user_id = :user_id 
                    AND eaci.participation_kbn IS NULL
                    AND DATE_ADD(
                ci.course_date,
                INTERVAL CAST(
                    COALESCE(
                        REPLACE(e.material_release_period, ' days', ''),
                        '0'
                    ) AS SIGNED
                ) DAY
            ) "
                . $comparison_operator .
                " :current_date
                    AND eaci.ticket_type = :self_ticket_type
                ORDER BY 
                    ci.course_date ASC
                ";


            // カウント用クエリ
            $count_sql = "
                SELECT COUNT(eaci.id) as count
                    FROM 
                    {event_application_course_info} eaci
                JOIN 
                    {event_application} ea ON ea.id = eaci.event_application_id
                JOIN 
                    {course_info} ci ON ci.id = eaci.course_info_id
                JOIN 
                    {event} e ON e.id = ea.event_id
                WHERE 
                    ea.user_id = :user_id 
                   AND DATE_ADD(
                ci.course_date,
                INTERVAL CAST(
                    COALESCE(
                        REPLACE(e.material_release_period, ' days', ''),
                        '0'
                    ) AS SIGNED
                ) DAY
            ) "
                . $comparison_operator .
                " :current_date
                    AND eaci.ticket_type = :self_ticket_type
                ";

            $params = [
                'user_id' => $this->USER->id,
                'current_date' => $current_date,
                'self_ticket_type' => $self_ticket_type,
            ];

            // トータルカウントの取得
            $totalCount = (int) $this->DB->count_records_sql($count_sql, $params);

            // トータルページ数の計算
            $totalPages = ceil($totalCount / $perPage);

            // ページネーション用のLIMITとOFFSET追加
            $course_sql .= " LIMIT $limit OFFSET $offset";

            // データの取得
            $courses = $this->DB->get_records_sql($course_sql, $params);

            // IDを暗号化するためのメソッド
            $encrypt = function ($id) use ($url_secret_key) {
                $iv = substr(hash('sha256', $url_secret_key), 0, 16);
                return urlencode(base64_encode(openssl_encrypt((string)$id, 'AES-256-CBC', $url_secret_key, 0, $iv)));
            };

            // 各レコードのevent_application_course_info_idを暗号化
            foreach ($courses as &$course) {
                // 暗号化したIDを追加
                $course->encrypted_eaci_id = $encrypt($course->event_application_course_info_id);
            }

            // ページネーション情報とデータをまとめて返す
            $paginate_data = [
                'data' => $courses,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'per_page' => $perPage,
                    'total_count' => $totalCount
                ]
            ];

            return $paginate_data;
        } catch (Exception $e) {
            error_log('getEventApplications Error: ' . $e->getMessage());

            // エラー時は空の結果を返す
            return [
                'data' => [],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => 0,
                    'per_page' => $perPage,
                    'total_count' => 0
                ],
                'error' => 'イベント情報の取得に失敗しました'
            ];
        }
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
                // SESのクライアント設定
                $SesClient = new SesClient([
                    'version' => 'latest',
                    'region'  => 'ap-northeast-1', // 東京リージョン
                    'credentials' => [
                        'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
                        'secret' => $_ENV['AWS_SECRET_ACCESS_KEY_ID'],
                    ]
                ]);

                $recipients = [$email];

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

                $subject = '【マイページ】退会完了のお知らせ';

                try {
                    $result = $SesClient->sendEmail([
                        'Destination' => [
                            'ToAddresses' => $recipients,
                        ],
                        'ReplyToAddresses' => ['no-reply@example.com'],
                        'Source' => "知の広場 <{$_ENV['MAIL_FROM_ADDRESS']}>",
                        'Message' => [
                            'Subject' => [
                                'Data' => $subject,
                                'Charset' => 'UTF-8'
                            ],
                            'Body' => [
                                'Html' => [
                                    'Data' => $htmlBody,
                                    'Charset' => 'UTF-8'
                                ]
                            ]
                        ]
                    ]);
                } catch (AwsException $e) {
                    error_log('退会メール送信エラー: ' . $e->getMessage() . ' UserID: ' . $id);
                    $_SESSION['message_error'] = '送信に失敗しました';
                    redirect('/custom/app/Views/user/pass_mail.php');
                    exit;
                }
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
            error_log('ユーザー退会処理エラー: ' . $e->getMessage() . ' UserID: ' . $id);
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

    /**
     *  イベントのQR表示可否を送る
     */
    public function getEventLectureFormats($event_id_list)
    {
        $is_disp_qr_list = [];
        foreach ($event_id_list as $event_id) {
            $format_list = $this->DB->get_records('event_lecture_format', ['event_id' => $event_id]);
            foreach ($format_list as $formats) {
                if ($formats->lecture_format_id == LECTURE_FORMAT_ON_SITE) {
                    $is_disp_qr_list[$event_id] = true;
                    break;
                }
            }
            if (empty($is_disp_qr_list[$event_id])) {
                $is_disp_qr_list[$event_id] = false;
            }
        }

        return $is_disp_qr_list;
    }
}
