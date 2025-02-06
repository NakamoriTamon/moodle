<?php
require_once('/var/www/html/moodle/config.php');

$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

require_once('/var/www/html/moodle/custom/app/Controllers/FrontController.php');

$eventId = $_GET['id'];
$frontController = new FrontController();
$responce = $frontController->index($eventId);

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/custom/public/css/style.css" type="text/css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>ユーザー登録</title>
</head>

<style>
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0px;
        max-width: 1000px;
    }

    th,
    td {
        border: 1px solid black;
        text-align: left;
        padding: 8px;
    }

    th {
        background-color: #f2f2f2;
        width: 30%;
    }

    td {
        width: 70%;
    }

    .table_area {
        margin: 120px auto auto auto;
        width: 60%;
    }

    input {
        width: 90%;
        padding: .5rem;
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

    .card {
        width: 300px;
        height: 150px;
        background-image: linear-gradient(-225deg, #2CD8D5 0%, #C5C1FF 56%, #FFBAC3 100%);
        border: 1px solid #0aa6cbad;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        font-family: 'Arial', sans-serif;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }

    .card:hover {
        transform: translateY(4px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .name {
        font-size: 24px;
        font-weight: bold;
        color: #ffffff;
    }

    .sub-text {
        font-size: 13px;
        color: #ffffff;
        margin-top: 8px;
    }

    .card_area {
        display: flex;
        justify-content: center;
    }

    .danger_text {
        background-color: red;
        color: white;
    }

    .required {
        color: red;
        font-weight: bold;
    }

    .guardian-info,
    .guardian-consent {
        display: none;
    }

    input[type="checkbox"] {
        width: auto;
        display: inline-block;
    }

    .guardian-consent label {
        display: inline-flex;
        align-items: center;
        gap: 0.5em;
    }

    /* エラー表示用 */
    .text-danger {
        color: red;
        font-size: 0.9em;
    }

    .mt-2 {
        margin-top: 0.5rem;
    }
</style>

<body>
    <header>
        <?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
    </header>

    <div class="table_area">
        <form action="/custom/app/Controllers/signup/signup_insert_controller.php" method="POST" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <table>
                <!-- お名前 -->
                <tr>
                    <th>お名前 <span class="required">（必須）</span></th>
                    <td>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($old_input['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (!empty($errors['name'])): ?>
                            <div class="text-danger mt-2">
                                <?= htmlspecialchars($errors['name']); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- フリガナ -->
                <tr>
                    <th>フリガナ <span class="required">（必須）</span></th>
                    <td>
                        <!-- 全角カタカナのみ許容 -->
                        <input type="text" name="kana" id="kana" pattern="^[ァ-ヶー]+$" title="全角カタカナで入力してください" value="<?php echo htmlspecialchars($old_input['kana'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (!empty($errors['kana'])): ?>
                            <div class="text-danger mt-2">
                                <?= htmlspecialchars($errors['kana']); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- お住まいの都道府県 -->
                <tr>
                    <th>お住まいの都道府県 <span class="required">（必須）</span></th>
                    <td>
                        <select name="prefecture">
                            <option value="">選択してください</option>
                            <!-- 都道府県の選択肢（以下、例） -->
                            <option value="hokkaido" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === 'hokkaido') echo 'selected'; ?>>北海道</option>
                            <option value="aomori" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === 'aomori') echo 'selected'; ?>>青森県</option>
                            <!-- 中略 -->
                            <option value="okinawa" <?php if (isset($old_input['prefecture']) && $old_input['prefecture'] === 'okinawa') echo 'selected'; ?>>沖縄県</option>
                        </select>
                        <?php if (!empty($errors['prefecture'])): ?>
                            <div class="text-danger mt-2">
                                <?= htmlspecialchars($errors['prefecture']); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- メールアドレス -->
                <tr>
                    <th>メールアドレス <span class="required">（必須）</span></th>
                    <td>
                        <input type="email" name="email" autocomplete="off" value="<?php echo htmlspecialchars($old_input['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (!empty($errors['email'])): ?>
                            <div class="text-danger mt-2">
                                <?= htmlspecialchars($errors['email']); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- メールアドレス（確認用） -->
                <tr>
                    <th>メールアドレス（確認用） <span class="required">（必須）</span></th>
                    <td>
                        <input type="email" name="email_confirm" id="email_confirm"
                            value="<?php echo htmlspecialchars($old_input['email_confirm'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (!empty($errors['email_confirm'])): ?>
                            <div class="text-danger mt-2">
                                <?= htmlspecialchars($errors['email_confirm']); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- パスワード -->
                <tr>
                    <th>パスワード <span class="required">（必須）</span></th>
                    <td>
                        <input type="password" name="password" id="password" autocomplete="new-password"
                            title="8文字以上20文字以内、数字、アルファベットを組み合わせてご入力ください。使用できる記号は !&quot;#$%&amp;'()*+,-./:;&lt;=&gt;?@[]\^_`{|}~ です">
                        <br>
                        <?php if (!empty($errors['password'])): ?>
                            <div class="text-danger mt-2">
                                <?= htmlspecialchars($errors['password']); ?>
                            </div>
                        <?php endif; ?>
                        <small class="input-hint">
                            ※ 8文字以上20文字以内、数字、アルファベットを組み合わせてご入力ください。<br>
                            ※ 使用できる記号は <code>!"#$%&amp;’()*+,-./:;&lt;=&gt;?@[\]^_&#96;{|}~</code> です。
                        </small>
                    </td>
                </tr>
                <!-- パスワード（確認用） -->
                <tr>
                    <th>パスワード（確認用） <span class="required">（必須）</span></th>
                    <td>
                        <input type="password" name="password_confirm" id="password_confirm">
                        <?php if (!empty($errors['password_confirm'])): ?>
                            <div class="text-danger mt-2">
                                <?= htmlspecialchars($errors['password_confirm']); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- 電話番号 -->
                <tr>
                    <th>電話番号（携帯番号もしくは自宅）</th>
                    <td>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($old_input['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </td>
                </tr>
                <!-- 生年月日 -->
                <tr>
                    <th>生年月日 <span class="required">（必須）</span></th>
                    <td>
                        <input type="date" name="birthdate" id="birthdate" value="<?php echo htmlspecialchars($old_input['birthdate'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (!empty($errors['birthdate'])): ?>
                            <div class="text-danger mt-2">
                                <?= htmlspecialchars($errors['birthdate']); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- 保護者情報（13歳未満の場合） -->
                <tr class="guardian-info">
                    <th>保護者のお名前 <span class="required">（必須）</span></th>
                    <td>
                        <input type="text" name="guardian_name" value="<?php echo htmlspecialchars($old_input['guardian_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (!empty($errors['guardian_name'])): ?>
                            <div class="text-danger mt-2">
                                <?= htmlspecialchars($errors['guardian_name']); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr class="guardian-info">
                    <th>保護者の連絡先 <span class="required">（必須）</span></th>
                    <td>
                        <input type="tel" name="guardian_contact" value="<?php echo htmlspecialchars($old_input['guardian_contact'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (!empty($errors['guardian_contact'])): ?>
                            <div class="text-danger mt-2">
                                <?= htmlspecialchars($errors['guardian_contact']); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- 保護者の同意チェック（13歳以上18歳未満の場合） -->
                <tr class="guardian-consent">
                    <th>保護者の同意 <span class="required">（必須）</span></th>
                    <td>
                        <label>
                            <input type="checkbox" name="guardian_consent" value="1" <?php echo isset($old_input['guardian_consent']) ? 'checked' : ''; ?>>
                            保護者が同意しました
                        </label>
                        <?php if (!empty($errors['guardian_consent'])): ?>
                            <div class="text-danger mt-2">
                                <?= htmlspecialchars($errors['guardian_consent']); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <div class="policy-agreement">
                <label for="policy_agreement" class="policy-label">
                    <span class="policy-text">
                        個人情報の提供について、大学での個人情報保護に関する<br>
                        <a href="/policy" target="_blank" id="policy_link">プライバシーポリシー</a>を確認し、同意します。
                    </span>
                    <input type="checkbox" id="policy_agreement" name="policy_agreement" value="1" <?php echo isset($old_input['policy_agreement']) ? 'checked' : ''; ?> disabled>同意する
                </label>
                <?php if (!empty($errors['policy_agreement'])): ?>
                    <div class="text-danger mt-2">
                        <?= htmlspecialchars($errors['policy_agreement']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit">この内容で仮登録する</button>
        </form>
    </div>

    <script>
        // --- 共通のコピー・ペースト等の無効化関数 ---
        function disableCopyPaste(event) {
            event.preventDefault();
            return false;
        }

        window.addEventListener('DOMContentLoaded', function() {
            // メールアドレス確認フィールドのイベント登録
            var emailConfirmField = document.getElementById('email_confirm');
            emailConfirmField.addEventListener('paste', disableCopyPaste);
            emailConfirmField.addEventListener('copy', disableCopyPaste);
            emailConfirmField.addEventListener('cut', disableCopyPaste);
            emailConfirmField.addEventListener('contextmenu', disableCopyPaste);

            // パスワード確認フィールドのイベント登録
            var passwordConfirmField = document.getElementById('password_confirm');
            passwordConfirmField.addEventListener('paste', disableCopyPaste);
            passwordConfirmField.addEventListener('copy', disableCopyPaste);
            passwordConfirmField.addEventListener('cut', disableCopyPaste);
            passwordConfirmField.addEventListener('contextmenu', disableCopyPaste);

            // フリガナ入力フィールドの取得
            var kanaField = document.getElementById('kana');

            kanaField.addEventListener('input', function() {
                // ひらがなが入力されている場合はカタカナに変換
                let converted = hiraganaToKatakana(this.value);
                // その後、カタカナと長音記号以外の文字を除去
                converted = converted.replace(/[^ァ-ヶー]/g, '');
                // 変換後の文字列をフィールドに反映
                this.value = converted;
            });

            // 生年月日変更イベントで年齢判定を実施
            var birthdateField = document.getElementById('birthdate');
            birthdateField.addEventListener('change', checkAge);

            // ここで、ページ読み込み時に生年月日フィールドに値があれば即時チェックを実施
            if (birthdateField.value) {
                checkAge();
            }

            // プライバシーポリシーのリンクをクリックした際にチェックボックスを有効化
            var policyLink = document.getElementById('policy_link');
            policyLink.addEventListener('click', function() {
                var policyCheckbox = document.getElementById('policy_agreement');
                // リンクをクリックしたらチェックボックスを有効化
                policyCheckbox.disabled = false;
            });
        });

        // --- 生年月日から年齢を計算する関数 ---
        function calculateAge(birthdate) {
            var today = new Date();
            var birth = new Date(birthdate);
            var age = today.getFullYear() - birth.getFullYear();
            var m = today.getMonth() - birth.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
                age--;
            }
            return age;
        }

        // --- 生年月日変更時の処理 ---
        function checkAge() {
            var birthdateField = document.getElementById('birthdate');
            var guardianInfoRows = document.querySelectorAll('.guardian-info');
            var guardianConsentRows = document.querySelectorAll('.guardian-consent');

            if (birthdateField.value) {
                var age = calculateAge(birthdateField.value);

                if (age < 13) {
                    // 13歳未満：保護者情報を表示、保護者同意チェックは非表示
                    guardianInfoRows.forEach(function(row) {
                        row.style.display = 'table-row';
                    });
                    guardianConsentRows.forEach(function(row) {
                        row.style.display = 'none';
                    });
                } else if (age >= 13 && age < 18) {
                    // 13歳以上18歳未満：保護者情報は非表示、保護者同意チェックを表示
                    guardianInfoRows.forEach(function(row) {
                        row.style.display = 'none';
                    });
                    guardianConsentRows.forEach(function(row) {
                        row.style.display = 'table-row';
                    });
                } else {
                    // 18歳以上：どちらも非表示
                    guardianInfoRows.forEach(function(row) {
                        row.style.display = 'none';
                    });
                    guardianConsentRows.forEach(function(row) {
                        row.style.display = 'none';
                    });
                }
            } else {
                guardianInfoRows.forEach(function(row) {
                    row.style.display = 'none';
                });
                guardianConsentRows.forEach(function(row) {
                    row.style.display = 'none';
                });
            }
        }
    </script>
</body>

</html>