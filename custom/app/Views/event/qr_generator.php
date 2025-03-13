<?php
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/config.php');

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

header('Content-Type: image/png');

$base_url = $CFG->wwwroot;
$event_application_id = $_GET['event_application_id'] ?? null;
$course_id = $_GET['event_application_course_info'] ?? null;

$url = $base_url . "/custom/app/Controllers/event/event_proof_controller.php?event_application_id=$event_application_id&event_application_course_info=$course_id";

$qrCode = new QrCode($url);
$writer = new PngWriter();
$qr_code_image = $writer->write($qrCode)->getString();

echo $qr_code_image;
