<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once('/var/www/html/moodle/custom/app/Controllers/tekijuku/tekijuku_index_controller.php');

// CSRF動的トークン生成
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// ログインユーザーID
$login_id = $_SESSION['user_id'] ?? null;

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
    <meta name="robots" content="noindex" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <title></title>
    <link rel="shortcut icon" href="/common/img/favicon.ico" />
    <!-- ogp -->
    <meta property="og:title" content="" />
    <meta property="og:description" content="" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="" />
    <meta property="og:image" content="" />
    <meta property="og:site_name" content="" />
    <meta property="og:locale" content="ja_JP" />
    <!-- stylesheet -->
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
                    <a href="/custom/app/Views/mypage/index.php" class="btn_h btn_login">
                        <p>マイページ</p>
                    </a>
                <?php endif; ?>
                <form method="" action="/custom/app/Controllers/event/event_controller.php" class="search">
                    <input type="hidden" name="action" value="index">
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
            <a href="/custom/app/Views/user/index.php" class="btn_h btn_user">
                <p>ユーザー登録</p>
            </a>
            <a href=<?= empty($login_id) ? "/custom/app/Views/login/index.php" : "/custom/app/Views/mypage/index.php" ?> class="btn_h btn_login">
                <p>ログイン<span>（マイページ）</span></p>
            </a>
        </div>
    </header>
    <!-- header -->