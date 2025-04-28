<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventCustomFieldCategoryModel.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/event/event_edit_controller.php');
class CustomController
{

    private $customFieldModel;
    private $eventController;

    public function __construct()
    {
        $this->customFieldModel = new EventCustomFieldCategoryModel();
        $this->eventController = new EventEditController();
    }

    public function index()
    {
        $event_category_list = $this->customFieldModel->getCustomFieldCategory();

        foreach($event_category_list as &$event_category) {
            $event_category['input_flg'] = false;
            $events = $event_category['event'];
            foreach($events as $event) {
                $tickets = $this->eventController->getTicketCount($event['id']);
                if(count($tickets) > 0) {
                    $event_category['input_flg'] = true;
                    break;
                }
            }
        }
        return $event_category_list;
    }
    public function edit($id = null)
    {
        if(empty($id)) {
            return [];
        } else {
            $customFields = $this->customFieldModel->findCustomFieldCategory($id);
            
            $event_category['input_flg'] = false;
            $events = $customFields['event'];
            foreach($events as $event) {
                $tickets = $this->eventController->getTicketCount($event['id']);
                if(count($tickets) > 0) {
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
