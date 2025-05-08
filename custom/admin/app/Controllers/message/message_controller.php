<?php
// PHPの実行時間制限を延長
set_time_limit(300); // 5分に設定

require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php'); // バリデーション関数用

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;
use Dotenv\Dotenv;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mail_title = $_POST['mail_title'] ?? '';
    $mail_body = $_POST['mail_body'] ?? '';
    $mail_to_list = $_POST['mail_to_list'] ?? []; // 配列として受け取る

    // エラー配列を初期化
    $_SESSION['errors'] = [];

    try {
        // バリデーションチェック
        $_SESSION['errors']['mail_title'] = validate_text($mail_title, 'メールタイトル', 100, true);
        $_SESSION['errors']['mail_body'] = validate_textarea($mail_body, 'メール本文', true, 2000);

        if ($_SESSION['errors']['mail_title'] || $_SESSION['errors']['mail_body']) {
            throw new Exception('入力内容に問題があります。フォームを確認してください。');
        }

        $dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
        $dotenv->load();

        // 無効なメールアドレスを除外
        $email_addresses = array_filter($mail_to_list, function ($email) {
            // ドメイン名が有効かDNSチェック（MXレコード確認）
            $domain = substr(strrchr($email, "@"), 1);
            // filter_var() 形式チェック
            // checkdnsrr() は MX レコードを確認する関数 メールを受信可能なドメインかどうかチェック
            return !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && $domain && checkdnsrr($domain, "MX");
        });
        $email_addresses = array_values(array_unique($email_addresses));

        if (empty($email_addresses)) {
            throw new Exception('有効なメールアドレスがありません。');
        }

        $_SESSION['debug_info'] = '処理するメールアドレス: ' . count($email_addresses) . '件';

        // HTMLメール本文
        $htmlBody = "
        <div style=\"text-align: center; font-family: Arial, sans-serif;\">
            <p style=\"text-align: left; font-size: 14px; margin:0; padding:0;\">" . nl2br($mail_body) . "</p>
            <p style=\"margin-top: 3.5rem; font-size: 13px; text-align: left;\">※ このメールは送信専用アドレスから送信しています。返信はしていただけませんので、ご質問のある方は、「知の広場」のお問い合わせフォームから問い合わせてください。</p>
            <p style=\"font-size: 13px; text-align: left;\">大阪大学 知の広場 ハンダイ市民講座事務局</p>
        </div>
        ";

        // SESクライアント設定
        $SesClient = new SesClient([
            'version' => 'latest',
            'region'  => 'ap-northeast-1',
            'credentials' => [
                'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY_ID'],
            ]
        ]);

        // 1回の送信でのBCC最大数
        $batch_size = 20;
        $success_count = 0;
        $failed_batches = [];
        $total_emails = count($email_addresses);
        $batches = ceil($total_emails / $batch_size);

        for ($batch = 0; $batch < $batches; $batch++) {
            $start_index = $batch * $batch_size;
            $bcc_list = array_slice($email_addresses, $start_index, $batch_size);

            $boundary = md5(time());

            $rawMessage = "From: 知の広場 <{$_ENV['MAIL_FROM_ADDRESS']}>\r\n";
            $rawMessage .= "To: {$_ENV['MAIL_FROM_ADDRESS']}\r\n";
            $rawMessage .= "Subject: =?UTF-8?B?" . base64_encode($mail_title) . "?=\r\n";
            $rawMessage .= "MIME-Version: 1.0\r\n";
            $rawMessage .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n";

            $textBody = strip_tags(str_replace(["<br>", "<br/>", "<br />"], "\n", $mail_body));

            $rawMessage .= "--{$boundary}\r\n";
            $rawMessage .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $rawMessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $rawMessage .= $textBody . "\r\n\r\n";

            $rawMessage .= "--{$boundary}\r\n";
            $rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n";
            $rawMessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $rawMessage .= $htmlBody . "\r\n\r\n";
            $rawMessage .= "--{$boundary}--";

            try {
                $result = $SesClient->sendRawEmail([
                    'RawMessage' => [
                        'Data' => $rawMessage
                    ],
                    'Source' => $_ENV['MAIL_FROM_ADDRESS'],
                    'Destinations' => $bcc_list
                ]);
                $success_count += count($bcc_list);
            } catch (AwsException $e) {
                $failed_batches[] = [
                    'emails' => $bcc_list,
                    'error' => $e->getAwsErrorMessage()
                ];
                // 次のループへ continue
                continue;
            }

            usleep(500000); // 0.5秒待機
        }

        if (!empty($failed_batches)) {
            $error_summary = count($failed_batches) . '件のバッチで送信エラーが発生しました。';
            $_SESSION['message_error'] = $error_summary;
            // 詳細ログを保存しておきたければここでファイル出力も可能
        } else {
            $_SESSION['message_error'] = null; // クリア
        }

        $_SESSION['message_success'] = $success_count . '件のメールを送信しました。';
        header('Location: /custom/admin/app/Views/message/index.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['old_input'] = $_POST;
        $_SESSION['message_error'] = 'メール送信に失敗しました: ' . $e->getMessage();

        if (isset($_SESSION['debug_info'])) {
            $_SESSION['message_error'] .= ' [' . $_SESSION['debug_info'] . ']';
            unset($_SESSION['debug_info']);
        }

        header('Location: /custom/admin/app/Views/message/index.php');
        exit;
    }
}
