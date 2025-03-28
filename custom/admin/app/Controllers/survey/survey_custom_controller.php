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
        return $id ? $this->surveyCustomFieldCategoryModel->findSurveyCustomFieldCategory($id) : [];
    }
}
