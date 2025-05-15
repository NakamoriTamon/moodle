<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventCustomFieldCategoryModel.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/event/event_edit_controller.php');
require_once($CFG->dirroot . '/custom/app/Models/RoleAssignmentsModel.php');
class CustomController
{

    private $customFieldModel;
    private $eventController;
    private $roleAssignmentsModel;

    public function __construct()
    {
        $this->customFieldModel = new EventCustomFieldCategoryModel();
        $this->eventController = new EventEditController();
        $this->roleAssignmentsModel = new RoleAssignmentsModel();
    }

    public function index()
    {
        global $USER;

        $event_category_list = $this->customFieldModel->getCustomFieldCategory();

        $role = $this->roleAssignmentsModel->getShortname($USER->id);
        $shortname = $role['shortname'];

        // システム管理者以外は自身のイベントのみ表示する
        if ($shortname !== ROLE_ADMIN) {
            foreach ($event_category_list as $index => &$event_category) {
                if ($event_category['fk_user_id'] != $USER->id) {
                    unset($event_category_list[$index]);
                    continue;
                }
                $event_category['event'] = array_filter(
                    $event_category['event'],
                    fn($event) => $event['userid'] == $USER->id
                );
            }
            unset($survey_application); // 参照解除
        }


        foreach ($event_category_list as &$event_category) {
            $event_category['input_flg'] = false;
            $events = $event_category['event'];
            foreach ($events as $event) {
                $tickets = $this->eventController->getTicketCount($event['id']);
                if (count($tickets) > 0) {
                    $event_category['input_flg'] = true;
                    break;
                }
            }
        }
        return $event_category_list;
    }
    public function edit($id = null)
    {
        global $USER;

        if (empty($id)) {
            return [];
        } else {
            $customFields = $this->customFieldModel->findCustomFieldCategory($id);


            $role = $this->roleAssignmentsModel->getShortname($USER->id);
            $shortname = $role['shortname'];

            // 部門管理者かつ自身の作成したフィールドでなければ一覧画面に戻す
            if ($shortname !== ROLE_ADMIN) {
                if ($customFields['fk_user_id'] != $USER->id) {
                    redirect(new moodle_url('/custom/admin/app/Views/event/custom_index.php'));
                    exit;
                }
            }

            $event_category['input_flg'] = false;
            $events = $customFields['event'];
            foreach ($events as $event) {
                $tickets = $this->eventController->getTicketCount($event['id']);
                if (count($tickets) > 0) {
                    $customFields['input_flg'] = true;
                    break;
                }
            }
        }

        if (empty($customFields)) {
            $_SESSION['message_error'] = '選択したカスタムフィールドは存在しません ';
            redirect(new moodle_url('/custom/admin/app/Views/event/custom_index.php'));
            exit;
        }

        return $customFields;
    }
}
