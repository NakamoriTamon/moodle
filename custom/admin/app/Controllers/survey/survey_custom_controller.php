<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventSurveyCustomFieldCategoryModel.php');

class SurveyCustomController
{

    private $surveyCustomFieldCategoryModel;

    public function __construct()
    {
        $this->surveyCustomFieldCategoryModel = new EventSurveyCustomFieldCategoryModel();
    }

    public function index()
    {
        $survey_application_list = $this->surveyCustomFieldCategoryModel->getSurveyCustomFieldCategory();

        return $survey_application_list;
    }

    public function edit($id = null)
    {
        if(empty($id)) {
            return [];
        } else {
            $surveyCustomFields = $this->surveyCustomFieldCategoryModel->findSurveyCustomFieldCategory($id);
        }
        
        if (empty($surveyCustomFields)) {
            $_SESSION['message_error'] = '選択したカスタムフィールドは存在しません ';
            redirect(new moodle_url('/custom/admin/app/Views/survey/custom_index.php'));
            exit;
        }
        return $surveyCustomFields;
    }
}
