<?php
ob_start();
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once('/var/www/html/moodle/custom/app/Controllers/tekijuku/tekijuku_index_controller.php');

// CSRF動的トークン生成
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// ログインユーザーID(会員番号)
$login_id = $_SESSION['user_id'] ?? null;
// ログイン済みチェックフラグ
$login_check_flg = $_SESSION['user_id'] ? 1: 0;

$tekijuku_index_controller = new TekijukuIndexController;
$footre_tekijuku_commemoration = $tekijuku_index_controller->getTekijukuCommemoration();
$basic_error = $_SESSION['message_error'] ?? null;
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['message_error']);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="keywords" content="" />
    <meta name="description" content="<?php
                                        $path = $_SERVER['REQUEST_URI'];
                                        if (strpos($path, 'event/register')) {
                                            echo '申し込みイベントを確認できます。';
                                        } elseif (strpos($path, 'user/pass')) {
                                            echo 'パスワードの再設定を行います。';
                                        } elseif (strpos($path, 'contact')) {
                                            echo 'お問い合わせはこちらから。';
                                        } elseif (strpos($path, 'event')) {
                                            echo '開催イベントを閲覧できます。';
                                        } elseif (strpos($path, 'faq')) {
                                            echo 'よくあるご質問をまとめました。';
                                        } elseif (strpos($path, 'front')) {
                                            echo '開催イベントを閲覧できます。';
                                        } elseif (strpos($path, 'guide')) {
                                            echo '受講について詳しく説明しています。';
                                        } elseif (strpos($path, 'login')) {
                                            echo 'ユーザー様のログインページです。';
                                        } elseif (strpos($path, 'logout')) {
                                            echo 'ログアウトしました。';
                                        } elseif (strpos($path, 'mypage')) {
                                            echo 'ユーザー様のマイページです。予約情報もこちらからご確認いただけます。';
                                        } elseif (strpos($path, 'regulate')) {
                                            echo '特定商取引法に基づく表記';
                                        } elseif (strpos($path, 'signup')) {
                                            echo '本登録が完了しました';
                                        } elseif (strpos($path, 'survey')) {
                                            echo 'アンケートのご協力ありがとうございます。';
                                        } elseif (strpos($path, 'tekijuku')) {
                                            echo '適塾記念会のページです。';
                                        } elseif (strpos($path, 'user')) {
                                            echo '新規ユーザー登録をしていただけます。';
                                        } else {
                                            echo '大阪大学【知の広場】ハンダイ市民講座は大阪大学が主催する市民向け講座や子ども向けイベントなど、多様な学びに触れることのできる開かれた広場です。地域・社会と大学、そして研究者と市民をつなぐことで、社会との共創を目指します。';
                                        }
                                        ?>" />
    <title>
        <?php
        $path = $_SERVER['REQUEST_URI'];
        $baseTitle = '大阪大学【知の広場】ハンダイ市民講座｜社会と未来、学びをつなぐ・・';
        $pageTitle = '';

        if (strpos($path, 'contact')) {
            $pageTitle = 'お問い合わせ';
        } elseif (strpos($path, 'event')) {
            $pageTitle = 'イベント';
        } elseif (strpos($path, 'guide')) {
            $pageTitle = '受講ガイド';
        } elseif (strpos($path, 'faq')) {
            $pageTitle = 'よくある質問';
        } elseif (strpos($path, 'user')) {
            $pageTitle = 'ユーザー登録';
        } elseif (strpos($path, 'login')) {
            $pageTitle = 'ログイン';
        } elseif (strpos($path, 'mypage')) {
            $pageTitle = 'マイページ';
        } elseif (strpos($path, 'tekijuku')) {
            $pageTitle = '適塾記念会';
        } elseif (strpos($path, 'user/pass')) {
            $pageTitle = 'パスワード再設定';
        } elseif (strpos($path, 'contact')) {
            $pageTitle = 'お問い合わせ';
        } elseif (strpos($path, 'event')) {
            $pageTitle = 'イベント';
        } elseif (strpos($path, 'faq')) {
            $pageTitle = 'よくある質問';
        } elseif (strpos($path, 'front')) {
            $pageTitle = 'イベント';
        } elseif (strpos($path, 'guide')) {
            $pageTitle = '受講ガイド';
        } elseif (strpos($path, 'login')) {
            $pageTitle = 'ログイン';
        } elseif (strpos($path, 'logout')) {
            $pageTitle = 'ログアウト';
        } elseif (strpos($path, 'mypage')) {
            $pageTitle = 'マイページ';
        } elseif (strpos($path, 'regulate')) {
            $pageTitle = '特定商取引法に基づく表記';
        } elseif (strpos($path, 'signup')) {
            $pageTitle = '本登録';
        } elseif (strpos($path, 'survey')) {
            $pageTitle = 'アンケート';
        } elseif (strpos($path, 'tekijuku')) {
            $pageTitle = '適塾記念会';
        } elseif (strpos($path, 'user')) {
            $pageTitle = 'ユーザー登録';
        } else {
            $pageTitle = '';
        }

        echo $pageTitle ? "$pageTitle - $baseTitle" : $baseTitle;

        ?>
    </title>

    <meta property="og:title" content="大阪大学【知の広場】ハンダイ市民講座｜社会と未来、学びをつなぐ・・">
    <meta property="og:description" content="大阪大学【知の広場】ハンダイ市民講座は大阪大学が主催する市民向け講座や子ども向けイベントなど、
    多様な学びに触れることのできる開かれた広場です。地域・社会と大学、そして研究者と市民をつなぐことで、社会との共創を目指します。">
    <meta property="og:image" content="https://open-univ.osaka-u.ac.jp/custom/public/assets/img/home/ogp.jpg">
    <meta property="og:url" content="https://open-univ.osaka-u.ac.jp/">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="大阪大学【知の広場】ハンダイ市民講座｜社会と未来、学びをつなぐ・・">
    <meta name="twitter:description" content="大阪大学【知の広場】ハンダイ市民講座は大阪大学が主催する市民向け講座や子ども向けイベントなど、
    多様な学びに触れることのできる開かれた広場です。地域・社会と大学、そして研究者と市民をつなぐことで、社会との共創を目指します。">
    <meta name="twitter:image" content="https://open-univ.osaka-u.ac.jp/custom/public/assets/img/home/ogp.jpg">


    <link rel="icon" href="/custom/public/assets/img/home/favicon.svg" type="image/svg+xml">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0YF1PN4FKM"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-0YF1PN4FKM');
    </script>

    <!-- ogp -->
    <meta property="og:title" content="" />
    <meta property="og:description" content="" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="" />
    <meta property="og:image" content="" />
    <meta property="og:site_name" content="" />
    <meta property="og:locale" content="ja_JP" />
    <!-- stylesheet -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="/custom/public/assets/common/css/import.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
    <!-- fontawesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.0/css/all.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&display=swap"
        rel="stylesheet" />
</head>

<body id="home">
    <header id="header">
        <h1 class="header_logo">
            <a href="/custom/app/Views/index.php"><img src="/custom/public/assets/common/img/logo_header.svg" alt="知の広場ハンダイ市民講座" />
            </a>
        </h1>
        <nav>
            <ul class="header_menu">
                <li>
                    <a href="/custom/app/Views/event/index.php">
                        <img src="/custom/public/assets/common/img/icon_menu01.svg" alt="講座一覧" />
                        <p class="txt">イベント一覧</p>
                    </a>
                </li>
                <li>
                    <a href="/custom/app/Views/guide/index.php">
                        <img src="/custom/public/assets/common/img/icon_menu02.svg" alt="受講ガイド" />
                        <p class="txt">受講ガイド</p>
                    </a>
                </li>
                <li>
                    <a href="/custom/app/Views/faq/index.php">
                        <img src="/custom/public/assets/common/img/icon_menu03.svg" alt="よくある質問" />
                        <p class="txt">よくある質問</p>
                    </a>
                </li>
                <li>
                    <a href="/custom/app/Views/contact/index.php">
                        <img src="/custom/public/assets/common/img/icon_menu04.svg" alt="お問い合わせ" />
                        <p class="txt">お問い合わせ</p>
                    </a>
                </li>
            </ul>
            <div class="header_tool">
                <?php if (empty($login_id)): ?>
                    <a href="/custom/app/Views/user/index.php" class="btn_h btn_user">
                        <p>ユーザー登録</p>
                    </a>
                    <a href="/custom/app/Views/login/index.php" class="btn_h btn_login">
                        <p>ログイン</p>
                    </a>
                <?php else: ?>
                    <a href="/custom/app/Views/mypage/index.php" class="btn_full btn_login">
                        <p>マイページ</p>
                    </a>
                <?php endif; ?>
                <form method="" action="/custom/app/Controllers/event/event_controller.php" class="search">
                    <input type="hidden" name="action" value="index">
                    <input type="hidden" id="login_check_flg" name="login_check_flg" value="<?= htmlspecialchars($login_check_flg) ?>">
                    <button type="submit" aria-label="検索"></button>
                    <label>
                        <input type="text" name="keyword" placeholder="イベントを検索する" />
                    </label>
                </form>
            </div>
        </nav>
        <div class="header_hbg nopc">
            <ul class="hbg">
                <li></li>
                <li></li>
                <li></li>
            </ul>
            <span class="txt">MENU</span>
        </div>
        <div class="header_bottom nopc">
            <?php if (empty($login_id)): ?>
                <a href="/custom/app/Views/user/index.php" class="btn_h btn_user">
                    <p>ユーザー登録</p>
                </a>
                <a href="/custom/app/Views/login/index.php" class="btn_h btn_login">
                    <p>ログイン</p>
                </a>
            <?php else: ?>
                <a href="/custom/app/Views/mypage/index.php" class="btn_full btn_login">
                    <p>マイページ</p>
                </a>
            <?php endif; ?>
        </div>
    </header>
    <!-- header -->