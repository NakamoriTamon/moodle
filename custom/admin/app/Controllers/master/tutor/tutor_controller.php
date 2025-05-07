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

    public function index()
    {

        $result = $this->tutorModel->getTutors();

        return $result;
    }
}
