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
require_once($CFG->libdir . '/filelib.php');

use Dotenv\Dotenv;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ログイン判定
if (isloggedin() && isset($_SESSION['USER'])) {
    $user_id = $_SESSION['USER']->id;
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

$event_kbn = htmlspecialchars(optional_param('event_kbn', '' , PARAM_INT));
if ($event_kbn == PLURAL_EVENT) {
    $event = $eventModel->getEventByIdAndCourseInfoId($eventId, $courseInfoId);
    $participation_fee = $event['single_participation_fee'];
} else {
    $event = $eventModel->getEventById($eventId);
    $participation_fee = $event['participation_fee'];
}

// イベント情報がなかった場合
if (is_null($event)) {
    header('Location: /custom/app/Views/event/index.php');
    return;
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
$age = htmlspecialchars(optional_param('age', '' , PARAM_INT));
// 枚数
$ticket = htmlspecialchars(required_param('ticket', PARAM_INT), ENT_QUOTES, 'UTF-8');
$_SESSION['errors']['ticket'] = validate_int($ticket, '枚数', true); // バリデーションチェック
$price =  htmlspecialchars(required_param('price', PARAM_INT), ENT_QUOTES, 'UTF-8');
if ($price != $ticket * $participation_fee) {
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

if(!empty($guardian_kbn) && ADULT_AGE >= $age) {
    $_SESSION['errors']['guardian_name'] = validate_text($guardian_name, '保護者名', 225, true);
    $_SESSION['errors']['guardian_email'] = validate_custom_email($guardian_email, '保護者の');
    $_SESSION['errors']['guardian_phone'] = validate_tel_number($guardian_phone);
} else {
    $_SESSION['errors']['guardian_name'] = null;
    $_SESSION['errors']['guardian_email'] = null;
    $_SESSION['errors']['guardian_phone'] = null;
}

$event_customfield_category_id =  htmlspecialchars(required_param('event_customfield_category_id', PARAM_INT), ENT_QUOTES, 'UTF-8');
$eventCustomFieldModel = new eventCustomFieldModel();
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

        // 1. イベントのcapacityと現在のチケット枚数をロック
        $stmt = $pdo->prepare("
            SELECT e.capacity, COALESCE(SUM(a.ticket_count), 0) AS current_count
            FROM mdl_event e
            LEFT JOIN mdl_event_application a ON e.id = a.event_id
            WHERE e.id = :event_id
            FOR UPDATE
        ");
        $stmt->execute([':event_id' => $eventId]);
        $event_capacity = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event_capacity) {
            throw new Exception("イベントが見つかりません。");
        }

        $capacity = (int)$event_capacity['capacity'];
        $currentCount = (int)$event_capacity['current_count'];
        $participation_fee = (int)$event['participation_fee'];
        // 2. 定員超過のチェック 定員：無制限(0)、または定員数1人以上で定員数1が受付済みのチケット枚数 + 注文しているチケット枚数以下であること
        if ($capacity < 1 || ($capacity > 0 && ($currentCount + $ticket) <= $capacity)) {
            $itmt = $pdo->prepare("
                INSERT INTO mdl_event_application (
                    event_id, user_id, event_custom_field_id, field_value
                    , name, name_kana, email, ticket_count, price, pay_method
                    , request_mail_kbn, applicant_kbn, application_date
                    , note, contact_phone, guardian_name, guardian_email, guardian_phone
                ) VALUES (
                    :event_id , :user_id , :event_custom_field_id , :field_value
                    , :name , :name_kana , :email , :ticket_count , :price , :pay_method
                    , :request_mail_kbn , :applicant_kbn , CURRENT_TIMESTAMP
                    , :note , :contact_phone , :guardian_name , :guardian_email, :guardian_phone
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
                ':guardian_phone' => $guardian_phone
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


            // 知った経由　mdl_event_application_course_info
            $itmt3 = $pdo->prepare("
                    INSERT INTO mdl_event_application_course_info (created_at, updated_at, event_id, event_application_id, course_info_id, participant_mail) 
                    VALUES (CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :event_id, :event_application_id, :course_info_id, :participant_mail)
                ");
            foreach ($mails as $mail) {
                foreach ($select_courses as $courses) {
                    $itmt3->execute([
                        ':event_id' => $eventId,
                        ':event_application_id' => $eventApplicationId,
                        ':course_info_id' => $courses['id'], // 空白を除去
                        ':participant_mail' => $mail
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
            if ($event['participation_fee'] < 1 ) {
                $pdo->commit();
                
                $dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
                $dotenv->load();

                $eventApplicationModel = new EventApplicationModel();
                $eventApplication = $eventApplicationModel->getEventApplicationByEventId($eventApplicationId);

                foreach($eventApplication['course_infos'] as $course) {
                    // QR生成
                    $baseUrl = $CFG->wwwroot; // MoodleのベースURL（本番環境では自動で変更される）
                    $qrCode = new QrCode($baseUrl . '/custom/app/Controllers/event/event_proof_controller.php?event_application_id='
                        . $eventApplicationId . '&event_application_course_info=' . $course['id']);
                    $writer = new PngWriter();
                    $qrCodeImage = $writer->write($qrCode)->getString();
                    $temp_file = tempnam(sys_get_temp_dir(), 'qr_');
                    $qrCodeBase64 = base64_encode($qrCodeImage);
                    $dataUri = 'data:image/png;base64,' . $qrCodeBase64;
                    file_put_contents($temp_file, $qrCodeImage);
                
                    $mail = new PHPMailer(true);
                
                    $mail->isSMTP();
                    $test = getenv('MAIL_HOST');
                    $mail->Host = $_ENV['MAIL_HOST'];
                    $mail->SMTPAuth = true;
                    $mail->Username = $_ENV['MAIL_USERNAME'];
                    $mail->Password = $_ENV['MAIL_PASSWORD'];
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->CharSet = PHPMailer::CHARSET_UTF8;
                    $mail->Port = $_ENV['MAIL_PORT'];
                
                    $mail->setFrom($_ENV['MAIL_FROM_ADRESS'], 'Sender Name');
                    $mail->addAddress($course['participant_mail'], 'Recipient Name');
                
                    $sendAdresses = ['tamonswallow@gmail.com'];
                    foreach ($sendAdresses as $sendAdress) {
                        $mail->addAddress($sendAdress, 'Recipient Name');
                    }
                    $mail->addReplyTo('no-reply@example.com', 'No Reply');
                    $mail->isHTML(true);
                    
                    $day = new DateTime($course["course_date"]);
                    $course_date = $day->format('Ymd');
                    $ymd = $day->format('Y/m/d');
                    $dateTime = DateTime::createFromFormat('H:i:s', $event['start_hour']);
                    $start_hour = $dateTime->format('H:i'); // "00:00"
                    $dateTime = DateTime::createFromFormat('H:i:s', $event['end_hour']);
                    $end_hour = $dateTime->format('H:i'); // "00:00"
                    $qr_img = 'qr_code_' . $course_date . '.png';
                    // QRをインライン画像で追加
                    $mail->addEmbeddedImage($temp_file, 'qr_code_cid', $qr_img);
                
                    $htmlBody = "
                        <div style=\"text-align: center; font-family: Arial, sans-serif;\">
                            <p style=\"text-align: left; font-weight:bold;\">" . $name . "</p>
                            <P style=\"text-align: left; font-size: 13px; margin:0; padding:0;\">ご購入ありがとうございます。チケットのご購入が完了いたしました。</P>
                            <P style=\"text-align: left;  font-size: 13px; margin:0; margin-bottom: 30px; \">QRはマイページでも確認できます。</P>
                            <div>
                                <img src=\"cid:qr_code_cid\" alt=\"QR Code\" style=\"width: 150px; height: 150px; display: block; margin: 0 auto;\" />
                            </div>
                            <p style=\"margin-top: 20px; font-size: 14px;\">" . $event["name"] . "</p>
                            <p style=\"margin-top: 20px; font-size: 14px;\">開催日：" . $ymd . "</p>
                            <p style=\"margin-top: 20px; font-size: 14px;\">時間　：" . $start_hour . "～" . $end_hour . "</p>
                            <p style=\"margin-top: 30px; font-size: 13px; text-align: left;\">このメールは、配信専用アドレスで配信されています。<br>このメールに返信いただいても、返信内容の確認及びご返信ができません。
                            あらかじめご了承ください。</p>
                        </div>
                    ";
                
                    $name = "";
                
                    $mail->Subject = 'チケットの購入が完了しました';
                    $mail->Body = $htmlBody;
                
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                
                    $mail->send();
                    unlink($temp_file);
                }
            
                header('Location: /custom/app/Views/mypage/index.php');
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
                'cancel_url' => $CFG->wwwroot . '/custom/app/Views/event/apply.php?id=' . $eventId . '&course_info_id=' . $courses['id'], // キャンセル時のリダイレクトURL
                'metadata' => [
                    'user_name' => $_SESSION['USER']->name,
                    'event_id' => $eventId,
                    'event_application_id' => $eventApplicationId,
                    'payment_method_type' => $pay_method,
                ],
            ];

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
                $pdo->commit();

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
        $_SESSION['message_error'] = '登録に失敗しました: ' . $e->getMessage();
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

function removeHyphens($phone) {
    // 全角を半角に変換
    $phone = mb_convert_kana($phone, 'a');
    // ハイフンを削除
    return str_replace('-', '', $phone);
}
