<?php
    require_once '/var/www/html/moodle/custom/app/Controllers/FrontController.php';
    require_once '/var/www/html/moodle/custom/app/Models/BaseModel.php';

    $eventId = $_GET['eventId'];

    $frontController = new FrontController();
    $lib = $frontController->detail($eventId);

    $frontController->render('event', 'detail', [
        'event' => $lib,
    ]);
?>