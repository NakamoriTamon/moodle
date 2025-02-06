<?php
    require_once '/var/www/html/moodle/custom/app/Controllers/FrontController.php';
    require_once '/var/www/html/moodle/custom/app/Models/BaseModel.php';

    $frontController = new FrontController();
    $lib = $frontController->eventTop();

    $frontController->render('event', 'index', [
        'eventList' => $lib['eventList'],
        'currentPage' => $lib['pagination']['currentPage'],
        'totalPages' => $lib['pagination']['totalPages'],
    ]);
?>