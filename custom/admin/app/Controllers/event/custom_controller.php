<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventCustomFieldCategoryModel.php');
class CustomController
{

    private $customFieldModel;

    public function __construct()
    {
        $this->customFieldModel = new EventCustomFieldCategoryModel();
    }

    public function index()
    {
        $event_category_list = $this->customFieldModel->getCustomFieldCategory();

        return $event_category_list;
    }
    public function edit($id = null)
    {
        if(empty($id)) {
            return [];
        } else {
            $customFields = $this->customFieldModel->findCustomFieldCategory($id);
        }
        
        if (empty($customFields)) {
            $_SESSION['message_error'] = '選択したカスタムフィールドは存在しません ';
            redirect(new moodle_url('/custom/admin/app/Views/event/custom_index.php'));
            exit;
        }

        return $customFields;
    }
}
