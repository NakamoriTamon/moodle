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


    public function index($course_id)
    {
        global $USER;
        $histry_list = $this->eventApplicationCourseInfoModel->getByCourseInfoId($course_id, null);

        // 各種情報を切り分ける
        $common_array = reset($histry_list);
        $common_application = $common_array['application'][0];
        $no = '【第' . $common_array['course_info']['no'] . '回】';
        $event_name =  $no . $common_application['event']['name'];
        $price = $common_application['price'] != 0 ? number_format($common_application['price']) . '円' : '無料';
        $pay_method = PAYMENT_SELECT_LIST[$common_application['pay_method']];
        $is_payment = empty($common_application['payment_date']) ? '未決済' : '決済済';
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
