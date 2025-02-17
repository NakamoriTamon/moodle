<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/TargetModel.php');

class TargetController
{

    private $targetModel;

    public function __construct()
    {
        $this->targetModel = new TargetModel();
    }

    public function index()
    {
        $target = $this->targetModel->getTargets();

        return $target;
    }


    public function edit($id = null)
    {
        $target = $this->targetModel->getTargetById($id);

        return $target;
    }
}
