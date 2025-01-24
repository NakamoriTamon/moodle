<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/TutorModel.php');

class TutorController
{

    private $tutorModel;

    public function __construct()
    {
        $this->tutorModel = new TutorModel();
    }

    public function getEventDetails($tutorId)
    {
        $tutor = $this->tutorModel->getEventById($tutorId);

        return $tutor;
    }

    public function getToturs()
    {
        $tutors = $this->tutorModel->getToturs();

        return $tutors;
    }
}
