<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Controllers/survey/survey_application_controller.php');

global $DB;
global $USER;

$surveyApplicationController = new SurveyApplicationController();

// $old_input を既に取得しているので、ここで使用できる
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
} elseif (isset($_SESSION['event_id'])) {
    $event_id = $_SESSION['event_id'];
} elseif (isset($old_input['event_id'])) {
    $event_id = $old_input['event_id'];
} else {
    // event_id が見つからない場合のエラーハンドリング
    $_SESSION['message_error'] = 'イベントIDが指定されていません。';
    header("Location: /custom/app/Views/event/register.php");
    exit;
}

$event = $surveyApplicationController->events($event_id);
$survey_list = $surveyApplicationController->index($event_id);

$prefectures = PREFECTURES;

$startTime = $event->start_hour;
$endTime   = $event->end_hour;
$startTimestamp = strtotime($startTime);
$endTimestamp   = strtotime($endTime);
$diffSeconds = $endTimestamp - $startTimestamp;
$diffMinutes = $diffSeconds / 60;

if ($survey_list) {
    header("Location: /custom/app/Views/event/register.php");
    exit;
}
include($CFG->dirroot . '/custom/app/Views/common/header.php');

?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/survey.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="QUESTIONNAIRE">アンケート</h2>
    </section>
    <!-- heading -->
    <section id="quest" class="inner_l">
        <p class="quest_head"><?= htmlspecialchars(date("Y年m月d日", strtotime($event->event_date))); ?> / <?= htmlspecialchars($event->name); ?></p>
        <form method="POST" action="/custom/app/Views/survey/survey-upsert.php" class="whitebox quest_form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="event_id" value="<?php echo $event_id ?>">
            <?php if (!empty($basic_error)) { ?><p class="error"> <?= $basic_error ?></p><?php } ?>
            <div class="inner_s">
                <div class="form_block form01">
                    <ul class="list">
                        <li>
                            <h4 class="sub_ttl">本日の講義内容について、ご意見・ご感想をお書きください。</h4>
                            <div class="list_field f_txtarea">
                                <textarea name="impression"><?php echo htmlspecialchars($old_input['impression'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                        </li>
                        <li>
                            <h4 class="sub_ttl">
                                今までに大阪大学公開講座のプログラムに参加されたことはありますか
                            </h4>
                            <div class="list_field f_radio">
                                <label>
                                    <input type="radio" name="participation" value="1" <?php if (isset($old_input['participation']) && $old_input['participation'] === '1') echo 'checked'; ?>>はい
                                </label>
                                <label>
                                    <input type="radio" name="participation" value="2" <?php if (isset($old_input['participation']) && $old_input['participation'] === '2') echo 'checked'; ?>>いいえ
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
                    <p class="red">
                        <span>今回が初回受講の方は、以下の質問にすべてご回答ください。</span>
                    </p>
                    <p class="comment">※「その他」の欄にどこでご覧になったか具体的にご記載ください</p>
                    <ul class="list">
                        <li>
                            <h4 class="sub_ttl">
                                本日のプログラムをどのようにしてお知りになりましたか。
                                <span class="comment">※複数回答可</span>
                            </h4>
                            <div class="list_field f_check">
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="found_method[]" value="1" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('1', $old_input['found_method'])) echo 'checked'; ?>>
                                        チラシ
                                        <span class="comment">※「その他」の欄にどこでご覧になったか具体的にご記載ください</span>
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="found_method[]" value="2" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('2', $old_input['found_method'])) echo 'checked'; ?>>
                                        ウェブサイト
                                        <span class="comment">※「その他」の欄にどこでご覧になったか具体的にご記載ください</span>
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="found_method[]" value="3" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('3', $old_input['found_method'])) echo 'checked'; ?>>
                                        大阪大学公開講座「知の広場」からのメール
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
                                        <input type="checkbox" name="found_method[]" value="5" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('5', $old_input['found_method'])) echo 'checked'; ?>>
                                        21世紀懐徳堂からのメールマガジン
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
                                        <input type="checkbox" name="found_method[]" value="8" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('8', $old_input['found_method'])) echo 'checked'; ?>>
                                        Peatixからのメール
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
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="found_method[]" value="12" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('12', $old_input['found_method'])) echo 'checked'; ?>>
                                        スマートニュース広告
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
                                本日のテーマを受講した理由は何ですか？
                                <span class="comment">※複数回答可</span>
                            </h4>
                            <div class="list_field f_check">
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="reason[]" value="1" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('1', $old_input['reason'])) echo 'checked'; ?>>
                                        テーマに関心があったから
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="reason[]" value="2" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('2', $old_input['reason'])) echo 'checked'; ?>>
                                        本日のプログラム内容に関心があったから
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="reason[]" value="3" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('3', $old_input['reason'])) echo 'checked'; ?>>
                                        本日のゲストに関心があったから
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="checkbox" name="reason[]" value="4" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('4', $old_input['reason'])) echo 'checked'; ?>>
                                        大阪大学のプログラムに参加したかったから
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
                                本日のプログラムの満足度について、あてはまるもの1つをお選びください。
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
                                本日のプログラムの理解度について、あてはまるもの1つをお選びください。
                            </h4>
                            <div class="list_field f_radio">
                                <label>
                                    <input type="radio" name="understanding" value="1" <?php if (isset($old_input['understanding']) && $old_input['understanding'] === '1') echo 'checked'; ?>>
                                    よく理解できた
                                </label>
                                <label>
                                    <input type="radio" name="understanding" value="2" <?php if (isset($old_input['understanding']) && $old_input['understanding'] === '2') echo 'checked'; ?>>
                                    理解できた
                                </label>
                                <label>
                                    <input type="radio" name="understanding" value="3" <?php if (isset($old_input['understanding']) && $old_input['understanding'] === '3') echo 'checked'; ?>>
                                    ふつう
                                </label>
                                <label>
                                    <input type="radio" name="understanding" value="4" <?php if (isset($old_input['understanding']) && $old_input['understanding'] === '4') echo 'checked'; ?>>
                                    理解できなかった
                                </label>
                                <label>
                                    <input type="radio" name="understanding" value="5" <?php if (isset($old_input['understanding']) && $old_input['understanding'] === '5') echo 'checked'; ?>>
                                    全く理解できなかった
                                </label>
                            </div>
                            <?php if (!empty($errors['understanding'])): ?>
                                <div class="error-msg" style="margin-top:15px">
                                    <?= htmlspecialchars($errors['understanding']); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                        <li>
                            <h4 class="sub_ttl">
                                本日のプログラムで特に良かった点について教えてください。<br />
                                以下にあてはまるものがあれば、一つお選びください。あてはまるものがなければ、「その他」の欄に記述してください。
                            </h4>
                            <div class="list_field f_check">
                                <div class="check_item">
                                    <label>
                                        <input type="radio" name="good_point" value="1" <?php if (isset($old_input['good_point']) && $old_input['good_point'] === '1') echo 'checked'; ?>>
                                        テーマについて考えを深めることができた
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="radio" name="good_point" value="2" <?php if (isset($old_input['good_point']) && $old_input['good_point'] === '2') echo 'checked'; ?>>
                                        最先端の研究について学べた
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="radio" name="good_point" value="3" <?php if (isset($old_input['good_point']) && $old_input['good_point'] === '3') echo 'checked'; ?>>
                                        大学の研究者と対話ができた
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="radio" name="good_point" value="4" <?php if (isset($old_input['good_point']) && $old_input['good_point'] === '4') echo 'checked'; ?>>
                                        大学の講義の雰囲気を味わえた
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="radio" name="good_point" value="5" <?php if (isset($old_input['good_point']) && $old_input['good_point'] === '5') echo 'checked'; ?>>
                                        大阪大学について知ることができた
                                    </label>
                                </div>
                                <div class="check_item">
                                    <label>
                                        <input type="radio" name="good_point" value="6" <?php if (isset($old_input['good_point']) && $old_input['good_point'] === '6') echo 'checked'; ?>>
                                        身の周りの社会課題に対する解決のヒントが得られた
                                    </label>
                                </div>
                                <div class="other_item">
                                    <label> その他 </label>
                                    <input type="text" name="other_good_point" value="<?php echo htmlspecialchars($old_input['other_good_point'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                            </div>
                            <?php if (!empty($errors['good_point'])): ?>
                                <div class="error-msg">
                                    <?= htmlspecialchars($errors['good_point']); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                        <li>
                            <h4 class="sub_ttl">
                                本日のプログラムの開催時間(<?= htmlspecialchars($diffMinutes); ?>分)について、あてはまるもの1つをお選びください。
                            </h4>
                            <div class="list_field f_radio">
                                <label>
                                    <input type="radio" name="time" value="1" <?php if (isset($old_input['time']) && $old_input['time'] === '1') echo 'checked'; ?>>
                                    適当である
                                </label>
                                <label>
                                    <input type="radio" name="time" value="2" <?php if (isset($old_input['time']) && $old_input['time'] === '2') echo 'checked'; ?>>
                                    長すぎる
                                </label>
                                <label>
                                    <input type="radio" name="time" value="3" <?php if (isset($old_input['time']) && $old_input['time'] === '3') echo 'checked'; ?>>
                                    短すぎる
                                </label>
                            </div>
                            <?php if (!empty($errors['time'])): ?>
                                <div class="error-msg" style="margin-top:15px">
                                    <?= htmlspecialchars($errors['time']); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                        <li>
                            <h4 class="sub_ttl">
                                本日のプログラムの開催環境について、あてはまるものを１つお選びください。
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
                                【問9】で「あまり快適ではなかった」「全く快適ではなかった」と回答された方はその理由を教えてください。
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
                                今後の大阪大学公開講座で希望するジャンルやテーマや話題があればご提案ください。
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
                        <li>
                            <h4 class="sub_ttl">
                                話を聞いてみたい大阪大学の教員や研究者がいれば、具体的にご提案ください。
                            </h4>
                            <div class="list_field f_txtarea">
                                <textarea name="speaker_suggestions" row="20px"><?php echo htmlspecialchars($old_input['speaker_suggestions'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <?php if (!empty($errors['speaker_suggestions'])): ?>
                                <div class="error-msg">
                                    <?= htmlspecialchars($errors['speaker_suggestions']); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
                <div class="form_block form03">
                    <p class="red">
                        <span>以下、差し支えなければご回答ください。</span>
                    </p>
                    <ul class="list">
                        <li>
                            <h4 class="sub_ttl">ご職業等を教えてください。</h4>
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
                                    パート・アルバイト
                                </label>
                                <label>
                                    <input type="radio" name="work" value="8" <?php if (isset($old_input['work']) && $old_input['work'] === '8') echo 'checked'; ?>>
                                    主婦・主夫
                                </label>
                                <label>
                                    <input type="radio" name="work" value="9" <?php if (isset($old_input['work']) && $old_input['work'] === '9') echo 'checked'; ?>>
                                    定年退職
                                </label>
                                <label>
                                    <input type="radio" name="work" value="10" <?php if (isset($old_input['work']) && $old_input['work'] === '10') echo 'checked'; ?>>
                                    その他
                                </label>
                            </div>
                        </li>
                        <li>
                            <h4 class="sub_ttl">性別をご回答ください。</h4>
                            <div class="list_field f_radio">
                                <label>
                                    <input type="radio" name="sex" value="1" <?php if (isset($old_input['sex']) && $old_input['sex'] === '1') echo 'checked'; ?>>
                                    女性
                                </label>
                                <label>
                                    <input type="radio" name="sex" value="2" <?php if (isset($old_input['sex']) && $old_input['sex'] === '2') echo 'checked'; ?>>
                                    男性
                                </label>
                                <label>
                                    <input type="radio" name="sex" value="3" <?php if (isset($old_input['sex']) && $old_input['sex'] === '3') echo 'checked'; ?>>
                                    その他
                                </label>
                            </div>
                        </li>
                        <li>
                            <h4 class="sub_ttl">
                                お住いの地域を教えてください。
                                <span class="comment">※〇〇県△△市のようにご回答ください</span>
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