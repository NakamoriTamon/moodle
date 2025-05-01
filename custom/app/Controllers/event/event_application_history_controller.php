<?php
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/CourseInfoModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventApplicationCourseInfoModel.php');

class EventHistoryController
{

    private $eventApplicationCourseInfoModel;

    public function __construct()
    {
        $this->eventApplicationCourseInfoModel = new EventApplicationCourseInfoModel();
    }


    public function index($data)
    {
        global $USER;
        $course_id = $data['course_id'];
        $application_id = $data['id'];

        // var_dump($application_id); // デバッグ用の出力を削除
        $histry_list = $this->eventApplicationCourseInfoModel->getByCourseInfoId($course_id, null, 1, 1000000);

        // 自身のユーザーのみ取得する
        foreach ($histry_list as $key => $histry) {
            if ($histry['event_application_id'] != $application_id) {
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
        $event_name =  $no . $common_application['event']['name'];
        $price = $common_application['price'] != 0 ? number_format($common_application['price']) . '円' : '無料';
        $pay_method = $common_application['pay_method'] == 4 ? '' : PAYMENT_SELECT_LIST[$common_application['pay_method']];
        $is_payment = empty($common_applicationt['payment_date']) ? '未決済' : '決済済';
        $child_name = $common_application['user']['child_name'];

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

        // インデックスが再割り当てされているので、必要なら再インデックス化する
        $companion_array = array_values($companion_array);

        $data = [
            'common_array' => $common_array,
            'common_application' => $common_application,
            'event_name' => $event_name,
            'price' => $price,
            'pay_method' => $pay_method,
            'is_payment' => $is_payment,
            'companion_array' => $companion_array,
            'child_name' => $child_name,
        ];

        return $data;
    }
}
