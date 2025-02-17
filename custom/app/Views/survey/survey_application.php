<?php
require_once('/var/www/html/moodle/custom/app/Controllers/SurveyCustomFieldController.php');
$eventId = 2;
$surveyCustomFieldController = new SurveyCustomFieldController();
$responce = $surveyCustomFieldController->getSurveyCustomField($eventId);

$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お申込みフォーム</title>
    <style>
        form label {
            margin-bottom: 10px;
            display: inline-block;
        }

        form input,
        form textarea,
        form select {
            display: block;
            margin-bottom: 2rem;
            width: 50%;
            padding: 8px;
            box-sizing: border-box;
        }

        .label_d_flex {
            display: flex;
        }

        .label_d_flex input {
            margin-bottom: 10px;
        }

        .container {
            margin-top: 80px;
        }

        .label_name {
            color: #2D287F;
            font-weight: bold;
        }

        form {
            padding: 3rem;
        }

        h2 {
            padding-left: 3rem;
            margin-top: 80px;
            color: #2D287F;
        }

        form textarea {
            height: 10vh;
        }

        .radio-group {
            display: flex;
            flex-wrap: wrap;
        }

        .radio-group label {
            display: inline-block;
            margin-right: 20px;
        }

        .checkbox-group label {
            display: inline-block;
            margin-bottom: 0px;
        }

        .checkbox-group input,
        .radio-group input {
            display: initial;
            width: initial;
            margin-right: .5rem;
        }

        #submit {
            background-color: #5b5b5b;
        }

        .divider {
            text-align: left;
            border-bottom: 1px solid #ccc;
            line-height: 0.1em;
            margin: 20px 0;
        }

        .divider:before {
            content: attr(data-divider);
            background: #fff;
            padding: 0 10px;
            color: red;
        }

        .text-danger {
            color: red;
        }

        .policy-agreement {
            margin: 1em 0;
        }

        .policy-label {
            display: inline-flex;
            align-items: center;
            cursor: pointer;
        }

        .policy-label input[type="checkbox"] {
            width: auto;
            margin-right: 0.5em;
            cursor: pointer;
        }

        .policy-text a {
            text-decoration: underline;
            color: #007bff;
        }

        .policy-text a:hover {
            text-decoration: none;
        }

        .submit_button {
            display: flex;
            margin-top: 2vh;
            justify-content: center;
        }

        .error-message {
            color: red;
            margin-top: 4px;
            margin-bottom: 12px;
            font-size: 0.9em;
        }
    </style>
</head>
<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<div class="container">
    <h2>お申込みフォーム</h2>
    <form action="/custom/app/Views/survey/survey_application_insert.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="event_id" value="<?php echo $eventId ?>">

        <!-- プライバシーポリシー -->
        <div class="policy-agreement">
            <?php
            // 以前の入力がある場合は有効化する
            $policyChecked = isset($old_input['policy_agreement']) && $old_input['policy_agreement'] == '1';
            ?>
            <label for="policy_agreement" class="policy-label">
                <span class="policy-text">
                    個人情報の提供について、大学での個人情報保護に関する<br>
                    <a href="/policy" target="_blank" id="policy_link">プライバシーポリシー</a>を確認し、同意します。
                </span>
                <input type="checkbox" id="policy_agreement" name="policy_agreement" value="1"
                    <?php echo $policyChecked ? 'checked' : ''; ?>
                    <?php echo $policyChecked ? '' : 'disabled'; ?>>
                同意する
            </label>
            <?php if (!empty($errors['policy_agreement'])): ?>
                <div class="error-message">
                    <?= htmlspecialchars($errors['policy_agreement']); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- 講義内容への意見 -->
        <label class="label_name" for="impression">本日の講義内容について、ご意見・ご感想をお書きください </label>
        <textarea name="impression" row="20px"><?php echo htmlspecialchars($old_input['impression'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

        <!-- 参加経験 -->
        <label class="label_name" for="participation_experience">今までに大学公開講座のプログラムに参加されたことはありますか</label>
        <div class="radio-group">
            <label>
                <input type="radio" name="participation_experience" value="1" <?php if (isset($old_input['participation_experience']) && $old_input['participation_experience'] === '1') echo 'checked'; ?>>はい
            </label><br>
            <label>
                <input type="radio" name="participation_experience" value="2" <?php if (isset($old_input['participation_experience']) && $old_input['participation_experience'] === '2') echo 'checked'; ?>>いいえ
            </label><br>
        </div>
        <?php if (!empty($errors['participation_experience'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($errors['participation_experience']); ?>
            </div>
        <?php endif; ?>

        <p class="divider" data-divider="今回が初回受講の方は、以下の質問にすべてご回答ください。"></p>

        <!-- どのように知ったか (チェックボックス) -->
        <label class="label_name" for="found_method">本日のプログラムをどのようにしてお知りになりましたか</label>
        <div class="checkbox-group">
            <label>
                <input type="checkbox" name="found_method[]" value="1" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('1', $old_input['found_method'])) echo 'checked'; ?>>
                <span>チラシ(その他の欄にどこでご覧になったかをご記入ください)</span>
            </label><br>
            <label>
                <input type="checkbox" name="found_method[]" value="2" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('2', $old_input['found_method'])) echo 'checked'; ?>>
                <span>ウェブサイト(その他の欄にウェブサイト名をご記入ください)</span>
            </label><br>
            <label>
                <input type="checkbox" name="found_method[]" value="3" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('3', $old_input['found_method'])) echo 'checked'; ?>>
                <span>本プラットフォームからのメール</span>
            </label><br>
            <label>
                <input type="checkbox" name="found_method[]" value="4" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('4', $old_input['found_method'])) echo 'checked'; ?>>
                <span>SNS(X,Instagram,Facebookなど)</span>
            </label><br>
            <label>
                <input type="checkbox" name="found_method[]" value="5" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('5', $old_input['found_method'])) echo 'checked'; ?>>
                <span>21世紀懐徳堂からのメールマガジン</span>
            </label><br>
            <label>
                <input type="checkbox" name="found_method[]" value="6" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('6', $old_input['found_method'])) echo 'checked'; ?>>
                <span>大学卒業生メールマガジン</span>
            </label><br>
            <label>
                <input type="checkbox" name="found_method[]" value="7" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('7', $old_input['found_method'])) echo 'checked'; ?>>
                <span>Peatixからのメール</span>
            </label><br>
            <label>
                <input type="checkbox" name="found_method[]" value="8" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('8', $old_input['found_method'])) echo 'checked'; ?>>
                <span>知人からの紹介</span>
            </label><br>
            <label>
                <input type="checkbox" name="found_method[]" value="9" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('9', $old_input['found_method'])) echo 'checked'; ?>>
                <span>講師・スタッフからの紹介</span>
            </label><br>
            <label>
                <input type="checkbox" name="found_method[]" value="10" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('10', $old_input['found_method'])) echo 'checked'; ?>>
                <span>自治体の広報・掲示</span>
            </label><br>
            <label>
                <input type="checkbox" name="found_method[]" value="11" <?php if (isset($old_input['found_method']) && is_array($old_input['found_method']) && in_array('11', $old_input['found_method'])) echo 'checked'; ?>>
                <span>スマートニュース広告</span>
            </label><br>
        </div>
        <label class="label_name" for="other_found_method">その他</label>
        <textarea name="other_found_method" row="20px"><?php echo htmlspecialchars($old_input['other_found_method'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        <?php if (!empty($errors['found_method'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($errors['found_method']); ?>
            </div>
        <?php endif; ?>

        <!-- 受講理由 (チェックボックス) -->
        <label class="label_name" for="reason">本日のテーマを受講した理由は何ですか</label>
        <div class="checkbox-group">
            <label>
                <input type="checkbox" name="reason[]" value="1" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('1', $old_input['reason'])) echo 'checked'; ?>>
                <span>テーマに関心があったから</span>
            </label><br>
            <label>
                <input type="checkbox" name="reason[]" value="2" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('2', $old_input['reason'])) echo 'checked'; ?>>
                <span>本日のプログラム内容に関心があったから</span>
            </label><br>
            <label>
                <input type="checkbox" name="reason[]" value="3" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('3', $old_input['reason'])) echo 'checked'; ?>>
                <span>本日のゲストに関心があったから</span>
            </label><br>
            <label>
                <input type="checkbox" name="reason[]" value="4" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('4', $old_input['reason'])) echo 'checked'; ?>>
                <span>大学のプログラムに参加したかったから</span>
            </label><br>
            <label>
                <input type="checkbox" name="reason[]" value="5" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('5', $old_input['reason'])) echo 'checked'; ?>>
                <span>教養を高めたいから</span>
            </label><br>
            <label>
                <input type="checkbox" name="reason[]" value="6" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('6', $old_input['reason'])) echo 'checked'; ?>>
                <span>仕事に役立つと思われたから</span>
            </label><br>
            <label>
                <input type="checkbox" name="reason[]" value="7" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('7', $old_input['reason'])) echo 'checked'; ?>>
                <span>日常生活に役立つと思われたから</span>
            </label><br>
            <label>
                <input type="checkbox" name="reason[]" value="8" <?php if (isset($old_input['reason']) && is_array($old_input['reason']) && in_array('8', $old_input['reason'])) echo 'checked'; ?>>
                <span>余暇を有効に利用したかったから</span>
            </label><br>
        </div>
        <label class="label_name" for="reason_other">その他</label>
        <textarea name="reason_other" row="20px"><?php echo htmlspecialchars($old_input['reason_other'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        <?php if (!empty($errors['reason'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($errors['reason']); ?>
            </div>
        <?php endif; ?>

        <!-- 満足度 (ラジオボタン) -->
        <label class="label_name" for="satisfaction">本日のプログラムの満足度について、あてはまるものを1つお選びください</label>
        <div class="radio-group">
            <label>
                <input type="radio" name="satisfaction" value="1" <?php if (isset($old_input['satisfaction']) && $old_input['satisfaction'] === '1') echo 'checked'; ?>>
                <span>非常に満足</span>
            </label><br>
            <label>
                <input type="radio" name="satisfaction" value="2" <?php if (isset($old_input['satisfaction']) && $old_input['satisfaction'] === '2') echo 'checked'; ?>>
                <span>満足</span>
            </label><br>
            <label>
                <input type="radio" name="satisfaction" value="3" <?php if (isset($old_input['satisfaction']) && $old_input['satisfaction'] === '3') echo 'checked'; ?>>
                <span>ふつう</span>
            </label><br>
            <label>
                <input type="radio" name="satisfaction" value="4" <?php if (isset($old_input['satisfaction']) && $old_input['satisfaction'] === '4') echo 'checked'; ?>>
                <span>不満</span>
            </label><br>
            <label>
                <input type="radio" name="satisfaction" value="5" <?php if (isset($old_input['satisfaction']) && $old_input['satisfaction'] === '5') echo 'checked'; ?>>
                <span>非常に不満</span>
            </label><br>
        </div>
        <?php if (!empty($errors['satisfaction'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($errors['satisfaction']); ?>
            </div>
        <?php endif; ?>

        <!-- 理解度 (ラジオボタン) -->
        <label class="label_name" for="understanding">本日のプログラムの理解度について、あてはまるものを1つお選びください</label>
        <div class="radio-group">
            <label>
                <input type="radio" name="understanding" value="1" <?php if (isset($old_input['understanding']) && $old_input['understanding'] === '1') echo 'checked'; ?>>
                <span>よく理解できた</span>
            </label><br>
            <label>
                <input type="radio" name="understanding" value="2" <?php if (isset($old_input['understanding']) && $old_input['understanding'] === '2') echo 'checked'; ?>>
                <span>理解できた</span>
            </label><br>
            <label>
                <input type="radio" name="understanding" value="3" <?php if (isset($old_input['understanding']) && $old_input['understanding'] === '3') echo 'checked'; ?>>
                <span>ふつう</span>
            </label><br>
            <label>
                <input type="radio" name="understanding" value="4" <?php if (isset($old_input['understanding']) && $old_input['understanding'] === '4') echo 'checked'; ?>>
                <span>理解できなかった</span>
            </label><br>
            <label>
                <input type="radio" name="understanding" value="5" <?php if (isset($old_input['understanding']) && $old_input['understanding'] === '5') echo 'checked'; ?>>
                <span>全く理解できなかった</span>
            </label><br>
        </div>
        <?php if (!empty($errors['understanding'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($errors['understanding']); ?>
            </div>
        <?php endif; ?>

        <!-- 良かった点 (ラジオボタン) -->
        <label class="label_name" for="good_point">本日のプログラムで特に良かった点について教えてください。以下に当てはまるものがあれば一つお選びください<br>
            あてはまるものがなければ、「その他」の欄に記述してください。
        </label>
        <div class="radio-group">
            <label>
                <input type="radio" name="good_point" value="1" <?php if (isset($old_input['good_point']) && $old_input['good_point'] === '1') echo 'checked'; ?>>
                <span>テーマについて考えを深めることができた</span>
            </label><br>
            <label>
                <input type="radio" name="good_point" value="2" <?php if (isset($old_input['good_point']) && $old_input['good_point'] === '2') echo 'checked'; ?>>
                <span>最先端の研究について学べた</span>
            </label><br>
            <label>
                <input type="radio" name="good_point" value="3" <?php if (isset($old_input['good_point']) && $old_input['good_point'] === '3') echo 'checked'; ?>>
                <span>大学の研究者と対話ができた</span>
            </label><br>
            <label>
                <input type="radio" name="good_point" value="4" <?php if (isset($old_input['good_point']) && $old_input['good_point'] === '4') echo 'checked'; ?>>
                <span>大学の講義の雰囲気を味わえた</span>
            </label><br>
            <label>
                <input type="radio" name="good_point" value="5" <?php if (isset($old_input['good_point']) && $old_input['good_point'] === '5') echo 'checked'; ?>>
                <span>大学について知ることができた</span>
            </label><br>
            <label>
                <input type="radio" name="good_point" value="6" <?php if (isset($old_input['good_point']) && $old_input['good_point'] === '6') echo 'checked'; ?>>
                <span>身の回りの社会課題に対する解決のヒントが得られた</span>
            </label><br>
        </div>
        <label class="label_name" for="other_good_point">その他</label>
        <textarea name="other_good_point" row="20px"><?php echo htmlspecialchars($old_input['other_good_point'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        <?php if (!empty($errors['good_point'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($errors['good_point']); ?>
            </div>
        <?php endif; ?>

        <!-- 開催時間 (ラジオボタン) -->
        <label class="label_name" for="time">本日のプログラムの開催時間(〇〇分)について,あてはまるものを1つお選びください。</label>
        <div class="radio-group">
            <label>
                <input type="radio" name="time" value="1" <?php if (isset($old_input['time']) && $old_input['time'] === '1') echo 'checked'; ?>>
                <span>適当である</span>
            </label><br>
            <label>
                <input type="radio" name="time" value="2" <?php if (isset($old_input['time']) && $old_input['time'] === '2') echo 'checked'; ?>>
                <span>長すぎる</span>
            </label><br>
            <label>
                <input type="radio" name="time" value="3" <?php if (isset($old_input['time']) && $old_input['time'] === '3') echo 'checked'; ?>>
                <span>短すぎる</span>
            </label><br>
        </div>
        <?php if (!empty($errors['time'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($errors['time']); ?>
            </div>
        <?php endif; ?>

        <!-- 開催環境 (ラジオボタン) -->
        <label class="label_name" for="holding_enviroment">本日のプログラムの開催環境について、あてはまるものを1つお選びください<br>
            「あまり快適ではなかった」「全く快適ではなかった」と回答された方は次の質問にその理由を教えてください
        </label>
        <div class="radio-group">
            <label>
                <input type="radio" name="holding_enviroment" value="1" <?php if (isset($old_input['holding_enviroment']) && $old_input['holding_enviroment'] === '1') echo 'checked'; ?>>
                <span>とても快適だった</span>
            </label><br>
            <label>
                <input type="radio" name="holding_enviroment" value="2" <?php if (isset($old_input['holding_enviroment']) && $old_input['holding_enviroment'] === '2') echo 'checked'; ?>>
                <span>快適だった</span>
            </label><br>
            <label>
                <input type="radio" name="holding_enviroment" value="3" <?php if (isset($old_input['holding_enviroment']) && $old_input['holding_enviroment'] === '3') echo 'checked'; ?>>
                <span>ふつう</span>
            </label><br>
            <label>
                <input type="radio" name="holding_enviroment" value="4" <?php if (isset($old_input['holding_enviroment']) && $old_input['holding_enviroment'] === '4') echo 'checked'; ?>>
                <span>あまり快適ではなかった</span>
            </label><br>
            <label>
                <input type="radio" name="holding_enviroment" value="5" <?php if (isset($old_input['holding_enviroment']) && $old_input['holding_enviroment'] === '5') echo 'checked'; ?>>
                <span>全く快適ではなかった</span>
            </label><br>
        </div>
        <?php if (!empty($errors['holding_enviroment'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($errors['holding_enviroment']); ?>
            </div>
        <?php endif; ?>

        <!-- 理由 (テキストエリア) -->
        <label class="label_name" for="no_good_enviroment_reason">問9で「あまり快適ではなかった」「全く快適ではなかった」と回答された方はその理由を教えてください</label>
        <textarea name="no_good_enviroment_reason" row="20px"><?php echo htmlspecialchars($old_input['no_good_enviroment_reason'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

        <p class="divider" data-divider="以下、差し支えなければご回答ください。"></p>

        <!-- 職業 (ラジオボタン) ※ラジオボタンは checked 属性を使用 -->
        <label class="label_name" for="work">ご職業等を教えてください。</label>
        <div class="radio-group">
            <label>
                <input type="radio" name="work" value="1" <?php if (isset($old_input['work']) && $old_input['work'] === '1') echo 'checked'; ?>>
                <span>高校生以下</span>
            </label><br>
            <label>
                <input type="radio" name="work" value="2" <?php if (isset($old_input['work']) && $old_input['work'] === '2') echo 'checked'; ?>>
                <span>学生（高校生、大学生、大学院生等）</span>
            </label><br>
            <label>
                <input type="radio" name="work" value="3" <?php if (isset($old_input['work']) && $old_input['work'] === '3') echo 'checked'; ?>>
                <span>会社員</span>
            </label><br>
            <label>
                <input type="radio" name="work" value="4" <?php if (isset($old_input['work']) && $old_input['work'] === '4') echo 'checked'; ?>>
                <span>自営業・フリーランス</span>
            </label><br>
            <label>
                <input type="radio" name="work" value="5" <?php if (isset($old_input['work']) && $old_input['work'] === '5') echo 'checked'; ?>>
                <span>公務員</span>
            </label><br>
            <label>
                <input type="radio" name="work" value="6" <?php if (isset($old_input['work']) && $old_input['work'] === '6') echo 'checked'; ?>>
                <span>教職員</span>
            </label><br>
            <label>
                <input type="radio" name="work" value="7" <?php if (isset($old_input['work']) && $old_input['work'] === '7') echo 'checked'; ?>>
                <span>パート・アルバイト</span>
            </label><br>
            <label>
                <input type="radio" name="work" value="8" <?php if (isset($old_input['work']) && $old_input['work'] === '8') echo 'checked'; ?>>
                <span>主婦・主夫</span>
            </label><br>
            <label>
                <input type="radio" name="work" value="9" <?php if (isset($old_input['work']) && $old_input['work'] === '9') echo 'checked'; ?>>
                <span>定年退職</span>
            </label><br>
            <label>
                <input type="radio" name="work" value="10" <?php if (isset($old_input['work']) && $old_input['work'] === '10') echo 'checked'; ?>>
                <span>その他</span>
            </label><br>
        </div>
        <?php if (!empty($errors['work'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($errors['work']); ?>
            </div>
        <?php endif; ?>

        <!-- 性別 (ラジオボタン) -->
        <label class="label_name" for="sex">性別をご回答ください。</label>
        <div class="radio-group">
            <label>
                <input type="radio" name="sex" value="1" <?php if (isset($old_input['sex']) && $old_input['sex'] === '1') echo 'checked'; ?>>
                <span>女性</span>
            </label><br>
            <label>
                <input type="radio" name="sex" value="2" <?php if (isset($old_input['sex']) && $old_input['sex'] === '2') echo 'checked'; ?>>
                <span>男性</span>
            </label><br>
            <label>
                <input type="radio" name="sex" value="3" <?php if (isset($old_input['sex']) && $old_input['sex'] === '3') echo 'checked'; ?>>
                <span>その他</span>
            </label><br>
        </div>
        <?php if (!empty($errors['sex'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($errors['sex']); ?>
            </div>
        <?php endif; ?>

        <!-- お住まいの地域 -->
        <label class="label_name">お住まいの地域を教えてください。</label>
        <div class="address-container" style="display: flex; gap: 1rem; align-items: flex-start;">
            <div class="prefecture-field" style="flex: 0 0 200px;">
                <label for="prefecture">都道府県</label>
                <select name="prefecture" id="prefecture" style="width: 100%;">
                    <option value="">選択してください</option>
                    <option value="1" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '1') echo 'selected'; ?>>北海道</option>
                    <option value="2" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '2') echo 'selected'; ?>>青森県</option>
                    <option value="3" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '3') echo 'selected'; ?>>岩手県</option>
                    <option value="4" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '4') echo 'selected'; ?>>宮城県</option>
                    <option value="5" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '5') echo 'selected'; ?>>秋田県</option>
                    <option value="6" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '6') echo 'selected'; ?>>山形県</option>
                    <option value="7" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '7') echo 'selected'; ?>>福島県</option>
                    <option value="8" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '8') echo 'selected'; ?>>茨城県</option>
                    <option value="9" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '9') echo 'selected'; ?>>栃木県</option>
                    <option value="10" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '10') echo 'selected'; ?>>群馬県</option>
                    <option value="11" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '11') echo 'selected'; ?>>埼玉県</option>
                    <option value="12" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '12') echo 'selected'; ?>>千葉県</option>
                    <option value="13" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '13') echo 'selected'; ?>>東京都</option>
                    <option value="14" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '14') echo 'selected'; ?>>神奈川県</option>
                    <option value="15" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '15') echo 'selected'; ?>>新潟県</option>
                    <option value="16" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '16') echo 'selected'; ?>>富山県</option>
                    <option value="17" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '17') echo 'selected'; ?>>石川県</option>
                    <option value="18" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '18') echo 'selected'; ?>>福井県</option>
                    <option value="19" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '19') echo 'selected'; ?>>山梨県</option>
                    <option value="20" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '20') echo 'selected'; ?>>長野県</option>
                    <option value="21" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '21') echo 'selected'; ?>>岐阜県</option>
                    <option value="22" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '22') echo 'selected'; ?>>静岡県</option>
                    <option value="23" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '23') echo 'selected'; ?>>愛知県</option>
                    <option value="24" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '24') echo 'selected'; ?>>三重県</option>
                    <option value="25" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '25') echo 'selected'; ?>>滋賀県</option>
                    <option value="26" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '26') echo 'selected'; ?>>京都府</option>
                    <option value="27" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '27') echo 'selected'; ?>>大阪府</option>
                    <option value="28" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '28') echo 'selected'; ?>>兵庫県</option>
                    <option value="29" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '29') echo 'selected'; ?>>奈良県</option>
                    <option value="30" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '30') echo 'selected'; ?>>和歌山県</option>
                    <option value="31" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '31') echo 'selected'; ?>>鳥取県</option>
                    <option value="32" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '32') echo 'selected'; ?>>島根県</option>
                    <option value="33" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '33') echo 'selected'; ?>>岡山県</option>
                    <option value="34" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '34') echo 'selected'; ?>>広島県</option>
                    <option value="35" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '35') echo 'selected'; ?>>山口県</option>
                    <option value="36" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '36') echo 'selected'; ?>>徳島県</option>
                    <option value="37" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '37') echo 'selected'; ?>>香川県</option>
                    <option value="38" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '38') echo 'selected'; ?>>愛媛県</option>
                    <option value="39" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '39') echo 'selected'; ?>>高知県</option>
                    <option value="40" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '40') echo 'selected'; ?>>福岡県</option>
                    <option value="41" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '41') echo 'selected'; ?>>佐賀県</option>
                    <option value="42" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '42') echo 'selected'; ?>>長崎県</option>
                    <option value="43" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '43') echo 'selected'; ?>>熊本県</option>
                    <option value="44" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '44') echo 'selected'; ?>>大分県</option>
                    <option value="45" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '45') echo 'selected'; ?>>宮崎県</option>
                    <option value="46" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '46') echo 'selected'; ?>>鹿児島県</option>
                    <option value="47" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === '47') echo 'selected'; ?>>沖縄県</option>
                </select>
            </div>
            <!-- 市区町村・番地 -->
            <div class="address-field" style="flex: 1;">
                <label for="address">市区町村・番地等</label>
                <input type="text" name="address" id="address" style="width: 50%;" value="<?php echo htmlspecialchars($old_input['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <?php if (!empty($errors['address'])): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($errors['address']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="submit_button" style="margin-top: 1rem;">
            <button type="submit">送信する</button>
        </div>
    </form>
</div>
</body>
<script>
    window.addEventListener('DOMContentLoaded', function() {
        // プライバシーポリシーのリンクをクリックした際にチェックボックスを有効化
        var policyLink = document.getElementById('policy_link');
        policyLink.addEventListener('click', function() {
            var policyCheckbox = document.getElementById('policy_agreement');
            policyCheckbox.disabled = false;
        });
    });
</script>

</html>