<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventSurveyCustomFieldCategoryModel.php');
require_once($CFG->dirroot . '/custom/app/Models/SurveyApplicationModel.php');
require_once($CFG->dirroot . '/custom/app/Models/RoleAssignmentsModel.php');

class SurveyCustomController
{

    private $surveyCustomFieldCategoryModel;
    private $surveyApplicationModel;
    private $roleAssignmentsModel;

    public function __construct()
    {
        $this->surveyCustomFieldCategoryModel = new EventSurveyCustomFieldCategoryModel();
        $this->surveyApplicationModel = new SurveyApplicationModel();
        $this->roleAssignmentsModel = new RoleAssignmentsModel();
    }

    public function index()
    {
        global $USER;

        $survey_application_list = $this->surveyCustomFieldCategoryModel->getSurveyCustomFieldCategory();

        $role = $this->roleAssignmentsModel->getShortname($USER->id);
        $shortname = $role['shortname'];

        // システム管理者以外は自身のイベントのみ表示する
        if ($shortname !== ROLE_ADMIN) {
            foreach ($survey_application_list as $index => &$survey_application) {
                if ($survey_application['fk_user_id'] != $USER->id) {
                    unset($survey_application_list[$index]);
                    continue;
                }
                $survey_application['event'] = array_filter(
                    $survey_application['event'],
                    fn($event) => $event['userid'] == $USER->id
                );
            }
            unset($survey_application); // 参照解除
        }

        foreach ($survey_application_list as &$surveyCustomFields) {
            // アンケートがあるか確認
            $surveyCustomFields['answer'] = false;
            $events = $surveyCustomFields['event'];
            foreach ($events as $event) {
                $event_id = $event['id'];
                $surveyApplication = $this->surveyApplicationModel->getSurveyApplications(null, $event_id, 1, 1);

                if (!empty($surveyApplication)) {
                    $surveyCustomFields['answer'] = true;
                }
            }
        }
        return $survey_application_list;
    }

    public function edit($id = null)
    {
        global $USER;

        if (empty($id)) {
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

        $role = $this->roleAssignmentsModel->getShortname($USER->id);
        $shortname = $role['shortname'];

        // 部門管理者かつ自身の作成したフィールドでなければ一覧画面に戻す
        if ($shortname !== ROLE_ADMIN) {
            if ($surveyCustomFields['fk_user_id'] != $USER->id) {
                redirect(new moodle_url('/custom/admin/app/Views/survey/custom_index.php'));
                exit;
            }
        }

        // アンケートがあるか確認
        $surveyCustomFields['answer'] = false;
        foreach ($events as $event) {
            $event_id = $event['id'];
            $surveyApplication = $this->surveyApplicationModel->getSurveyApplications(null, $event_id, 1, 1);

            if (!empty($surveyApplication)) {
                $surveyCustomFields['answer'] = true;
                break;
            }
        }
        return $surveyCustomFields;
    }
}
