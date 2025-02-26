<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/MaterialModel.php');

class MaterialController
{

    private $materialModel;

    public function __construct()
    {
        $this->materialModel = new MaterialModel();
    }

    public function index()
    {
        $material = $this->materialModel->getMaterials();

        return $material;
    }
}
