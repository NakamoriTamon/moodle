<?php
ob_start();
require_once('/var/www/html/moodle/custom/admin/app/Controllers/login/roleController.php');

// CSRF動的トークン生成
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function is_mobile_device()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    return preg_match('/(iPhone|iPod|Android|BlackBerry|Opera Mini|Windows Phone|webOS)/i', $user_agent);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="AdminKit">
    <meta name="robots" content="noindex, nofollow">
    <title>知の広場 | 管理画面</title>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/l10n/ja.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="shortcut icon" href="/custom/admin/public/img/icons/icon-48x48.png" />
    <link rel="canonical" href="https://demo.adminkit.io/" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">

    <!-- Remove this after purchasing -->
    <link class="js-stylesheet" href="/custom/admin/public/css/light.css" rel="stylesheet">
    <link class="js-stylesheet" href="/custom/admin/public/css/style.css" rel="stylesheet">
    <link class="js-stylesheet" href="/custom/admin/public/css/custom_light.css" rel="stylesheet">
</head>

<?php
if (isset($_SESSION['message_success'])) {
    echo '<div class="alert alert-success max-650 fs-5 alert-dismissible position-fixed" role="alert" id="success-alert">
                <div class="alert-message fs-5 text-center">' . $_SESSION['message_success'] . '</div>
            </div>';
    unset($_SESSION['message_success']);
}
if (isset($_SESSION['message_error'])) {
    echo '<div class="alert alert-danger max-650 alert-dismissible position-fixed" role="alert" id="error-alert">
                <div class="alert-message text-center text-danger">' . $_SESSION['message_error'] . '</div>
            </div>';
    unset($_SESSION['message_error']);
}
?>

<script>
    $(document).ready(function() {
        if ($('#success-alert').length > 0) {
            setTimeout(function() {
                $('#success-alert').fadeOut('slow');
            }, 2000);
        }
        if ($('#error-alert').length > 0) {
            setTimeout(function() {
                $('#error-alert').fadeOut('slow');
            }, 2000);
        }
        flatpickr('.timepicker', {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            minuteIncrement: 5,
            defaultHour: 0,
            defaultMinute: 0,
        });
    });
</script>