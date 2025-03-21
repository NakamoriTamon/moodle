<?php
require '/var/www/vendor/autoload.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();

// メール本文テンプレート
$emailTemplates = [
    "request" => "2026年度適塾記念会年会費お支払いのお願い",
    "reminder1" => "【4月14日まで・ご確認ください】2026年度適塾記念会年会費お支払いのお願い",
    "reminder2" => "【4月30日まで・至急ご確認ください】2026年度適塾記念会年会費お支払いのお願い",
    "expulsion" => "2026年度適塾記念会年会費退会のお知らせ"
];
$bodies = [
    'request'   => "<p>適塾記念会会員各位</p><p>いつもお世話になっております。<br>大阪大学適塾記念会事務局です。</p><p>2026年度（2026年4月1日～2027年3月31日）の適塾記念会年会費のお支払いについて、ご案内いたします。</p><p>【金額】<br>・普通会員　一口　2,000円<br>・賛助会員　一口　10,000円</p><p>【お支払期限】<br>2026年3月31日（火）</p><p>【お支払方法】<br>・クレジットカード決済<br>・コンビニ決済<br>・銀行振込（ペイジー）<br>適塾記念会会員専用ウェブサイトからマイページにログインいただき、上記いずれかのお支払方法を選択して期日までにお支払いください。</p><p><a href='https://open-univ.osaka-u.ac.jp/custom/app/Views/login/index.php'>https://open-univ.osaka-u.ac.jp/custom/app/Views/login/index.php</a></p><p>クレジット決済の定期課金プランを選択された方は、上記お支払期限に自動的に決済処理が行われ、各カード会社が定める期日に銀行口座から年会費が引落としされますので、お手続きは不要です。<br>なお、初めてご利用される方は、ユーザー登録が必要です。</p><p>-----<br>大阪大学適塾記念会事務局<br>（大阪大学共創推進部社会連携課総務係）</p>",
    'reminder1' => "<p>適塾記念会会員各位<br>（期限までに年会費をお支払いいただいていない方へ）</p><p>いつもお世話になっております。<br>大阪大学適塾記念会事務局です。</p><p>先日、2026年度（2026年4月1日～2027年3月31日）の適塾記念会年会費のお支払いについて、ご連絡を差し上げました。<br>〇〇〇〇様は、本日現在、次年度の年会をお支払いいただいていないと存じますので、<br>念のためご確認いただきたく、ご連絡さしあげた次第です。<br>【金額】<br>・普通会員　一口　2,000円<br>・賛助会員　一口　10,000円</p><p>【お支払期限】<br>2026年4月15日（水）</p><p>【お支払方法】<br>・クレジットカード決済<br>・コンビニ決済<br>・銀行振込（ペイジー）<br>適塾記念会会員専用ウェブサイトからマイページにログインいただき、上記いずれかのお支払方法を選択して期日までにお支払いください。</p><p><a href='https://open-univ.osaka-u.ac.jp/custom/app/Views/login/index.php'>https://open-univ.osaka-u.ac.jp/custom/app/Views/login/index.php</a></p><p>クレジット決済の場合は、便利な自動引落とし（年1回）もお選びいただけます。大変失礼ながら、すでにお支払いいただいた場合は、本メールは入れ違いですので、ご放念くださいませ。<br>なお、クレジット決済の場合は、毎年の自動引落としもお選びいただけます。<br>どうぞよろしくお願いします。</p><p>-----<br>大阪大学適塾記念会事務局<br>（大阪大学共創推進部社会連携課総務係）</p>",
    'reminder2' => "<p>適塾記念会会員各位<br>（期限までに年会費をお支払いいただいていない方へ）</p><p>いつもお世話になっております。<br>大阪大学適塾記念会事務局です。</p><p>2026年度（2026年4月1日～2027年3月31日）の適塾記念会年会費のお支払いについて、先月以来２度のご案内をいたしました。<br>〇〇〇〇様は、本日現在、次年度の年会をお支払いいただいていないと存じますので、念のためご確認いただきたく、最終のご連絡をさしあげる次第です。</p><p>【金額】<br>・普通会員　一口　2,000円<br>・賛助会員　一口　10,000円</p><p>【お支払期限】<br>2026年4月30日（木）</p><p>【お支払方法】<br>・クレジットカード決済<br>・コンビニ決済<br>・銀行振込（ペイジー）<br>適塾記念会会員専用ウェブサイトからマイページにログインいただき、上記いずれかのお支払方法を選択して期日までにお支払いください。</p><p><a href='https://open-univ.osaka-u.ac.jp/custom/app/Views/login/index.php'>https://open-univ.osaka-u.ac.jp/custom/app/Views/login/index.php</a></p><p>大変失礼ながら、すでにお支払いいただいた場合は、本メールは入れ違いですので、ご放念くださいませ。<br>クレジット決済の場合は、便利な自動引落とし（年1回）もお選びいただけます。</p><p>今回が最終のご案内となりますので、<br>【4月30日までにお支払いがなかった場合、誠に残念ではございますが、〇〇〇〇様の退会の手続きを執らせていただきます。】</p><p>どうぞよろしくお願いします。</p><p>-----<br>大阪大学適塾記念会事務局<br>（大阪大学共創推進部社会連携課総務係）</p>",
    'expulsion' => "<p>適塾記念会会員各位</p><p>いつもお世話になっております。<br>大阪大学適塾記念会事務局です</p><p>これまでお支払いのご案内をさせていただきましたが、4月30日までにご入金の確認が取れなかったため、適塾記念会の登録を解除いたしました。</p><p>これに伴い、今後本会のサービスをご利用いただくことができなくなりますので、ご了承ください。</p><p>もし今回の退会に関してご不明な点や、お支払いについてのご相談がございましたら、お早めに事務局までご連絡ください。</p><p>何卒ご理解のほどよろしくお願い申し上げます。</p><p>-----<br>大阪大学適塾記念会事務局<br>（大阪大学共創推進部社会連携課総務係）</p>"
];

// JSONデータを取得
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$action = $data['action'] ?? '';

// 指定されたアクションが正しいかチェック
if (!isset($emailTemplates[$action])) {
    http_response_code(400);
    echo json_encode(['error' => '無効なアクションです']);
    exit;
}

// メール設定
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = $_ENV['MAIL_HOST'];
$mail->SMTPAuth = true;
$mail->Username = $_ENV['MAIL_USERNAME'];
$mail->Password = $_ENV['MAIL_PASSWORD'];
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->CharSet = PHPMailer::CHARSET_UTF8;
$mail->Port = $_ENV['MAIL_PORT'];

$mail->setFrom($_ENV['MAIL_FROM_ADRESS'], 'Sender Name');
$mail->addAddress('s.kamei@trans-it.net', 'Recipient Name');
// $mail->addAddress('s.kamei@trans-it.net', 'Recipient Name');
// $mail->addAddress('s.kamei@trans-it.net', 'Recipient Name');
$mail->addReplyTo('no-reply@example.com', 'No Reply');
$mail->isHTML(true);

// メール本文
$mail->Subject = $emailTemplates[$action];
$mail->Body = $bodies[$action];

$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);

try {
    $mail->send();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'メール送信に失敗しました: ' . $mail->ErrorInfo]);
    exit;
}

// 成功レスポンスを返す
http_response_code(200);
echo json_encode(['message' => 'Email sent successfully']);
?>
