<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Controllers/event/event_application_register_controller.php');
require_once($CFG->dirroot . '/custom/app/Controllers/event/event_movie_controller.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
include($CFG->dirroot . '/custom/app/Views/common/header.php');

$course_info_id = isset($_POST['course_info_id']) ? $_POST['course_info_id'] : null;
if (empty($course_info_id)) {
}
$event_movie_controller = new EventMovieController();
$result_list = $event_movie_controller->index($course_info_id);

// バリデーションエラー
$errors   = $_SESSION['errors']   ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

// 動画を見たら、オンデマンド配信イベントは参加済みにする
$user_id = $_SESSION['user_id'] ?? null;
if ($user_id) {
    $event_application_register_controller = new EventRegisterController();
    $res = $event_application_register_controller->updateParticipation($user_id, $course_info_id);
}
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/event.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="EVENT MOVIE">イベント動画</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="movie">
            <div class="movie_wrap w-100" data-is-double-speed="<?= $result_list['is_double_speed']; ?>">
                <video id="movie_video" controls oncontextmenu="return false;" disablePictureInPicture
                    <?= $result_list["is_double_speed"] != 1 ? 'controlsList="nodownload noplaybackrate"' : 'controlsList="nodownload"'; ?>>
                    <source id="movie_video_source" src="<?= htmlspecialchars($result_list['path'], ENT_QUOTES, 'UTF-8') ?>" type="video/mp4">
                    <p>動画再生をサポートしていないブラウザです。</p>
                </video>
            </div>
            <a href="register.php" class="btn btn_blue arrow box_bottom_btn">前へ戻る</a>
        </section>
        <!-- result -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li><a href="register.php">申し込みイベント</a></li>
    <li>イベント動画</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>

<script>
    $(document).ready(function() {
        // PHPから取得した $course_info_id
        let course_info_id = "<?= $course_info_id ?>";

        // 1. もし POST でデータを受け取ったら sessionStorage に保存
        if (course_info_id) {
            sessionStorage.setItem('course_info_id', course_info_id);
        }

        // 2. リロード時に sessionStorage から取得し、再送信
        if (!course_info_id && sessionStorage.getItem('course_info_id')) {
            const course_info_id = sessionStorage.getItem('course_info_id');
            sessionStorage.removeItem('course_info_id');
            let form = $('<form>', {
                action: '/custom/app/Views/event/movie.php',
                method: 'POST',
                style: 'display: none;'
            });

            $('<input>').attr({
                type: 'hidden',
                name: 'course_info_id',
                value: course_info_id
            }).appendTo(form);

            $('body').append(form);
            form.submit();
        }

        const is_double_speed = "<?= $result_list['is_double_speed'] ?>";
        $('#movie_video').on('mouseenter', function() {
            $(this).prop('controlsList', 'nodownload');
            if (is_double_speed != 1) {
                $(this).prop('controlsList', 'nodownload noplaybackrate');
            } else {
                $(this).prop('controlsList', 'nodownload');
            }
        });

        // 再生時の設定
        $('#movie_video').on('play', function() {
            // 再生時に倍速の設定を変更
            if (is_double_speed != 1) {
                $(this).prop('controlsList', 'nodownload noplaybackrate'); // 倍速無効化
            } else {
                $(this).prop('controlsList', 'nodownload'); // ダウンロードだけ無効化
            }
        });
    });
</script>