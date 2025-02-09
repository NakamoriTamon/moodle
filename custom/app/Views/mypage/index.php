<?php
require_once('/var/www/html/moodle/custom/app/Controllers/mypage/MypageController.php');
require_once('/var/www/html/moodle/custom/helpers/form_helpers.php');
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

$lastname = "";
$firstname = "";
$email = "";
$phone1 = "";
$lastname_kana = "";
$firstname_kana = "";
$city = "";
$note = "";
$guardian_kbn = "";
$guardian_firstname = "";
$guardian_lastname = "";
$guardian_firstname_kana = "";
$guardian_lastname_kana = "";
$guardian_email = "";
$notification_kbn = 1;
if (isloggedin() && isset($_SESSION['USER'])) {
    $lastname = $userData->lastname ?? '';
    $firstname = $userData->firstname ?? '';
    $email = $userData->email ?? '';
    $phone1 = $userData->phone1 ?? '';
    $lastname_kana = $userData->lastname_kana ?? '';
    $firstname_kana = $userData->firstname_kana ?? '';
    $birthday = $userData->birthday ?? '';
    if (!empty($birthday)) {
        $birthDate = DateTime::createFromFormat('Y-m-d H:i:s', $birthday);
        $today = new DateTime('today'); // 今日の日付
        $age = $birthDate->diff($today)->y; // 年齢を計算
    } else {
        $age = null; // 生年月日がない場合
    }
    $city = $userData->city ?? '';
    $note = $userData->note ?? '';
    $guardian_kbn = $userData->guardian_kbn ?? 0;
    $guardian_firstname = $userData->guardian_firstname ?? '';
    $guardian_lastname = $userData->guardian_lastname ?? '';
    $guardian_firstname_kana = $userData->guardian_firstname_kana ?? '';
    $guardian_lastname_kana = $userData->guardian_lastname_kana ?? '';
    $guardian_email = $userData->guardian_email ?? '';
    $notification_kbn = $userData->notification_kbn ?? 1;
}
// セッションからエラーメッセージを取得
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];

unset($_SESSION['errors'], $_SESSION['old_input']); // 一度表示したら削除
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link class="js-stylesheet" href="/custom/admin/public/css/light.css" rel="stylesheet">
    <link class="js-stylesheet" href="/custom/admin/public/css/style.css" rel="stylesheet">
    <link class="js-stylesheet" href="/custom/admin/public/css/custom_light.css" rel="stylesheet">
    <link rel="stylesheet" href="/custom/public/css/style.css" type="text/css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/l10n/ja.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <title>マイページ</title>
</head>

<!-- スタイルは完全仮の状態なのでとりえず直書きする 後で個別ファイルに記述する -->
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
        /* 左寄せに変更 */
        padding: 8px;
    }

    th {
        background-color: #f2f2f2;
        /* ヘッダー部分に背景色を追加 */
        width: 30%;
    }

    td {
        width: 70%;
    }

    .table_area {
        margin: 120px auto auto auto;
        width: 60%;
    }

    input,textarea {
        width: 90%;
        padding: .5rem;
    }

    input[type="checkbox" i] {
        width: fit-content;
        padding: .5rem;
        margin-right: 10px;
    }

    .input_area {
        display: flex;
        justify-content: left;
    }

    .input-name-last {
        width: 40%;
        padding: .5rem;
        margin-right: 20px
    }

    .input-name-first {
        width: 40%;
        padding: .5rem;
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
</style>

<body>
    <header>
        <p>大阪大学 動画プラットフォーム</p>
        <?php if ($_SESSION['USER']->id == 0) {  ?>
            <button class="login-button mypage_button">会員登録</button>
            <button class="login-button" onclick="window.location.href='/login/index.php'">ログイン</button>
        <?php } else { ?>
            <button class="login-button mypage_button" onclick="window.location.href='/custom/app/Views/mypage/index.php'">マイページ</button>
            <button class="login-button" onclick="window.location.href='/login/logout.php'">ログアウト</button>
        <?php } ?>
    </header>

    <div class="table_area">
        <h2>知の広場　会員情報</h2>
        <form action="/custom/app/Controllers/mypage/MypageUpdateController.php" method="post" enctype="multipart/form-data">
            <input type="hidden" id="change_password" name="change_password" value="0">
            <input type="hidden" id="guardian_kbn" name="guardian_kbn" value="<?= htmlspecialchars($guardian_kbn) ?>">
            <input type="hidden" id="age" name="age" value="<?= htmlspecialchars($age) ?>">
            <table>
                <tr>
                    <th>ユーザID</th>
                    <td><?=  $_SESSION['USER']->id ?></td>
                </tr>
                <tr >
                    <th>氏名</th>
                    <td>
                        <div class="input_area">
                            <input type="text" name="lastname" class="input-name-last" placeholder="苗字" value="<?= htmlspecialchars(isSetValue($lastname, $old_input['lastname'] ?? '')) ?>">
                            <input type="text" name="firstname" class="input-name-first" placeholder="名前" value="<?= htmlspecialchars(isSetValue($firstname, $old_input['firstname'] ?? '')) ?>">
                        </div>
                        <div class="error-msg">
                            <?php if (!empty($errors['lastname'])): ?>
                                <?= htmlspecialchars($errors['lastname']); ?>
                            <?php endif; ?>
                            <?php if (!empty($errors['firstname'])): ?>
                                <?= htmlspecialchars($errors['firstname']); ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>フリガナ</th>
                    <td>
                        <div class="input_area">
                            <input type="text" name="lastname_kana" class="input-name-last" placeholder="苗字フリガナ" value="<?= htmlspecialchars(isSetValue($lastname_kana, $old_input['lastname_kana'] ?? '')) ?>">
                            <input type="text" name="firstname_kana" class="input-name-first" placeholder="名前フリガナ" value="<?= htmlspecialchars(isSetValue($firstname_kana, $old_input['firstname_kana'] ?? '')) ?>">
                        </div>
                        <div class="error-msg">
                            <?php if (!empty($errors['lastname_kana'])): ?>
                                <?= htmlspecialchars($errors['lastname_kana']); ?>
                            <?php endif; ?>
                            <?php if (!empty($errors['firstname_kana'])): ?>
                                <?= htmlspecialchars($errors['firstname_kana']); ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>生年月日</th>
                    <td>
                        <input type="date" name="birthday" value="<?= htmlspecialchars(isSetDate($birthday, $old_input['birthday'] ?? '')) ?>">
                        <?php if (!empty($errors['birthday'])): ?>
                            <div class="error-msg"><?= htmlspecialchars($errors['birthday']); ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>都道府県</th>
                    <td>
                        <select name="city">
                            <?php foreach($prefectures as $key => $value): ?>
								<option value="<?= htmlspecialchars($key) ?>" <?= isSelected($value, $city ?? null, $old_input['city'] ?? null) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($value) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                        <?php if (!empty($errors['city'])): ?>
                            <div class="error-msg"><?= htmlspecialchars($errors['city']); ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>メールアドレス</th>
                    <td>
                        <input type="text" name="email" value="<?= htmlspecialchars(isSetValue($email ?? '', $old_input['email'] ?? '')) ?>">
                        <?php if (!empty($errors['email'])): ?>
                            <div class="error-msg"><?= htmlspecialchars($errors['email']); ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>電話番号</th>
                    <td>
                        <input type="text" name="phone" value="<?= htmlspecialchars(isSetValue($phone1 ?? '', $old_input['phone'] ?? '')) ?>">
                        <?php if (!empty($errors['phone'])): ?>
                            <div class="error-msg"><?= htmlspecialchars($errors['phone']); ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>ハスワード</th>
                    <td>
                        <input type="password" id="password" name="password" value="<?= !empty($errors['password']) ? '' : '●●●●●●'?>">
                        <?php if (!empty($errors['password'])): ?>
                            <div class="error-msg"><?= htmlspecialchars($errors['password']); ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="note"><?= htmlspecialchars($note ?? ($old_input['note'] ?? '')) ?></textarea>
                        <?php if (!empty($errors['note'])): ?>
                            <div class="error-msg"><?= htmlspecialchars($errors['note']); ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- <tr id="guardian_kbn">
                    <td colspan="2">
                        <div class="input_area">
                            <input type="checkbox" id="guardian_kbn" name="guardian_kbn" class="checkbox" value="1" <?php if(!empty($guardian_kbn)): ?>checked<?php endif; ?>><label>13歳以下の方は会員登録に保護者の許可を得ています</label>
                            <?php if (!empty($errors['guardian_email'])): ?>
                                <div class="error-msg"><?= htmlspecialchars($errors['guardian_email']); ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr> -->
                <tr id="guardian_name" <?php if($age > 13): ?>style="display: none"<?php endif; ?>>
                    <th>保護者氏名</th>
                    <td>
                        <div class="input_area">
                            <input type="text" name="guardian_lastname" class="input-name-last" placeholder="保護者の苗字" value="<?= htmlspecialchars(isSetValue($guardian_lastname, $old_input['guardian_lastname'] ?? '')) ?>">
                            <input type="text" name="guardian_firstname" class="input-name-first" placeholder="保護者の名前" value="<?= htmlspecialchars(isSetValue($guardian_firstname, $old_input['guardian_firstname'] ?? '')) ?>">
                        </div>
                        <div class="error-msg">
                            <?php if (!empty($errors['guardian_lastname'])): ?>
                                <?= htmlspecialchars($errors['guardian_lastname']); ?>
                            <?php endif; ?>
                            <?php if (!empty($errors['guardian_firstname'])): ?>
                                <?= htmlspecialchars($errors['guardian_firstname']); ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <tr id="guardian_name_kana" <?php if($age > 13): ?>style="display: none"<?php endif; ?>>
                    <th>保護者氏名フリガナ</th>
                    <td>
                        <div class="input_area">
                            <input type="text" name="guardian_lastname_kana" class="input-name-last" placeholder="保護者の苗字フリガナ" value="<?= htmlspecialchars(isSetValue($guardian_lastname_kana, $old_input['guardian_lastname_kana'] ?? '')) ?>">
                            <input type="text" name="guardian_firstname_kana" class="input-name-first" placeholder="保護者の名前フリガナ" value="<?= htmlspecialchars(isSetValue($guardian_firstname_kana, $old_input['guardian_firstname_kana'] ?? '')) ?>">
                        </div>
                        <div class="error-msg">
                            <?php if (!empty($errors['guardian_lastname_kana'])): ?>
                                <?= htmlspecialchars($errors['guardian_lastname_kana']); ?>
                            <?php endif; ?>
                            <?php if (!empty($errors['guardian_firstname_kana'])): ?>
                                <?= htmlspecialchars($errors['guardian_firstname_kana']); ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <tr id="guardian_mail" <?php if($age > 13): ?>style="display: none"<?php endif; ?>>
                    <th>保護者のメールアドレス</th>
                    <td>
                        <input type="text" name="guardian_email" placeholder="保護者のメールアドレス" value="<?= htmlspecialchars(isSetValue($guardian_email ?? '', $old_input['guardian_email'] ?? '')) ?>">
                        <?php if (!empty($errors['guardian_email'])): ?>
                            <div class="error-msg"><?= htmlspecialchars($errors['guardian_email']); ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <div class="submit_button">
                <button type="submit">編集する</button>
            </div>
        </form>

        <h2>予約情報</h2>
        <table>
            <?php foreach($eventApplicationList as $row):
                $event_date = new DateTime($row['event_date']);
                $event_date = $event_date->format('Y/m/d');
            ?>
            <tr>
                <th><?= htmlspecialchars($event_date) ?></th>
                <td>
                    <div>
                        <?= htmlspecialchars($row['event_name']) ?>
                    </div>
                    <div>
                        【受講料】<?= htmlspecialchars(number_format($row['price'])) ?>円 【購入枚数】<?= htmlspecialchars($row['ticket_count']) ?>枚 【決済】<?php if(empty($row['payment_date'])): ?>未決済<?php else: ?>決済済<?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>イベント履歴</h2>
        <table>
            <?php foreach($oldEventApplicationList as $row):
                $event_date = new DateTime($row['event_date']);
                $event_date = $event_date->format('Y/m/d');
            ?>
                <tr>
                    <th><?= htmlspecialchars($event_date) ?></th>
                    
                <td>
                    <div>
                        <?= htmlspecialchars($row['event_name']) ?>
                    </div>
                    <div>
                        【受講料】<?= htmlspecialchars(number_format($row['price'])) ?>円 【購入枚数】<?= htmlspecialchars($row['ticket_count']) ?>枚
                    </div>
                </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>お知らせメール設定</h2>
        <table>
            <tr>
                <td colspan="2">
                    <p>ご登録いただいたアドレス宛にイベントの最新情報やメールマガジンをお送りいたします。</br>こちらで受信の設定が可能です。不要な方はチェックを外してください。</p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="input_area">
                        <input type="checkbox" id="notification_kbn" name="notification_kbn" class="checkbox" value="1" <?php if(!empty($notification_kbn)): ?>checked<?php endif; ?>><label>受け取る</label>
                        <?php if (!empty($errors['guardian_email'])): ?>
                            <div class="error-msg"><?= htmlspecialchars($errors['guardian_email']); ?></div>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>

        <!-- <h2 style="text-align: center; margin-top: 5rem">会員証</h2>
        <div class="card_area">
            <div class="card">
                <div class="name"><?php echo $_SESSION['USER']->lastname . ' ' . $_SESSION['USER']->firstname;  ?></div>
                <div class="sub-text">会員番号: 121 1235 1234</div>
            </div>
        </div> -->
    </div>



</body>
<script>
    const passwordInput = document.getElementById('password');
    const changePasswordFlag = document.getElementById('change_password');
    const maskedValue = '●●●●●●';

    // フォーカスイン時：マスクされた値なら空にする
    passwordInput.addEventListener('focus', () => {
        if (passwordInput.value === maskedValue) {
            passwordInput.value = '';
        }
    });

    // フォーカスアウト時：入力が空ならマスクを再設定
    passwordInput.addEventListener('blur', () => {
        if (passwordInput.value === '') {
            passwordInput.value = maskedValue;
            changePasswordFlag.value = '0';  // パスワード変更フラグをリセット
        }
    });

    // 入力時：パスワードが入力されたらフラグを「1」に変更
    passwordInput.addEventListener('input', () => {
        if (passwordInput.value !== '') {
            changePasswordFlag.value = '1';
        }
    });
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
            disable: [
                function(date) {
                    return (date.getMinutes() % 15 !== 0);
                }
            ]
        });
    });
</script>

</html>