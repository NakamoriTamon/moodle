<?php
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/lib/moodlelib.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventCustomFieldModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CognitionModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/LectureFormatModel.php');
require_once('/var/www/html/moodle/custom/app/Models/PaymentTypeModel.php');
require_once('/var/www/html/moodle/custom/app/Models/TekijukuCommemorationModel.php');
require_once($CFG->libdir . '/filelib.php');

use Dotenv\Dotenv;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

// ログイン判定
if (isloggedin() && isset($_SESSION['USER'])) {
    $user_id = $_SESSION['USER']->id;
    $user_email = $_SESSION['USER']->email;
} else {
    $_SESSION['message_error'] = 'ユーザ情報が取得できませんでした。ログインしてください。';
    header('Location: /custom/app/Views/front/index.php');
    return;
}

$result = true;

$eventId = htmlspecialchars(required_param('event_id', PARAM_INT), ENT_QUOTES, 'UTF-8');
$courseInfoId = htmlspecialchars(optional_param('course_info_id', 0, PARAM_INT));
$courseInfoId = $courseInfoId == 0 ? null : $courseInfoId;
$participation_fee = 0;
$eventModel = new eventModel();

$event_kbn = htmlspecialchars(optional_param('event_kbn', '', PARAM_INT));
$event_application_package_type = EVENT_APPLICATION_PACKAGE_TYPE['SINGLE'];
if ($event_kbn == PLURAL_EVENT && !is_null($courseInfoId)) { // 複数コース　単発申し込み
    $event = $eventModel->getEventByIdAndCourseInfoId($eventId, $courseInfoId);
    // イベント情報がなかった場合
    if (is_null($event)) {
        $_SESSION['message_error'] = 'イベント情報がありません。再度ご確認お願い致します。';
        header('Location: /custom/app/Views/event/index.php');
        return;
    }
    $participation_fee = $event['single_participation_fee'];
} else { // 複数コース　一括申し込み もしくは 単発コース
    $event = $eventModel->getEventById($eventId);
    // イベント情報がなかった場合
    if (is_null($event)) {
        $_SESSION['message_error'] = 'イベント情報がありません。再度ご確認お願い致します。';
        header('Location: /custom/app/Views/event/index.php');
        return;
    }
    $participation_fee = $event['participation_fee'];
    $event_application_package_type = $event_kbn == PLURAL_EVENT ? EVENT_APPLICATION_PACKAGE_TYPE['BUNDLE'] : EVENT_APPLICATION_PACKAGE_TYPE['SINGLE'];
}
// 毎日開催イベント
if ($event_kbn == EVERY_DAY_EVENT && !is_null($courseInfoId)) {
    $event = $eventModel->getEventByIdAndCourseInfoId($eventId, $courseInfoId);
    // イベント情報がなかった場合
    if (is_null($event)) {
        $_SESSION['message_error'] = 'イベント情報がありません。再度ご確認お願い致します。';
        header('Location: /custom/app/Views/event/index.php');
        return;
    }
    $participation_fee = $event['single_participation_fee'];
}

$tekijukuCommemorationModel = new TekijukuCommemorationModel();
$tekijuku = $tekijukuCommemorationModel->getTekijukuUserByPaid($user_id);

$tekijuku_discount = 0;
if ($tekijuku !== false && ((int)$tekijuku['paid_status'] === PAID_STATUS['COMPLETED'] || (int)$tekijuku['paid_status'] === PAID_STATUS['SUBSCRIPTION_PROCESSING'])) {
    $tekijuku_discount = empty($event['tekijuku_discount']) ? 0 : $event['tekijuku_discount'];
}

$categoryModel = new CategoryModel();
$lectureFormatModel = new LectureFormatModel();
$categorys = $categoryModel->getCategories();
$lectureFormats = $lectureFormatModel->getLectureFormats();

$select_lecture_formats = [];
$select_categorys = [];
$select_courses = [];
if (!empty($event)) {

    foreach ($event['lecture_formats'] as $lecture_format) {
        $lecture_format_id = $lecture_format['lecture_format_id'];

        foreach ($lectureFormats as $lectureFormat) {
            if ($lectureFormat['id'] == $lecture_format_id) {
                $select_lecture_formats[] = $lectureFormat;
                break;
            }
        }
    }

    foreach ($event['categorys'] as $select_category) {
        $category_id = $select_category['category_id'];

        foreach ($categorys as $category) {
            if ($category['id'] == $category_id) {
                $select_categorys[] = $category;
                break;
            }
        }
    }

    foreach ($event['course_infos'] as $select_course) {
        $select_courses[$select_course['no']] = $select_course;
    }
}

$name = htmlspecialchars(required_param('name', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$kana = htmlspecialchars(required_param('kana', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars(required_param('email', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$age = htmlspecialchars(optional_param('age', '', PARAM_INT));
// 枚数
$ticket = htmlspecialchars(required_param('ticket', PARAM_INT), ENT_QUOTES, 'UTF-8');
// $_SESSION['errors']['ticket'] = validate_int($ticket, '枚数', true); // バリデーションチェック
if(!empty($event) && $event['capacity'] > 0){
    $aki_ticket = $event['capacity'];
    $eventApplicationModel_check_ticket = new EventApplicationModel();
    $result_check_ticket = $eventApplicationModel_check_ticket->getSumTicketCountByEventId($eventId, empty($courseInfoId) ? null : $courseInfoId, true);
    if(!empty($result_check_ticket)) {
        $ticket_data = $result_check_ticket[0];
        $aki_ticket = $ticket_data['available_tickets'];
    }
    $_SESSION['errors']['ticket'] = validate_ticket($ticket, $aki_ticket); // バリデーションチェック
}else{
    $_SESSION['errors']['ticket'] = validate_ticket($ticket); // バリデーションチェック
}

$price =  htmlspecialchars(required_param('price', PARAM_INT), ENT_QUOTES, 'UTF-8');
if ($price != $ticket * $participation_fee - $tekijuku_discount) {
    $_SESSION['message_error'] = '支払い料金が変更されました。ご確認の上、再度お申し込みしてください。';
    $result = false;
}
$triggers = htmlspecialchars(required_param('triggers', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['triggers'] = validate_array($triggers, '', true) ? "どこで本イベントを知ったか選択してください。" : null;
if (is_null($_SESSION['errors']['triggers'])) {
    // カンマで分割して配列にする
    $triggerArray = explode(',', $triggers);
} else {
    $triggerArray = [];
}
$trigger_other = htmlspecialchars(required_param('trigger_other', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['trigger_other'] = validate_textarea($trigger_other, 'その他', false);
$pay_method = htmlspecialchars(required_param('pay_method', PARAM_INT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['pay_method'] = validate_select($pay_method, '支払方法', true); // バリデーションチェック
$note = htmlspecialchars(optional_param('note', '', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['note'] = validate_textarea($note, '備考欄', false);
if ($ticket > 1) {
    $companion_mails = htmlspecialchars(optional_param('companion_mails', '', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
    $companion_mails = explode(',', $companion_mails);
} else {
    $companion_mails = [];
}
$_SESSION['errors']['companion_mails'] = null;
$mails = [];
$mails[] = $email;
foreach ($companion_mails as $companion_mail) {
    $_SESSION['errors']['companion_mails'] = validate_custom_email($companion_mail) ? "受講する人のメールアドレスを入力してください。" : null;
    if (!is_null($_SESSION['errors']['companion_mails'])) {
        $result = false;
    } else {
        $mails[] = $companion_mail;
    }
}
$event_customfield_category_id = htmlspecialchars(optional_param('event_customfield_category_id', 0, PARAM_INT), ENT_QUOTES, 'UTF-8');
$guardian_kbn = htmlspecialchars(optional_param('guardian_kbn', 0, PARAM_INT), ENT_QUOTES, 'UTF-8');
$contact_phone = $_SESSION['USER']->phone1;
$applicant_kbn = htmlspecialchars(optional_param('applicant_kbn', 0, PARAM_INT), ENT_QUOTES, 'UTF-8');
$guardian_name = htmlspecialchars(optional_param('guardian_name', '', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$guardian_email = htmlspecialchars(optional_param('guardian_email', '', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$guardian_phone = htmlspecialchars(optional_param('guardian_phone', '', PARAM_TEXT), ENT_QUOTES, 'UTF-8');
$guardian_phone = removeHyphens($guardian_phone);
$notification_kbn = htmlspecialchars(optional_param('notification_kbn', 0, PARAM_INT), ENT_QUOTES, 'UTF-8');

if (!empty($guardian_kbn) && ADULT_AGE >= $age) {
    $_SESSION['errors']['guardian_name'] = validate_text($guardian_name, '保護者名', 225, true);
    $_SESSION['errors']['guardian_email'] = validate_custom_email($guardian_email, '保護者の');
    $_SESSION['errors']['guardian_phone'] = validate_tel_number($guardian_phone);
} else {
    $_SESSION['errors']['guardian_name'] = null;
    $_SESSION['errors']['guardian_email'] = null;
    $_SESSION['errors']['guardian_phone'] = null;
}

$event_customfield_category_id =  htmlspecialchars(required_param('event_customfield_category_id', PARAM_INT), ENT_QUOTES, 'UTF-8');
$eventCustomFieldModel = new EventCustomFieldModel();
$fieldList = [];
$fieldInputDataList = [];
$params = [];
if (!empty($event_customfield_category_id)) {
    $fieldList = $eventCustomFieldModel->getCustomFieldById($event_customfield_category_id);
    foreach ($fieldList as $fields) {
        $input_value = null;
        $tag_name = $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'];
        if ($fields['field_type'] == 3) {
            $input_data = optional_param($tag_name, '', PARAM_TEXT);
            $input_data = explode(",", $input_data);
            $params[$tag_name] = $input_data;
            $options = explode(",", $fields['selection']);

            foreach ($options as $i => $option) {
                if (in_array($option, $input_data)) {
                    if ($i == 0) {
                        $input_value = $option;
                        continue;
                    }
                    $input_value .= ',' . $option;
                }
            }
        } elseif ($fields['field_type'] == 4) {
            $input_value = optional_param($tag_name, '', PARAM_TEXT);
            $options = explode(",", $fields['selection']);
            foreach ($options as $i => $option) {
                if ($option == $input_value) {
                    $input_value = $option;
                    $params[$tag_name] = $input_value;
                    break;
                }
            }
            if (!isset($params[$tag_name])) {
                $params[$tag_name] = "";
            }
        } else {
            $input_value = optional_param($tag_name, '', PARAM_TEXT);
            $params[$tag_name] = $input_value;
        }

        if (!empty($input_value)) {
            $fieldInputDataList[] = ['event_customfield_id' => $fields['id'], 'field_type' => $fields['field_type'], 'input_data' => $input_value];
        }
    }
}

// エラーがある場合
if (
    $_SESSION['errors']['ticket']
    || $_SESSION['errors']['pay_method']
    || $_SESSION['errors']['triggers']
    || $_SESSION['errors']['trigger_other']
    || $_SESSION['errors']['note']
    || $_SESSION['errors']['companion_mails']
    || $_SESSION['errors']['guardian_name']
    || $_SESSION['errors']['guardian_email']
    || $_SESSION['errors']['guardian_phone']
) {
    $_SESSION['message_error'] = '登録に失敗しました。再度情報を入力してください。';
    $result = false;
}

if ($result) {
    // 申込登録処理
    try {
        $baseModel = new BaseModel();
        $pdo = $baseModel->getPdo();
        $pdo->beginTransaction();

        // 1. イベントをロック
        $stmt = $pdo->prepare("SELECT capacity FROM mdl_event WHERE id = :event_id FOR UPDATE");
        $stmt->execute([':event_id' => $eventId]);
        $event_capacity = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$event_capacity) throw new Exception("イベントが存在しません。");

        $capacity = (int)$event_capacity['capacity'];

        // 2. コース一覧取得（courseInfoId指定有無で分岐）
        if ($courseInfoId) {
            $courseIds = [$courseInfoId];
        } else {
            $stmt = $pdo->prepare("SELECT course_info_id FROM mdl_event_course_info WHERE event_id = :event_id");
            $stmt->execute([':event_id' => $eventId]);
            $courseIds = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'course_info_id');
        }

        if (empty($courseIds)) throw new Exception("対象コースが存在しません。");

        // 3. 対象コースの申込行をロック（FOR UPDATE）
        $inClause = implode(',', array_fill(0, count($courseIds), '?'));
        $params = array_merge([$eventId], $courseIds);
        $stmt = $pdo->prepare("
            SELECT eac.course_info_id
            FROM mdl_event_application_course_info eac
            JOIN mdl_event_application ea ON ea.id = eac.event_application_id
                AND ea.payment_kbn != 2
            WHERE ea.event_id = ? AND eac.course_info_id IN ($inClause)
            FOR UPDATE
        ");
        $stmt->execute($params);

        // 4. 各コースごとの申込数を取得
        $stmt = $pdo->prepare("
            SELECT eac.course_info_id, COUNT(*) AS ticket_count
            FROM mdl_event_application_course_info eac
            JOIN mdl_event_application ea ON ea.id = eac.event_application_id
                AND ea.payment_kbn != 2
            WHERE ea.event_id = ? AND eac.course_info_id IN ($inClause)
            AND eac.participation_kbn <> ".PARTICIPATION_KBN['CANCEL']." "
            ."GROUP BY eac.course_info_id
        ");
        $stmt->execute(array_merge([$eventId], $courseIds));
        $ticketCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // course_info_id => ticket_count

        // 5. 各コースの残チケット数を計算
        $minRemaining = PHP_INT_MAX;
        foreach ($courseIds as $cid) {
            $used = isset($ticketCounts[$cid]) ? (int)$ticketCounts[$cid] : 0;
            $remaining = $capacity - $used;
            if ($remaining < $minRemaining) {
                $minRemaining = $remaining;
            }
        }

        // 2. 定員超過のチェック 定員：無制限(0)、または定員数1人以上で定員数1が受付済みのチケット枚数 + 注文しているチケット枚数以下であること
        if ($capacity < 1 || $minRemaining >= $ticket) {
            $itmt = $pdo->prepare("
                INSERT INTO mdl_event_application (
                    event_id, user_id, event_custom_field_id, field_value
                    , name, name_kana, email, ticket_count, price, pay_method
                    , request_mail_kbn, applicant_kbn, application_date
                    , note, contact_phone, guardian_name, guardian_email
                    , guardian_phone, event_application_package_types
                ) VALUES (
                    :event_id , :user_id , :event_custom_field_id , :field_value
                    , :name , :name_kana , :email , :ticket_count , :price , :pay_method
                    , :request_mail_kbn , :applicant_kbn , CURRENT_TIMESTAMP
                    , :note , :contact_phone , :guardian_name , :guardian_email
                    , :guardian_phone, :event_application_package_types
                )
            ");

            $itmt->execute([
                ':event_id' => $eventId,
                ':user_id' => $user_id,
                ':event_custom_field_id' => $event_customfield_category_id,
                ':field_value' => '',
                ':name' => $name,
                ':name_kana' => $kana,
                ':email' => $email,
                ':ticket_count' => $ticket,
                ':price' => $price,
                ':pay_method' => $pay_method,
                ':request_mail_kbn' => $notification_kbn,
                ':applicant_kbn' => $applicant_kbn,
                ':note' => $note,
                ':contact_phone' => $contact_phone,
                ':guardian_name' => $guardian_name,
                ':guardian_email' => $guardian_email,
                ':guardian_phone' => $guardian_phone,
                ':event_application_package_types' => $event_application_package_type
            ]);


            // mdl_eventの挿入IDを取得
            $eventApplicationId = $pdo->lastInsertId();

            // 知った経由　mdl_event_application_cognition
            $itmt2 = $pdo->prepare("
                INSERT INTO mdl_event_application_cognition (created_at, updated_at, event_application_id, cognition_id, note) 
                VALUES (CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :event_application_id, :cognition_id, :note)
            ");
            foreach ($triggerArray as $cognition_id) {
                $itmt2->execute([
                    ':event_application_id' => $eventApplicationId,
                    ':cognition_id' => trim($cognition_id), // 空白を除去
                    ':note' => $trigger_other
                ]);
            }


            // 申し込み～コース中間テーブル　mdl_event_application_course_info
            $itmt3 = $pdo->prepare("
                    INSERT INTO mdl_event_application_course_info (created_at, updated_at, event_id, event_application_id, course_info_id, participant_mail, ticket_type) 
                    VALUES (CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :event_id, :event_application_id, :course_info_id, :participant_mail, :ticket_type)
                ");
            foreach ($mails as $mail) {
                $ticket_type = TICKET_TYPE['SELF'];
                if ($user_email !== $mail) {
                    $ticket_type = TICKET_TYPE['ADDITIONAL'];
                }
                foreach ($select_courses as $courses) {
                    $itmt3->execute([
                        ':event_id' => $eventId,
                        ':event_application_id' => $eventApplicationId,
                        ':course_info_id' => $courses['id'], // 空白を除去
                        ':participant_mail' => $mail,
                        ':ticket_type' => $ticket_type
                    ]);
                }
            }

            // カスタムフィールドがある場合
            if (!empty($event_customfield_category_id)) {
                foreach ($fieldInputDataList as $fieldInputData) {
                    $itmt4 = $pdo->prepare("
                        INSERT INTO mdl_event_application_customfield (created_at, updated_at, event_application_id, event_customfield_id, field_type, input_data) 
                        VALUES (CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :event_application_id, :event_customfield_id, :field_type, :input_data)
                    ");
                    $itmt4->execute([
                        ':event_application_id' => $eventApplicationId,
                        ':event_customfield_id' => $fieldInputData['event_customfield_id'],
                        ':field_type' => $fieldInputData['field_type'],
                        ':input_data' => $fieldInputData['input_data']
                    ]);
                }
            }

            $itmt5 = $pdo->prepare("
                UPDATE mdl_user
                SET 
                    notification_kbn = :notification_kbn
                WHERE id = :id
            ");

            $itmt5->execute([
                ':notification_kbn' => $notification_kbn,
                ':id' => $user_id // 一意の識別子をWHERE条件として設定
            ]);

            // 無料の場合
            if ($price < 1) {
                $pdo->commit();

                $dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
                $dotenv->load();

                $eventApplicationModel = new EventApplicationModel();
                $eventApplication = $eventApplicationModel->getEventApplicationByEventId($eventApplicationId);

                $user_name = $name;
                foreach ($eventApplication['course_infos'] as $course) {
                    global $url_secret_key;
                    $ticket_type = TICKET_TYPE['SELF'];
                    if ($user_email !== $mail) {
                        $ticket_type = TICKET_TYPE['ADDITIONAL'];
                    }
                    $encrypt_event_application_course_info_id = encrypt($course['id'], $url_secret_key);

                    // SESのクライアント設定
                    $SesClient = new SesClient([
                        'version' => 'latest',
                        'region'  => 'ap-northeast-1', // 東京リージョン
                        'credentials' => [
                            'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
                            'secret' => $_ENV['AWS_SECRET_ACCESS_KEY_ID'],
                        ]
                    ]);

                    $recipients = [$_POST['email']];
                    if (!empty($_POST['other_mail_adress'])) {
                        $recipients = array_merge($recipients, explode(',', $_POST['other_mail_adress']));
                    }

                    $ymd = "";
                    if($event_kbn == EVERY_DAY_EVENT) {
                        $ymd = (new DateTime($event['start_event_date']))->format('Y年m月d日') . "～" . (new DateTime($event['end_event_date']))->format('Y年m月d日');
                    } else {
                        $day = new DateTime($course["course_date"]);
                        $course_date = $day->format('Ymd');
                        $ymd = $day->format('Y/m/d');
                    }
                    $dateTime = DateTime::createFromFormat('H:i:s', $event['start_hour']);
                    $start_hour = $dateTime->format('H:i'); // "00:00"
                    $dateTime = DateTime::createFromFormat('H:i:s', $event['end_hour']);
                    $end_hour = $dateTime->format('H:i'); // "00:00"

                    // ✅ QRコードを生成（バイナリデータ）
                    $qrCode = new QrCode($encrypt_event_application_course_info_id);
                    $writer = new PngWriter();
                    $qrCodeImage = $writer->write($qrCode)->getString();
                    $qr_base64 = base64_encode($qrCodeImage);

                    // ✅ MIME メッセージの作成
                    $boundary = md5(time());

                    $rawMessage = "From: 知の広場 <{$_ENV['MAIL_FROM_ADDRESS']}>\r\n";
                    $rawMessage .= "To: " . implode(',', $recipients) . "\r\n";
                    $rawMessage .= "Subject: =?UTF-8?B?" . base64_encode("チケットのお申し込みが完了しました") . "?=\r\n";
                    $rawMessage .= "MIME-Version: 1.0\r\n";
                    $rawMessage .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";

                    $dear = !empty($user_name) ? '様' : '';
                    $htmlBody = "
                        <div style=\"text-align: center; font-family: Arial, sans-serif;\">
                            <p style=\"text-align: left; font-weight:bold;\">" . $user_name . $dear . "</p><br />
                            <P style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">お申込みありがとうございます。チケットのお申し込みが完了いたしました。</P>
                            <P style=\"text-align: left;  font-size: 13px; margin:0; margin-bottom: 30px; \">QRはマイページでも確認できます。</P>
                            <div>
                                <img src=\"cid:qr_code_cid\" alt=\"QR Code\" style=\"width: 150px; height: 150px; display: block; margin: 0 auto;\" />
                            </div>
                            <p style=\"margin-top: 20px; font-size: 14px;\">" . $event["name"] . "</p>
                            <p style=\"margin-top: 20px; font-size: 14px;\">開催回数：第" . $select_course['no'] . "回</p>
                            <p style=\"margin-top: 20px; font-size: 14px;\">開催日：" . $ymd . "</p>
                            <p style=\"margin-top: 20px; font-size: 14px;\">時間　：" . $start_hour . "～" . $end_hour . "</p><br />
                            <p style=\"margin-top: 30px; font-size: 13px; text-align: left;\">このメールは、配信専用アドレスで配信されています。<br>このメールに返信いただいても、返信内容の確認及びご返信ができません。
                            あらかじめご了承ください。</p>
                        </div>
                    ";

                    $rawMessage .= "--{$boundary}\r\n";
                    $rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n";
                    $rawMessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
                    $rawMessage .= $htmlBody . "\r\n\r\n";
                    
                    // ✅ QRコード画像の添付（インライン）
                    $rawMessage .= "--{$boundary}\r\n";
                    $rawMessage .= "Content-Type: image/png; name=\"qr_code.png\"\r\n";
                    $rawMessage .= "Content-Description: QR Code\r\n";
                    $rawMessage .= "Content-Disposition: inline; filename=\"qr_code.png\"\r\n";
                    $rawMessage .= "Content-ID: <qr_code_cid>\r\n";
                    $rawMessage .= "Content-Transfer-Encoding: base64\r\n\r\n";
                    $rawMessage .= chunk_split($qr_base64) . "\r\n\r\n";
                    
                    $rawMessage .= "--{$boundary}--";
                    
                    // ✅ SES で送信
                    try {
                        $result = $SesClient->sendRawEmail([
                            'RawMessage' => [
                                'Data' => $rawMessage
                            ],
                            'ReplyToAddresses' => ['no-reply@example.com'],
                            'Source' => $_ENV['MAIL_FROM_ADDRESS'],
                            'Destinations' => $recipients
                        ]);
                    } catch (AwsException $e) {
                        error_log('イベント申込確認メール送信エラー: ' . $e->getMessage());
                        $_SESSION['message_error'] = '送信に失敗しました';
                        redirect('/custom/app/Views/user/pass_mail.php');
                        exit;
                    }
                }

                $_SESSION['payment_method_type'] = $pay_method;

                header('Location: /custom/app/Views/event/complete.php');
                exit;
            } else {
                $paymentTypeModel = new PaymentTypeModel();
                $paymentType = $paymentTypeModel->getPaymentTypesById($pay_method);
                $type = $paymentType['payment_type'];
            }

            // 決済データ（サンプル）
            $data = [
                'payment_types' => [$type], // 利用可能な決済手段
                'amount' => $price,
                'currency' => 'JPY',
                'external_order_num' => uniqid(),
                'return_url' => $CFG->wwwroot . '/custom/app/Views/event/complete.php?id=' . $eventId, // 決済成功後のリダイレクトURL
                'cancel_url' => $CFG->wwwroot . '/custom/app/Views/event/pre_registration.php', // キャンセル時のリダイレクトURL
                'metadata' => [
                    'user_name' => $_SESSION['USER']->name,
                    'event_id' => $eventId,
                    'event_application_id' => $eventApplicationId,
                    'payment_method_type' => $pay_method,
                    'user_email' => $user_email,
                ],
            ];

            $_SESSION['payment_method_type'] = $pay_method;

            // ヘッダーの設定
            $headers = [
                'Authorization: Basic ' . base64_encode($komoju_api_key),
                'Content-Type: application/json',
            ];

            // cURLオプションの設定
            $ch = curl_init($komoju_endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // レスポンスを文字列で返す
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // ヘッダーを設定
            curl_setopt($ch, CURLOPT_POST, true); // POSTメソッド
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // POSTデータ

            // 結果を取得
            $response = curl_exec($ch);

            // ステータスコードの取得
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response) {
                $result = json_decode($response, true);
            }

            // セッションURLが取得できたらリダイレクト
            if (isset($result['session_url'])) {
                // セッションをクリア
                unset($SESSION->formdata);
                // header("Location: " . $result['session_url']);
                $redirect_url = $result['session_url'];

                $itmt6 = $pdo->prepare("
                    UPDATE mdl_event_application
                    SET 
                        komoju_url = :komoju_url
                    WHERE id = :id
                ");

                $itmt6->execute([
                    ':komoju_url' => $redirect_url,
                    ':id' => $eventApplicationId // 一意の識別子をWHERE条件として設定
                ]);

                $pdo->commit();

                echo "<script>window.location.href='$redirect_url';</script>";
                exit;
            } else {
                throw new Exception("決済ページ取得に失敗しました");
            }
        } else {
            // ロールバック
            $pdo->rollBack();
            $_SESSION['message_error'] = '定員を超えているため、申込できません。';
            if (!is_null($courseInfoId)) {
                header('Location: /custom/app/Views/event/apply.php?id=' . $eventId . '&course_info_id=' . $courseInfoId);
                $event = $eventModel->getEventByIdAndCourseInfoId($eventId, $courseInfoId);
            } else {
                header('Location: /custom/app/Views/event/apply.php?id=' . $eventId);
            }
            exit;
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('イベント申込登録エラー: ' . $e->getMessage());
        $_SESSION['message_error'] = '登録に失敗しました';
        if (!is_null($courseInfoId)) {
            header('Location: /custom/app/Views/event/apply.php?id=' . $eventId . '&course_info_id=' . $courseInfoId);
            $event = $eventModel->getEventByIdAndCourseInfoId($eventId, $courseInfoId);
        } else {
            header('Location: /custom/app/Views/event/apply.php?id=' . $eventId);
        }
        exit;
    }
} else {
    // セッションをクリア
    unset($_SESSION['errors']);
    // 修正(申込登録画面に戻る)
    // 入力画面に戻る
    $SESSION->formdata = [
        'id' => $eventId,
        'course_info_id' => $courseInfoId,
        'name' => $name,
        'kana' => $kana,
        'email' => $email,
        'price' => $price,
        'ticket' => $ticket,
        'trigger_other' => $trigger_other,
        'pay_method' => $pay_method,
        'notification_kbn' => $notification_kbn,
        'triggers' => $triggers,
        'note' => $note,
        'companion_mails' => $companion_mails,
        'applicant_kbn' => $applicant_kbn,
        'guardian_kbn' => $guardian_kbn,
        'guardian_name' => $guardian_name,
        'guardian_email' => $guardian_email,
        'guardian_phone' => $guardian_phone,
        'event_customfield_category_id' => $event_customfield_category_id,
        'params' => $params
    ];
    redirect(new moodle_url('/custom/app/Views/event/apply.php?id=' . $eventId));
}

function removeHyphens($phone)
{
    // 全角を半角に変換
    $phone = mb_convert_kana($phone, 'a');
    // ハイフンを削除
    return str_replace('-', '', $phone);
}

function encrypt($id, $key)
{
    $iv = substr(hash('sha256', $key), 0, 16);
    return urlencode(base64_encode(openssl_encrypt($id, 'AES-256-CBC', $key, 0, $iv)));
}
