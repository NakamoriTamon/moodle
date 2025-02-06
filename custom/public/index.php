<?php
    require_once '/var/www/html/moodle/custom/app/Controllers/FrontController.php';
    require_once '/var/www/html/moodle/custom/app/Models/BaseModel.php';

    $frontController = new FrontController();
    $lib = $frontController->index();

    $frontController->render('', 'index', ['eventList' => $lib]);
?>