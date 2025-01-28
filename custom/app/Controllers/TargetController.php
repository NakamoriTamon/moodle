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

    public function getTargetDetails($targetId)
    {
        $target = $this->targetModel->getTargetById($targetId);

        return $target;
    }

    public function getTargets()
    {
        $target = $this->targetModel->getTargets();

        return $target;
    }
}
