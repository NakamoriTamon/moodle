<?php
// PHPの実行時間制限を延長
set_time_limit(300); // 5分に設定

require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/local/commonlib/lib.php'); // バリデーション関数用

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
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

        // エラーがあるかどうかチェック
        if ($_SESSION['errors']['mail_title'] || $_SESSION['errors']['mail_body']) {
            throw new Exception('入力内容に問題があります。フォームを確認してください。');
        }

        $dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
        $dotenv->load();

        // 無効なメールアドレスとなる要素を削除
        $email_addresses = array_filter($mail_to_list, function($email) {
            return !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
        });
        
        // 重複を削除
        $email_addresses = array_values(array_unique($email_addresses));
        
        if (empty($email_addresses)) {
            throw new Exception('有効なメールアドレスがありません。');
        }
        
        // デバッグ情報をセッションに保存
        $_SESSION['debug_info'] = '処理するメールアドレス: ' . count($email_addresses) . '件';
        
        // HTMLメール本文の作成
        $htmlBody = "
        <div style=\"text-align: center; font-family: Arial, sans-serif;\">
            <p style=\"text-align: left; font-size: 14px; margin:0; padding:0;\">" . nl2br($mail_body) . "</p>
            <p style=\"font-size: 13px; text-align: left;\">大阪大学 知の広場 ハンダイ市民講座事務局</p>
        </div>
        ";

        // 1回のメールで送信するBCCの最大数
        $batch_size = 20;
        
        // 送信成功メールアドレス数
        $success_count = 0;
        
        // バッチ処理
        $total_emails = count($email_addresses);
        $batches = ceil($total_emails / $batch_size);
        
        for ($batch = 0; $batch < $batches; $batch++) {
            // 新しいPHPMailerインスタンスを作成
            $mail = new PHPMailer(true);
            
            // SMTP設定
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Port = $_ENV['MAIL_PORT'];
            
            // SSL設定
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // 送信元の設定
            $mail->setFrom($_ENV['MAIL_FROM_ADRESS'], '大阪大学 知の広場 ハンダイ市民講座事務局');
            
            // メール設定
            $mail->isHTML(true);
            $mail->Subject = $mail_title;
            $mail->Body = $htmlBody;
            
            // BCC以外の送信先が必要なので、送信元をToアドレスとして仮設定
            $mail->addAddress($_ENV['MAIL_FROM_ADRESS'], '大阪大学 知の広場 ハンダイ市民講座事務局');
            
            // このバッチで送信するメールアドレスの範囲を計算
            $start_index = $batch * $batch_size;
            $end_index = min($start_index + $batch_size, $total_emails);
            
            // このバッチのすべてのアドレスをBCCとして追加
            for ($i = $start_index; $i < $end_index; $i++) {
                $mail->addBCC($email_addresses[$i]);
            }
            
            // メール送信
            if ($mail->send()) {
                $success_count += ($end_index - $start_index);
            }
            
            // バッチ間に少し待機（サーバー負荷を軽減）
            if ($batch < $batches - 1) {
                usleep(500000); // 0.5秒待機
            }
        }

        $_SESSION['message_success'] = $success_count . '件のメールを送信しました。';
        header('Location: /custom/admin/app/Views/message/index.php');
        exit;
    } catch (Exception $e) {
        // 入力内容を保持
        $_SESSION['old_input'] = $_POST;
        
        // エラーメッセージを設定
        $_SESSION['message_error'] = 'メール送信に失敗しました: ' . $e->getMessage();
        
        // デバッグ情報があれば追加
        if (isset($_SESSION['debug_info'])) {
            $_SESSION['message_error'] .= ' [' . $_SESSION['debug_info'] . ']';
            unset($_SESSION['debug_info']);
        }
        
        header('Location: /custom/admin/app/Views/message/index.php');
        exit;
    }
}
