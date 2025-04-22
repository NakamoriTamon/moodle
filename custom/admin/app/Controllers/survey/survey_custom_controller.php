<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventSurveyCustomFieldCategoryModel.php');
require_once($CFG->dirroot . '/custom/app/Models/SurveyApplicationModel.php');
require_once($CFG->dirroot . '/custom/app/Models/SurveyApplicationCustomfieldModel.php');

class SurveyCustomController
{

    private $surveyCustomFieldCategoryModel;
    private $surveyApplicationModel;
    private $surveyApplicationCustomfieldModel;

    public function __construct()
    {
        $this->surveyCustomFieldCategoryModel = new EventSurveyCustomFieldCategoryModel();
        $this->surveyApplicationModel = new SurveyApplicationModel();
        $this->surveyApplicationCustomfieldModel = new SurveyApplicationCustomfieldModel();
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
        $events = $surveyCustomFields['event'];
        
        // アンケートがあるか確認
        $surveyCustomFields['answer'] = false;
        foreach ($events as $event) {
            $event_id = $event['id'];
            $surveyApplication = $this->surveyApplicationModel->getSurveyApplications(null, $event_id, 1 ,1);

            if(!empty($surveyApplication)) {
                $answer = $this->surveyApplicationCustomfieldModel->getESurveyApplicationCustomfieldBySurveyApplicationId($surveyApplication[0]['id']);
                if(!empty($answer)) {
                    $surveyCustomFields['answer'] = true;
                    break;
                }
            }
        }
        return $surveyCustomFields;
    }
}
