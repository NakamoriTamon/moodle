<?php
define('CLI_SCRIPT', true);
require '/var/www/vendor/autoload.php';
require(__DIR__ . '/../../config.php');

use Dotenv\Dotenv;
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();

// Bashから件名を引数で受け取る
$subject = $argv[1] ?? "【CAUTION】「知の広場」Nginxセキュリティレポート";
$htmlBody = file_get_contents('/tmp/security_report.html');
$mail_to_list = ['cyujo.nakamori@gmail.com'];

$SesClient = new SesClient([
    'version' => 'latest',
    'region'  => 'ap-northeast-1',
    'credentials' => [
        'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
        'secret' => $_ENV['AWS_SECRET_ACCESS_KEY_ID'],
    ]
]);

foreach ($mail_to_list as $to) {
    try {
        $SesClient->sendEmail([
            'Destination' => ['ToAddresses' => [$to]],
            'ReplyToAddresses' => ['no-reply@example.com'],
            'Source' => '管理者サポート <' . $_ENV['MAIL_FROM_ADDRESS'] . '>',
            'Message' => [
                'Subject' => ['Data' => $subject, 'Charset' => 'UTF-8'],
                'Body' => [
                    'Html' => ['Data' => $htmlBody, 'Charset' => 'UTF-8']
                ]
            ]
        ]);
    } catch (AwsException $e) {
        error_log("Send failed: " . $e->getMessage());
    }
}
