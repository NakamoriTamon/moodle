<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');

class CategoryController
{

    private $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    public function getCategoryDetails($categoryId)
    {
        $category = $this->categoryModel->getCategoryDetails($categoryId);

        return $category;
    }

    public function getCategories()
    {
        $category = $this->categoryModel->getCategories();

        return $category;
    }
}
