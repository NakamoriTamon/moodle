<?php
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/config.php');

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Content-Type設定
header('Content-Type: image/png');

// 暗号化されたIDを取得
$encrypted_id = $_GET['eaci_id'] ?? '';

$qr_code = new QrCode($encrypted_id);
$writer = new PngWriter();
$qr_code_image = $writer->write($qr_code)->getString();
// 画像として出力
echo $qr_code_image;
