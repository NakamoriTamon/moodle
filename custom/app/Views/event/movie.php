<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Controllers/event/event_application_register_controller.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/movie/movie_controller.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
include($CFG->dirroot . '/custom/app/Views/common/header.php');

if (isset($old_input['event_id'])) {
    $event_id = $old_input['event_id'];
} else {
    $event_id = $_GET['event_id'];
}

$reserve_controller = new EventRegisterController();

$movie = $reserve_controller->movie_list($event_id);
$course_list = $reserve_controller->course_list($movie->course_info_id);
$result_list = $reserve_controller->event_list($event_id);

// バリデーションエラー
$errors   = $_SESSION['errors']   ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

if ($movie) {
    $category_list = $result_list['category_list'] ?? [];
    $event_list = $result_list['event_list']  ?? [];
    $file_name = $movie->file_name;
    $course_info_id = $movie->course_info_id;
} else {
    $_SESSION['message_error'] = '動画資料が存在しません';
    header('Location: /custom/app/Views/event/register.php');
    exit;
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
                    <source id="movie_video_source" src="<?= htmlspecialchars('/uploads/movie/' . $result_list['course_info_id'] . '/' . $result_list['course_no'] . '/' . $file_name, ENT_QUOTES, 'UTF-8') ?>" type="video/mp4">
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
<script>
    $(document).ready(function() {
        // PHPから動画ファイル名を取得
        let video_file_name = null;
        <?php if (isset($movie->course_info_id) && isset($file_name) && isset($result_list['course_no'])): ?>
            video_file_name = "<?php echo htmlspecialchars($movie->course_info_id . '/' . $result_list['course_no'] . '/' . $file_name, ENT_QUOTES, 'UTF-8'); ?>";
        <?php endif; ?>

        const is_double_speed = $('.movie_wrap').data('is-double-speed');

        if (video_file_name) {
            let video_path = "/uploads/movie/" + video_file_name;
            $('#movie_video_source').attr('src', video_path);

            $('#movie_video')[0].load();
            $('#movie_video')[0].oncanplay = function() {
                $('#movie_video').show();
                $('#movie_img').hide();
            }
        }

        $('#movie_video').on('contextmenu', function(event) {
            event.preventDefault();
        });

        $('#movie_video_source').on('click', function(event) {
            event.preventDefault();
        });

        $('#movie_video').on('contextmenu', function(event) {
            event.preventDefault();
        });

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

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>