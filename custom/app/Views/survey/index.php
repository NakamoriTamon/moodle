<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Controllers/survey/survey_application_controller.php');
include($CFG->dirroot . '/custom/app/Views/common/header.php');

global $DB;
global $USER;


// $old_input を既に取得しているので、ここで使用できる
$event_application_id = "";
if (isset($_GET['course_info_id'])) {
    $course_info_id = $_GET['course_info_id'];
    $event_application_id = $_GET['event_application_id'];
} elseif (isset($_SESSION['course_info_id'])) {
    $course_info_id = $_SESSION['course_info_id'];
    $courseevent_application_id_info_id = $_SESSION['event_application_id'];
} elseif (isset($old_input['course_info_id'])) {
    $course_info_id = $old_input['course_info_id'];
    $event_application_id = $old_input['event_application_id'];
}

$formdata = isset($old_input['survey_params']) ? $old_input['survey_params'] : null;
$surveyApplicationController = new SurveyApplicationController();
$surveys = $surveyApplicationController->surveys($course_info_id, $formdata);
$event = $surveys['data'];
$department = $surveys['department'];
$passage = $surveys['passage'];
$event_survey_customfield_category_id = $surveys['event_survey_customfield_category_id'];

$prefectures = PREFECTURES;

$startTime = $event->start_hour;
$endTime   = $event->end_hour;
$startTimestamp = strtotime($startTime);
$endTimestamp   = strtotime($endTime);
$diffSeconds = $endTimestamp - $startTimestamp;
$diffMinutes = $diffSeconds / 60;

if ($surveys['exist']) {
    echo '<script type="text/javascript">
            window.location.href = "/custom/app/Views/survey/exist.php";
          </script>';
    exit;
}
$event_kbn = $event->event_kbn;

?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/survey.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="QUESTIONNAIRE">アンケート</h2>
    </section>
    <!-- heading -->
    <section id="quest" class="inner_l">
        <p class="quest_head"><?= htmlspecialchars(date("Y年m月d日", strtotime($event->course_date))); ?> / <?php if ($event_kbn == PLURAL_EVENT) { ?>【第<?= htmlspecialchars($event->no); ?>回】<?php } ?> <?= htmlspecialchars($event->name); ?></p>
        <form method="POST" action="/custom/app/Views/survey/survey-upsert.php" class="whitebox quest_form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="event_id" value="<?php echo $event->event_id ?>">
            <input type="hidden" name="event_application_id" value="<?php echo $event_application_id ?>">
            <input type="hidden" name="course_info_id" value="<?php echo $event->id ?>">
            <input type="hidden" name="event_survey_customfield_category_id" value="<?php echo $event_survey_customfield_category_id ?>">
            <?php if (!empty($basic_error)) { ?><p class="error"> <?= $basic_error ?></p><?php } ?>
            <div class="inner_s">
                <div class="form_block form03">
                    <!-- <p class="red">
                        <span>以下の質問について、差し支えない範囲でご回答ください。(任意)</span>
                    </p> -->
                    <ul class="list">
                        <li>
                            <h4 class="sub_ttl">年代を教えて下さい。</h4>
                            <div class="list_field f_radio">
                                <label>
                                    <input type="radio" name="age" value="1" <?php if (isset($old_input['age']) && $old_input['age'] === '1') echo 'checked'; ?>>
                                    20歳未満
                                </label>
                                <label>
                                    <input type="radio" name="age" value="2" <?php if (isset($old_input['age']) && $old_input['age'] === '2') echo 'checked'; ?>>
                                    20歳台
                                </label>
                                <label>
                                    <input type="radio" name="age" value="3" <?php if (isset($old_input['age']) && $old_input['age'] === '3') echo 'checked'; ?>>
                                    30歳台
                                </label>
                                <label>
                                    <input type="radio" name="age" value="4" <?php if (isset($old_input['age']) && $old_input['age'] === '4') echo 'checked'; ?>>
                                    40歳台
                                </label>
                                <label>
                                    <input type="radio" name="age" value="5" <?php if (isset($old_input['age']) && $old_input['age'] === '5') echo 'checked'; ?>>
                                    50歳台
                                </label>
                                <label>
                                    <input type="radio" name="age" value="6" <?php if (isset($old_input['age']) && $old_input['age'] === '6') echo 'checked'; ?>>
                                    60歳台
                                </label>
                                <label>
                                    <input type="radio" name="age" value="7" <?php if (isset($old_input['age']) && $old_input['age'] === '7') echo 'checked'; ?>>
                                    70歳以上
                                </label>
                            </div>
                        </li>
                        <li>
                            <h4 class="sub_ttl">ご職業や学生区分を教えてください。</h4>
                            <div class="list_field f_radio">
                                <label>
                                    <input type="radio" name="work" value="1" <?php if (isset($old_input['work']) && $old_input['work'] === '1') echo 'checked'; ?>>
                                    高校生以下
                                </label>
                                <label>
                                    <input type="radio" name="work" value="2" <?php if (isset($old_input['work']) && $old_input['work'] === '2') echo 'checked'; ?>>
                                    学生（高校生、大学生、大学院生等）
                                </label>
                                <label>
                                    <input type="radio" name="work" value="3" <?php if (isset($old_input['work']) && $old_input['work'] === '3') echo 'checked'; ?>>
                                    会社員
                                </label>
                                <label>
                                    <input type="radio" name="work" value="4" <?php if (isset($old_input['work']) && $old_input['work'] === '4') echo 'checked'; ?>>
                                    自営業・フリーランス
                                </label>
                                <label>
                                    <input type="radio" name="work" value="5" <?php if (isset($old_input['work']) && $old_input['work'] === '5') echo 'checked'; ?>>
                                    公務員
                                </label>
                                <label>
                                    <input type="radio" name="work" value="6" <?php if (isset($old_input['work']) && $old_input['work'] === '6') echo 'checked'; ?>>
                                    教職員
                                </label>
                                <label>
                                    <input type="radio" name="work" value="7" <?php if (isset($old_input['work']) && $old_input['work'] === '7') echo 'checked'; ?>>
                                    無職
                                </label>
                            </div>
                        </li>
                        <li>
                            <h4 class="sub_ttl">
                                お住まいの地域を教えてください。
                                <span class="comment">※〇〇県△△市のようにご回答ください（例：大阪府豊中市）。</span>
                            </h4>
                            <div class="list_field f_area">
                                <div class="area_item01">
                                    <label>都道府県 </label>
                                    <div class="select">
                                        <select name="prefecture_disabled" disabled>
                                            <?php foreach ($prefectures as $prefecture) { ?>
                                                <option value="<?= htmlspecialchars($prefecture) ?>"
                                                    <?= isSelected($prefecture, $USER->city ?? null, null) ? 'selected' : '' ?>>
                                                    <?= $prefecture ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <input type="hidden" name="prefecture" value="<?= htmlspecialchars($USER->city ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>
                                <div class="area_item02">
                                    <label>市町村 </label>
                                    <input type="text" name="address" id="address" style="width: 50%;" value="<?php echo htmlspecialchars($old_input['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php if (!empty($errors['address'])): ?>
                                        <div class="error-msg">
                                            <?= htmlspecialchars($errors['address']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        <?php if (!empty($errors['passage'])): ?>
                            <?php foreach ($errors['passage'] as $key => $message): ?>
                                <?php if (!empty($message)): ?>
                                    <div class="error-msg"><?= htmlspecialchars($message); ?></div><br>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php echo $passage ?>
                    </ul>
                </div>
                <div class="form_block form01">
                    <ul class="list">
                        <li>
                            <h4 class="sub_ttl">本日のイベントについて、ご意見・ご感想をお書きください。</h4>
                            <div class="list_field f_txtarea">
                                <textarea name="impression"><?php echo htmlspecialchars($old_input['impression'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                        </li>
                        <li>
                            <h4 class="sub_ttl">
                                今までに大阪大学主催のイベントに参加されたことはありますか
                            </h4>
                            <div class="list_field f_radio">
                                <label>
                                    <input type="radio" name="participation" value="1" <?php if (isset($old_input['participation']) && $old_input['participation'] === '1') echo 'checked'; ?>>ある
                                </label>
                                <label>
                                    <input type="radio" name="participation" value="2" <?php if (isset($old_input['participation']) && $old_input['participation'] === '2') echo 'checked'; ?>>ない
                                </label>
                            </div>
                            <?php if (!empty($errors['participation'])): ?>
                                <div class="error-msg" style="margin-top:15px">
                                    <?= htmlspecialchars($errors['participation']); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
                <div class="form_block form02">
                    <!-- <p class="red">
                        <span>今回が初回受講の方は、以下の質問にすべてご回答ください。</span>
                    </p> -->
                    <ul class="list">
                        <li>
                            <h4 class="sub_ttl">
                                本日のイベントをどのようにしてお知りになりましたか。
                                <span class="comment">※複数回答可</span>
                            </h4>
                            <div class="list_field f_check">
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="found_method[]" value="1" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('1', $old_input['found_method'])) echo 'checked'; ?>>
                                        チラシ
                                        <span class="comment">※「その他」の欄にどこで受け取られたかをご記入ください</span>
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="found_method[]" value="2" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('2', $old_input['found_method'])) echo 'checked'; ?>>
                                        ウェブサイト
                                        <span class="comment">※「その他」の欄にウェブサイト名をご記入ください</span>
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="found_method[]" value="3" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('3', $old_input['found_method'])) echo 'checked'; ?>>
                                        メールマガジン
                                        <span class="comment">※「その他」の欄にメールマガジン名をご記入ください</span>
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="found_method[]" value="4" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('4', $old_input['found_method'])) echo 'checked'; ?>>
                                        SNS（X, Instagram, Facebookなど）
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="found_method[]" value="6" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('6', $old_input['found_method'])) echo 'checked'; ?>>
                                        大阪大学卒業生メールマガジン
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="found_method[]" value="7" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('7', $old_input['found_method'])) echo 'checked'; ?>>
                                        大阪大学入試課からのメール
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="found_method[]" value="9" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('9', $old_input['found_method'])) echo 'checked'; ?>>
                                        知人からの紹介
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="found_method[]" value="10" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('10', $old_input['found_method'])) echo 'checked'; ?>>
                                        講師・スタッフからの紹介
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="found_method[]" value="11" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('11', $old_input['found_method'])) echo 'checked'; ?>>
                                        自治体の広報・掲示
                                    </label>
                                </div>
                                <div class="other_item">
                                    <label> その他 </label>
                                    <input type="text" name="other_found_method" value="<?php echo htmlspecialchars($old_input['other_found_method'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                            </div>
                            <?php if (!empty($errors['found_method'])): ?>
                                <div class="error-msg">
                                    <?= htmlspecialchars($errors['found_method']); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                        <li>
                            <h4 class="sub_ttl">
                                本日のイベントに参加した理由は何ですか？
                                <span class="comment">※複数回答可</span>
                            </h4>
                            <div class="list_field f_check">
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="reason[]" value="3" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('3', $old_input['reason'])) echo 'checked'; ?>>
                                        本日の講師に関心があったから
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="reason[]" value="4" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('4', $old_input['reason'])) echo 'checked'; ?>>
                                        大阪大学のイベントに参加したかったから
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="reason[]" value="5" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('5', $old_input['reason'])) echo 'checked'; ?>>
                                        教養を高めたいから
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="reason[]" value="6" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('6', $old_input['reason'])) echo 'checked'; ?>>
                                        仕事に役立つと思われたから
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="reason[]" value="7" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('7', $old_input['reason'])) echo 'checked'; ?>>
                                        日常生活に役立つと思われたから
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="reason[]" value="8" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('8', $old_input['reason'])) echo 'checked'; ?>>
                                        余暇を有効に利用したかったから
                                    </label>
                                </div>
                                <div class="other_item">
                                    <label> その他 </label>
                                    <input type="text" name="reason_other" value="<?php echo htmlspecialchars($old_input['reason_other'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                            </div>
                            <?php if (!empty($errors['reason'])): ?>
                                <div class="error-msg">
                                    <?= htmlspecialchars($errors['reason']); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                        <li>
                            <h4 class="sub_ttl">
                                本日のイベントの満足度について、あてはまるもの1つをお選びください。
                            </h4>
                            <div class="list_field f_radio">
                                <label>
                                    <input type="radio" name="satisfaction" value="1" <?php if (isset($old_input['satisfaction']) && $old_input['satisfaction'] === '1') echo 'checked'; ?>>
                                    非常に満足
                                </label>
                                <label>
                                    <input type="radio" name="satisfaction" value="2" <?php if (isset($old_input['satisfaction']) && $old_input['satisfaction'] === '2') echo 'checked'; ?>>
                                    満足
                                </label>
                                <label>
                                    <input type="radio" name="satisfaction" value="3" <?php if (isset($old_input['satisfaction']) && $old_input['satisfaction'] === '3') echo 'checked'; ?>>
                                    ふつう
                                </label>
                                <label>
                                    <input type="radio" name="satisfaction" value="4" <?php if (isset($old_input['satisfaction']) && $old_input['satisfaction'] === '4') echo 'checked'; ?>>
                                    不満
                                </label>
                                <label>
                                    <input type="radio" name="satisfaction" value="5" <?php if (isset($old_input['satisfaction']) && $old_input['satisfaction'] === '5') echo 'checked'; ?>>
                                    非常に不満
                                </label>
                            </div>
                            <?php if (!empty($errors['satisfaction'])): ?>
                                <div class="error-msg" style="margin-top:15px">
                                    <?= htmlspecialchars($errors['satisfaction']); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                        <li>
                            <h4 class="sub_ttl">
                                （会場での開催の場合のみ回答ください）本日のイベントの開催環境について、あてはまるものを１つお選びください。
                                <span class="comment">※「あまり快適ではなかった」「全く快適ではなかった」と回答された方は次の質問にその理由を教えてください。</span>
                            </h4>
                            <div class="list_field f_radio">
                                <label>
                                    <input type="radio" name="holding_enviroment" value="1" <?php if (isset($old_input['holding_enviroment']) && $old_input['holding_enviroment'] === '1') echo 'checked'; ?>>
                                    とても快適だった
                                </label>
                                <label>
                                    <input type="radio" name="holding_enviroment" value="2" <?php if (isset($old_input['holding_enviroment']) && $old_input['holding_enviroment'] === '2') echo 'checked'; ?>>
                                    快適だった
                                </label>
                                <label>
                                    <input type="radio" name="holding_enviroment" value="3" <?php if (isset($old_input['holding_enviroment']) && $old_input['holding_enviroment'] === '3') echo 'checked'; ?>>
                                    ふつう
                                </label>
                                <label>
                                    <input type="radio" name="holding_enviroment" value="4" <?php if (isset($old_input['holding_enviroment']) && $old_input['holding_enviroment'] === '4') echo 'checked'; ?>>
                                    あまり快適ではなかった
                                </label>
                                <label>
                                    <input type="radio" name="holding_enviroment" value="5" <?php if (isset($old_input['holding_enviroment']) && $old_input['holding_enviroment'] === '5') echo 'checked'; ?>>
                                    全く快適ではなかった
                                </label>
                            </div>
                            <?php if (!empty($errors['holding_enviroment'])): ?>
                                <div class="error-msg" style="margin-top:15px">
                                    <?= htmlspecialchars($errors['holding_enviroment']); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                        <li>
                            <h4 class="sub_ttl">
                                【問7】で「あまり快適ではなかった」「全く快適ではなかった」と回答された方はその理由を教えてください。
                            </h4>
                            <div class="list_field f_txtarea">
                                <textarea name="no_good_enviroment_reason" row="20px"><?php echo htmlspecialchars($old_input['no_good_enviroment_reason'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <?php if (!empty($errors['no_good_enviroment_reason'])): ?>
                                <div class="error-msg">
                                    <?= htmlspecialchars($errors['no_good_enviroment_reason']); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                        <li>
                            <h4 class="sub_ttl">
                                今後の大阪大学主催のイベントで、希望するジャンルやテーマ、話題があれば、ご提案ください。
                            </h4>
                            <div class="list_field f_txtarea">
                                <textarea name="lecture_suggestions" row="20px"><?php echo htmlspecialchars($old_input['lecture_suggestions'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <?php if (!empty($errors['lecture_suggestions'])): ?>
                                <div class="error-msg">
                                    <?= htmlspecialchars($errors['lecture_suggestions']); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>
            <input type="submit" class="btn btn_red" value="アンケート内容を送信する" />
        </form>
    </section>
    <!-- quest -->
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>アンケート</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>