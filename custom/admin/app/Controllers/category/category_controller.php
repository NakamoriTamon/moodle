<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/CategoryModel.php');
class CategoryController
{

    private $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    public function index()
    {
        $category_list = $this->categoryModel->getCategories();

        return $category_list;
    }

    public function edit($id = null)
    {
        return $id ? $this->categoryModel->find($id) : [];
    }
}
