<?php
require '/var/www/vendor/autoload.php';
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Controllers/event/event_application_register_controller.php');
require_once($CFG->dirroot . '/custom/app/Controllers/event/event_movie_controller.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
include($CFG->dirroot . '/custom/app/Views/common/header.php');

use Dotenv\Dotenv;

// 講義動画取得
$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();

$course_info_id = isset($_POST['course_info_id']) ? $_POST['course_info_id'] : null;
if (empty($course_info_id)) {
    redirect(new moodle_url('/custom/app/Views/event/register.php'));
    exit;
}
$event_movie_controller = new EventMovieController();
$result_list = $event_movie_controller->index($course_info_id);

// バリデーションエラー
$errors   = $_SESSION['errors']   ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

// 動画を見たら、オンデマンド配信イベントは参加済みとして未ログインであったらログイン画面に遷移
$user_id = $_SESSION['user_id'] ?? null;
if ($user_id) {
    $event_application_register_controller = new EventRegisterController();
    $res = $event_application_register_controller->updateParticipation($user_id, $course_info_id);
} else {
    redirect(new moodle_url('/custom/app/Views/login/index.php'));
    exit;
}

$cloud_front_domain =  $_ENV['CLOUD_FRONT_DOMAIN'];
$expires = time() + 3600;
$key_pair_id = $_ENV['KEY_PAIR_ID'];
$private_key_path = $_ENV['PRIVATE_KEY_PATH'];

// カスタムポリシーJSON
$policy = json_encode([
    "Statement" => [[
        "Resource" => "$cloud_front_domain/*",
        "Condition" => [
            "DateLessThan" => ["AWS:EpochTime" => $expires]
        ]
    ]]
]);

// Base64-URLエンコード関数
function base64url_encode($input)
{
    return strtr(rtrim(base64_encode($input), '='), '+/', '-_');
}

// 秘密鍵読み込み
$privateKey = file_get_contents($private_key_path);

// 署名生成
openssl_sign($policy, $signature, $privateKey, OPENSSL_ALGO_SHA1);

// Cookie用の値にエンコード
$encodedPolicy = base64url_encode($policy);
$encodedSignature = base64url_encode($signature);

// Cookieを発行
setcookie('CloudFront-Policy', $encodedPolicy, [
    'expires' => $expires,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None'
]);

setcookie('CloudFront-Signature', $encodedSignature, [
    'expires' => $expires,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

setcookie('CloudFront-Key-Pair-Id', $key_pair_id, [
    'expires' => $expires,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

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
                <video id="movie_video"
                    controls
                    oncontextmenu="return false;"
                    disablePictureInPicture
                    <?= $result_list['is_double_speed'] != 1 ? 'controlsList="nodownload, noplaybackrate"' : 'controlsList="nodownload"'; ?>>
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
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
    $(window).on('load', function() {
        // PHPから動画ファイル名を取得
        console.log('ok');
        const s3_file_name = <?= json_encode($result_list['path']) ?>;
        const is_double_speed = <?= json_encode($result_list['is_double_speed']) ?>;
        const video = document.getElementById('movie_video');
        const controls_area = document.getElementById('controls_area');
        if (s3_file_name) {
            const m3u8Url = "https://d1q5pewnweivby.cloudfront.net/" + s3_file_name;
            if (Hls.isSupported()) {
                const hls = new Hls();
                hls.loadSource(m3u8Url);
                hls.attachMedia(video);
                hls.on(Hls.Events.MANIFEST_PARSED, function() {
                    $('#movie_video').css('display', 'block');
                    // 倍速再生ボタン
                    if (is_double_speed == 1) {
                        const speedBtn = document.createElement('button');
                        speedBtn.textContent = "1x";
                        let speeds = [1, 1.25, 1.5, 2];
                        let index = 0;
                        speedBtn.addEventListener('click', () => {
                            index = (index + 1) % speeds.length;
                            video.playbackRate = speeds[index];
                            speedBtn.textContent = speeds[index] + 'x';
                        });
                        speedBtn.style.marginLeft = '10px';
                        controls_area.appendChild(speedBtn);
                    }

                });
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = m3u8Url;
                if (is_double_speed == 1) {
                    const speedBtn = document.createElement('button');
                    speedBtn.textContent = "1x";
                    let speeds = [1, 1.25, 1.5, 2];
                    let index = 0;
                    speedBtn.addEventListener('click', () => {
                        index = (index + 1) % speeds.length;
                        video.playbackRate = speeds[index];
                        speedBtn.textContent = speeds[index] + 'x';
                    });
                    document.getElementById('controls_area').appendChild(speedBtn);
                }
                $('#movie_video').css('display', 'block');
            }
        }
        $('#movie_video').on('contextmenu', function(event) {
            event.preventDefault();
        });

        // 動画ソースへのクリックを無効化
        $('#movie_video_source').on('click', function(event) {
            event.preventDefault();
        });

        // 動画の右クリックメニューを無効化
        $('#movie_video').on('contextmenu', function(event) {
            event.preventDefault();
        });

        // マウスが動画に乗ったときの設定
        $('#movie_video').on('mouseenter', function() {
            // ダウンロードボタンを非表示にする
            $(this).prop('controlsList', 'nodownload');
            //倍速の設定
            if (is_double_speed != 1) {
                $(this).prop('controlsList', 'nodownload noplaybackrate'); // 倍速無効化
            } else {
                $(this).prop('controlsList', 'nodownload'); // ダウンロードだけ無効化
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

        // ファイル選択時の処理
        $('#video_input').on('change', function(event) {
            const file = event.target.files[0];
            $('#delete_video_btn').hide();
            if (!file) {
                $('#movie_video').hide();
                $('#movie_img').hide();
                return
            }

            $('#movie_video').hide();
            $('#movie_img').hide();

            // 動画ファイルでない場合はエラーメッセージ
            if (!file.type.startsWith('video/')) {
                alert('動画ファイルを選択してください');
                $(this).val('');
                return;
            }

            const video = document.createElement('video');
            const file_url = URL.createObjectURL(file);
            video.src = file_url;
            video.muted = true;
            video.playsInline = true;
            video.preload = "metadata"; // 最小限のデータ取得

            $(video).on('loadeddata', function() {
                // 最初のフレームへ
                video.currentTime = 0;
            });

            $(video).on('seeked', function() {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                // 解像度を半分にして負荷軽減
                canvas.width = video.videoWidth / 2;
                canvas.height = video.videoHeight / 2;

                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                // サムネイル表示
                $('#movie_img').attr('src', canvas.toDataURL('image/png')).show();
                URL.revokeObjectURL(file_url);
            });
        });
    });

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
    });
</script>