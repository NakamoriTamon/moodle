<?php
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/CourseInfoModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventApplicationCourseInfoModel.php');

class EventReserveController
{

    private $eventApplicationCourseInfoModel;

    public function __construct()
    {
        $this->eventApplicationCourseInfoModel = new EventApplicationCourseInfoModel();
    }


    public function index($course_id, $id, $event_application_course_info_id)
    {
        global $USER, $url_secret_key;

        $histry_list = $this->eventApplicationCourseInfoModel->getByCourseInfoId($course_id, null, 1, 1000000);

        // 自身のユーザーのみ取得する
        foreach ($histry_list as $key => $histry) {
            if ($histry['event_application_id'] != $id) {
                unset($histry_list[$key]);
                continue;
            }
            $user = reset($histry['application'])['user'];
            if ($user['id'] != $USER->id) {
                unset($histry_list[$key]);
                continue;
            }
        }

        // 配列のキーをリセット（必要なら）
        $histry_list = array_values($histry_list);
        // 各種情報を切り分ける
        $common_array = reset($histry_list);
        $common_application = $common_array['application'][0];
        $no = '【第' . $common_array['course_info']['no'] . '回】';
        $event = $common_application['event'];
        $realtime_path = $event['real_time_distribution_url'];
        $event_kbn = $event['event_kbn'];
        $event_name =  $event['name'];

        // 会場名
        $venue_name = empty($event['venue_name']) ? "" : $event['venue_name'];
        //　開催時間
        $start_hour = date('H:i', strtotime($event['start_hour']));
        $end_hour = date('H:i', strtotime($event['end_hour']));
        $format_hour = $start_hour . ' ~ ' . $end_hour;
        $format_date = "";
        if ($event_kbn == EVERY_DAY_EVENT) {
            $start_date = date('Y年m月d日', strtotime($event['start_event_date']));
            $end_date = date('Y年m月d日', strtotime($event['end_event_date']));
            $format_date = $start_date . ' ～ ' . $end_date;
        } else {
            $format_date = date('Y年m月d日', strtotime($common_array['course_info']['course_date']));
        }
        $price = $common_application['price'] != 0 ? number_format($common_application['price']) . '円' : '無料';

        if ($common_application['price'] != 0) {
            $pay_method = PAYMENT_SELECT_LIST[$common_application['pay_method']];
            $is_payment = empty($common_application['payment_date']) ? '未決済' : '決済済';
        } else {
            $pay_method = "";
            $is_payment = "";
        }

        $child_name = $common_application['companion_name'] ?? $common_application['user']['child_name'];

        $companion_array = [];
        foreach ($histry_list as $histry) {
            foreach ($histry['application'] as $application) {
                if ($USER->id == $application['user_id']) {
                    $target_id = $application['id'];
                    break;
                }
            }
        }

        $companion_email_array = $this->eventApplicationCourseInfoModel->getByApplicationId($target_id, $course_id);

        // 本人のメールアドレスは排除
        $email = $common_application['email'];
        $companion_array = array_filter($companion_email_array, function ($item) use ($email) {
            return $item['participant_mail'] !== $email;
        });

        // IDを暗号化するためのメソッド
        $encrypt = function ($id) use ($url_secret_key) {
            $iv = substr(hash('sha256', $url_secret_key), 0, 16);
            return urlencode(base64_encode(openssl_encrypt((string)$id, 'AES-256-CBC', $url_secret_key, 0, $iv)));
        };
        $encrypted_eaci_id = $encrypt($event_application_course_info_id);
        $data = [
            'common_array' => $common_array,
            'common_application' => $common_application,
            'course_number' => $no,
            'event_name' => $event_name,
            'event_kbn' => $event_kbn,
            'price' => $price,
            'pay_method' => $pay_method,
            'is_payment' => $is_payment,
            'companion_array' => $companion_array,
            'child_name' => $child_name,
            'realtime_path' => $realtime_path,
            'format_date' => $format_date,
            'format_hour' => $format_hour,
            'venue_name' => $venue_name,
            'encrypted_eaci_id' => $encrypted_eaci_id
        ];

        return $data;
    }
}
