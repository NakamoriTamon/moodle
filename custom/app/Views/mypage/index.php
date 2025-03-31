<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Controllers/mypage/mypage_controller.php');
require_once('/var/www/html/moodle/custom/app/Models/TekijukuCommemorationModel.php');
// ページネート表示数
$perPage = 4;
// 予約情報　現在のページ数
$event_application_page = isset($_GET['event_application_page']) ? (int)$_GET['event_application_page'] : 1;
// 予約情報　ページネート取得位置位置
$event_application_offset = ($event_application_page - 1) * $perPage;

// イベント履歴　現在のページ数
$event_history_page = isset($_GET['event_history_page']) ? (int)$_GET['event_history_page'] : 1;
// イベント履歴　ページネート取得位置位置
$event_history_offset = ($event_history_page - 1) * $perPage;

$mypage_controller = new MypageController;
$user = $mypage_controller->getUser(); // ユーザーの情報を引っ張ってくる
$is_general_user = $mypage_controller->isGeneralUser($user->id);
if (!$is_general_user) {
    header('Location: /custom/app/Views/logout/index.php');
}

// $tekijuku_commemoration = $mypage_controller->getTekijukuCommemoration(); // 適塾の情報を引っ張ってくる
$tekijukuCommemorationModel = new TekijukuCommemorationModel();
$tekijuku_commemoration = $tekijukuCommemorationModel->getTekijukuUserByPaid($user->id); // 適塾の情報を引っ張ってくる
// 適塾表示フラグ
$is_disply_tekijuku_commemoration = false;
if ($tekijuku_commemoration !== false) {
    // 現在の日付を取得
    $current_date = new DateTime();
    $current_year = (int)$current_date->format('Y');
    $current_month = (int)$current_date->format('n');

    // 現在の年度を計算（4月1日を年度の始まりとする）
    $current_fiscal_year = $current_year;
    if ($current_month < 4) {
        $current_fiscal_year = $current_year - 1;
    }

    if (!empty($tekijuku_commemoration['paid_date'])) {
        // paid_dateが存在する場合
        $paid_date = new DateTime($tekijuku_commemoration['paid_date']);

        // 支払日の年度を計算
        $paid_year = (int)$paid_date->format('Y');
        $paid_month = (int)$paid_date->format('n');
        $paid_fiscal_year = $paid_year;
        if ($paid_month < 4) {
            $paid_fiscal_year = $paid_year - 1;
        }

        // 現在の年度と支払い年度が同じであればtrue
        if ($current_fiscal_year === $paid_fiscal_year) {
            $is_disply_tekijuku_commemoration = true;
        }
    }

    // is_deposit_フラグの確認（paid_dateの有無に関わらず確認）
    // 対象年度のカラムが存在し、かつ2031年度未満であるか確認
    if ($current_fiscal_year >= 2024 && $current_fiscal_year <= 2030) {
        // 該当年度のデポジットフラグを確認
        $deposit_column = "is_deposit_{$current_fiscal_year}";

        // 該当年度のデポジットフラグが存在し、値が'1'の場合に表示
        if (
            array_key_exists($deposit_column, $tekijuku_commemoration) &&
            $tekijuku_commemoration[$deposit_column] == '1'
        ) {
            $is_disply_tekijuku_commemoration = true;
        }
    }
}

$event_applications = $mypage_controller->getEventApplications($event_application_offset, $perPage, $event_application_page); // 予約情報を引っ張ってくる
$event_histories = $mypage_controller->getEventApplications($event_history_offset, $perPage, $event_history_page, 'histories'); // イベント履歴を引っ張ってくる
$user_id = sprintf('%08d', $user->id); // IDのゼロ埋め
$birthday = substr($user->birthday, 0, 10); // 生年月日を文字列化

$errors = $_SESSION['errors'] ?? []; // バリデーションエラー
$success = $_SESSION['message_success'] ?? [];
$tekijuku_success = $_SESSION['tekijuku_success'] ?? [];
$message_membership_success = $_SESSION['message_membership_success'];
$message_membership_error = $_SESSION['message_membership_error'] ?? [];
$currentDate = date('Y-m-d');
// 今は4/1で固定
$startDate = date('Y') . '-' . MEMBERSHIP_START_DATE;
if ($currentDate < $startDate) {
    // 4/1以前なら去年
    $currentYear = date('y') - 1;
} else {
    $currentYear = date('y');
}

// 決済状態を判定する関数
function determinePaymentStatus($tekijuku_commemoration, $current_fiscal_year)
{
    if ($tekijuku_commemoration === false) {
        return null; // 適塾記念会会員ではない
    }

    // デポジットフラグ
    $isDeposit = false;
    if ($current_fiscal_year >= 2024 && $current_fiscal_year <= 2030) {
        $deposit_column = "is_deposit_{$current_fiscal_year}";
        if (array_key_exists($deposit_column, $tekijuku_commemoration)) {
            $isDeposit = $tekijuku_commemoration[$deposit_column] == '1';
        }
    }

    // 支払日付の有効性チェック（現在の年度内かどうか）
    $hasPaidDate = false;
    if (!empty($tekijuku_commemoration['paid_date'])) {
        $paid_date = new DateTime($tekijuku_commemoration['paid_date']);

        // 支払日の年度を計算
        $paid_year = (int)$paid_date->format('Y');
        $paid_month = (int)$paid_date->format('n');
        $paid_fiscal_year = $paid_year;
        if ($paid_month < 4) {
            $paid_fiscal_year = $paid_year - 1;
        }

        // 現在の年度と支払い年度が同じであればtrue
        $hasPaidDate = ($current_fiscal_year === $paid_fiscal_year);
    }

    // 決済状態の判定
    if (($isDeposit || $hasPaidDate)) {
        return [
            'status' => 'completed',
            'label' => '決済済',
            'can_edit' => true
        ];
    } elseif (!$hasPaidDate && !$isDeposit && $tekijuku_commemoration['paid_status'] == PAID_STATUS['PROCESSING']) {
        return [
            'status' => 'in-progress',
            'label' => '決済中',
            'can_edit' => false
        ];
    } elseif (!$hasPaidDate && !$isDeposit) {
        return [
            'status' => 'unpaid',
            'label' => '未決済',
            'can_edit' => true
        ];
    }

    // 万が一どの条件にも当てはまらない場合のデフォルト
    return [
        'status' => 'in-progress',
        'label' => '決済中(デフォルト)',
        'can_edit' => false
    ];
}

// 決済状態を取得
$paymentStatus = determinePaymentStatus($tekijuku_commemoration, $current_fiscal_year);

// フォーム要素を無効化する属性文字列を生成
$disabledAttr = ($paymentStatus && !$paymentStatus['can_edit']) ? 'disabled' : '';
include('/var/www/html/moodle/custom/app/Views/common/header.php');
unset(
    $_SESSION['old_input'],
    $_SESSION['message_success'],
    $_SESSION['tekijuku_success'],
    $_SESSION['message_'],
    $_SESSION['message_membership_error'],
    $_SESSION['message_membership_success']
);
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/mypage.css" />
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />
<style>
    /* 決済情報フォーム無効化時のスタイル */
    .btn.disabled {
        background-color: #cccccc !important;
        cursor: not-allowed !important;
        opacity: 0.6 !important;
        pointer-events: none;
    }

    input[type="radio"]:disabled+label,
    input[type="checkbox"]:disabled+label {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* 非活性状態のフォーム要素のスタイル */
    .form_cont.disabled input,
    .form_cont.disabled select,
    .form_cont.disabled textarea {
        background-color: #f5f5f5;
        border-color: #ddd;
        color: #999;
        cursor: not-allowed;
    }

    /* 決済状態表示用 */
    .payment-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.85em;
        margin-left: 10px;
    }

    .payment-status.unpaid {
        background-color: #f8d7da;
        color: #721c24;
    }

    .payment-status.in-progress {
        background-color: #fff3cd;
        color: #856404;
    }

    .payment-status.completed {
        background-color: #d4edda;
        color: #155724;
    }
</style>
<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="MEMBER'S PAGE">マイページ</h2>
    </section>

    <!-- heading -->
    <section id="mypage" class="inner_l">
        <?php if ($is_disply_tekijuku_commemoration): ?>
            <div class="card-wrapper">
                <div id="card">
                    <p class="card_head">適塾記念会デジタル会員証</p>
                    <p class="card_year"><?php echo htmlspecialchars($currentYear); ?>年度の<br class="nopc" />本会会員ということを証明する</p>
                    <p class="card_name"><?php echo htmlspecialchars($tekijuku_commemoration['name'] ?? ''); ?></p>
                    <p class="card_id"><?php echo htmlspecialchars($tekijuku_commemoration['number'] ? sprintf('%08d', $tekijuku_commemoration['number']) : ''); ?></p>
                    <ul class="card_desc">
                        <li>・本会員証は他人への貸与や譲渡はできません。</li>
                        <li>・この会員証を提示すると適塾に何度でも参観できます。</li>
                    </ul>
                    <div class="card_pres">
                        <p class="card_pres_pos">適塾記念会会長</p>
                        <p class="card_pres_name">熊ノ郷 淳</p>
                    </div>
                </div>
                <?php if ((int)$tekijuku_commemoration['is_delete'] === TEKIJUKU_COMMEMORATION_IS_DELETE['INACTIVE']) : ?>
                    <div class="inactive-text">（退会済み）</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div id="user_form">
            <div id="form" class="mypage_cont">
                <h3 class="mypage_head">知の広場 会員情報</h3>
                <form method="POST" action="/custom/app/Controllers/mypage/mypage_update_controller.php" id='user_edit_form'>
                    <div class="whitebox form_cont">
                        <div class="inner_m">
                            <?php if (!empty($basic_error)) { ?><p class="error"> <?= $basic_error ?></p><?php } ?>
                            <?php if (!empty($success)) { ?><p id="main_success_message"> <?= $success ?></p><?php } ?>
                            <ul class="list">
                                <li class="list_item01">
                                    <p class="list_label">会員番号</p>
                                    <div class="list_field f_txt"><?php echo htmlspecialchars($user_id); ?></div>
                                </li>
                                <li class="list_item02 req">
                                    <p class="list_label">お名前</p>
                                    <div class="list_field f_txt">
                                        <input type="text" name="name" value="<?php echo htmlspecialchars($old_input['name'] ?? $user->name); ?>" />
                                        <?php if (!empty($errors['name'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['name']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <li class="list_item03 req">
                                    <p class="list_label">フリガナ</p>
                                    <div class="list_field f_txt">
                                        <input type="text" name="name_kana" value="<?php echo htmlspecialchars($old_input['name_kana'] ?? $user->name_kana); ?>" />
                                        <?php if (!empty($errors['name_kana'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['name_kana']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <li class="list_item04 req">
                                    <p class="list_label">お住いの都道府県</p>
                                    <div class="list_field f_select select">
                                        <select name="city">
                                            <?php foreach ($prefectures as $key => $prefecture): ?>
                                                <option value="<?php echo htmlspecialchars($key); ?>"
                                                    <?= ($key === ($old_input['city'] ?? $user->city ?? null)) ? 'selected' : '' ?>>
                                                    <?php echo htmlspecialchars($prefecture); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (!empty($errors['city'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['city']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <li class="list_item05 req">
                                    <p class="list_label">メールアドレス</p>
                                    <div class="list_field f_txt">
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($old_input['email'] ?? $user->email); ?>"
                                            inputmode="email"
                                            autocomplete="email"
                                            oninput="this.value = this.value.replace(/[^a-zA-Z0-9@._-]/g, '');">
                                        <?php if (!empty($errors['email'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['email']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <li class="list_item06">
                                    <p class="list_label">パスワード（変更時のみ入力）</p>
                                    <div class="list_field f_txt">
                                        <input type="password" name="password" />

                                        <?php if (!empty($errors['password'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['password']); ?></div>
                                        <?php endif; ?>

                                        <p class="note">
                                            8文字以上20文字以内、数字・アルファベットを組み合わせてご入力ください。
                                        </p>
                                        <p class="note">使用できる記号!"#$%'()*+,-./:;<=>?@[¥]^_{|}~</p>

                                    </div>
                                </li>
                                <li class="list_item07 req">
                                    <p class="list_label">電話番号（ハイフンなし）</p>
                                    <div class="list_field f_txt">
                                        <input type="text"
                                            maxlength="15"
                                            pattern="[0-9]*"
                                            inputmode="numeric"
                                            name="phone"
                                            value="<?php echo htmlspecialchars($old_input['phone'] ?? $user->phone1); ?>"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '');" />
                                        <?php if (!empty($errors['phone'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['phone']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <li class="list_item08">
                                    <p class="list_label">生年月日</p>
                                    <div class="list_field f_txt">
                                        <?php
                                        $birthday_raw = $old_input['birthday'] ?? $birthday;
                                        $birthday_date = DateTime::createFromFormat('Y-m-d', $birthday_raw);
                                        $birthday_formatted = $birthday_date ? $birthday_date->format('Y年n月j日') : '未設定';
                                        ?>

                                        <input type="hidden" name="birthday" value="<?php echo htmlspecialchars($birthday_raw); ?>">
                                        <p><?php echo htmlspecialchars($birthday_formatted); ?></p>
                                        <?php if (!empty($errors['birthday'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['birthday']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <li class="list_item09">
                                    <p class="list_label">お子様の氏名</p>
                                    <div class="list_field f_txt">
                                        <input type="text" name="child_name" value="<?php echo htmlspecialchars($old_input['child_name'] ?? $user->child_name); ?>" />
                                        <?php if (!empty($errors['child_name'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['child_name']); ?></div>
                                        <?php endif; ?>
                                        <p class="note">
                                            保護者が代理入力している場合記入してください。
                                        </p>
                                    </div>
                                </li>
                                <li class="list_item10 long_item">
                                    <p class="list_label">備考</p>
                                    <div class="list_field f_txtarea">
                                        <textarea name="description"><?php echo htmlspecialchars($old_input['description'] ?? $user->description); ?></textarea>
                                        <?php if (!empty($errors['description'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['description']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <div id="parents_input_area">
                                    <li class="list_item11 req">
                                        <p class="list_label">保護者の氏名</p>
                                        <div class="list_field f_txt">
                                            <input type="text" name="guardian_name" value="<?= htmlspecialchars($old_input['guardian_name'] ?? $user->guardian_name) ?>" />
                                            <?php if (!empty($errors['guardian_name'])): ?>
                                                <div class="error-msg mt-2">
                                                    <?= htmlspecialchars($errors['guardian_name']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item12 req">
                                        <p class="list_label">保護者連絡先</p>
                                        <div class="list_field f_txt">
                                            <input type="email" name="guardian_email" value="<?= htmlspecialchars($old_input['guardian_email'] ?? $user->guardian_email) ?>" />
                                            <?php if (!empty($errors['guardian_email'])): ?>
                                                <div class="error-msg mt-2">
                                                    <?= htmlspecialchars($errors['guardian_email']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                </div>
                                <div id="parents_check_area">
                                    <li class="list_item12 req">
                                        <div class="agree">
                                            <p class="agree_txt">
                                                この会員登録は保護者の同意を得ています 。
                                            </p>
                                            <label for="parent_agree">
                                                <input type="checkbox" name="parent_agree" id="parent_agree" <?= !empty($old_input['parent_agree']) ? "checked" : ''; ?> />同意する
                                            </label>
                                        </div>
                                    </li>
                                </div>
                            </ul>
                        </div>
                    </div>
                    <div class="form_btn">
                        <input type="hidden" name="post_kbn" value="update_user">
                        <a class="btn btn_red box_bottom_btn submit_btn" href="javascript:void(0);" id="user_form_button">変更を確定する</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($tekijuku_commemoration !== false && (!is_null($tekijuku_commemoration['paid_date']) || (int)$tekijuku_commemoration[$deposit_column] === 1)): ?>
            <div id="tekijuku_form">
                <div id="form" class="mypage_cont">
                    <h3 class="mypage_head">適塾記念会 会員情報
                        <?php if ((int)$tekijuku_commemoration['is_delete'] === TEKIJUKU_COMMEMORATION_IS_DELETE['INACTIVE']) : ?>
                            <div class="inactive-text">（退会済み）</div>
                        <?php endif; ?>
                    </h3>
                    <form method="POST" action="/custom/app/Controllers/mypage/mypage_update_controller.php" id="tekijuku_edit_form">
                        <input type="hidden" name="tekijuku_commemoration_id" value=<?php echo htmlspecialchars($tekijuku_commemoration['id']) ?>>
                        <div class="whitebox form_cont">
                            <div class="inner_m">
                                <?php if (!empty($basic_error)) { ?><p class="error"> <?= htmlspecialchars($basic_error) ?></p><?php } ?>
                                <?php if (!empty($tekijuku_success)) { ?><p id="main_success_message"> <?= htmlspecialchars($tekijuku_success) ?></p><?php } ?>
                                <ul class="list">
                                    <li class="list_item01">
                                        <p class="list_label">会員番号</p>
                                        <div class="list_field f_txt"><?php echo htmlspecialchars($tekijuku_commemoration['number'] ? sprintf('%08d', $tekijuku_commemoration['number']) : ''); ?></div>
                                    </li>
                                    <li class="list_item02 req">
                                        <p class="list_label">会員種別</p>
                                        <div class="list_field f_txt" id="type_code" data-type-code="<?= htmlspecialchars($tekijuku_commemoration['type_code']) ?>"><?php echo TYPE_CODE_LIST[$tekijuku_commemoration['type_code']] ?></div>
                                    </li>
                                    <li class="list_item03 req">
                                        <p class="list_label">お名前</p>
                                        <div class="list_field f_txt">
                                            <input type="text" name="tekijuku_name" value="<?= htmlspecialchars($old_input['tekijuku_name'] ?? $tekijuku_commemoration['name']); ?>">
                                            <?php if (!empty($errors['tekijuku_name'])): ?>
                                                <div class=" text-danger mt-2"><?= htmlspecialchars($errors['tekijuku_name']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item04 req">
                                        <p class="list_label">フリガナ</p>
                                        <div class="list_field f_txt">
                                            <input type="text" name="kana" value="<?= htmlspecialchars($old_input['kana'] ?? $tekijuku_commemoration['kana']) ?>">
                                            <?php if (!empty($errors['kana'])): ?>
                                                <div class=" text-danger mt-2"><?= htmlspecialchars($errors['kana']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item05 req">
                                        <p class="list_label">郵便番号（ハイフンなし）</p>
                                        <div class="list_field f_txt a">
                                            <div class="post_code">
                                                <input type="text" id="zip" name="post_code" maxlength="7" pattern="\d{7}"
                                                    value="<?= htmlspecialchars($old_input['post_code'] ?? $tekijuku_commemoration['post_code']) ?>"
                                                    pattern="[0-9]*" inputmode="numeric"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                                <button id="post_button" type="button" onclick="fetchAddress()">住所検索</button>
                                            </div>
                                            <?php if (!empty($errors['post_code'])): ?>
                                                <div class="text-danger mt-2"><?= htmlspecialchars($errors['post_code']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item06 req">
                                        <p class="list_label">住所</p>
                                        <div class="list_field f_txt">
                                            <input type="text" id="address" name="address" value="<?= htmlspecialchars($old_input['address'] ?? $tekijuku_commemoration['address']) ?>">
                                            <?php if (!empty($errors['address'])): ?>
                                                <div class=" text-danger mt-2"><?= htmlspecialchars($errors['address']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item07 req">
                                        <p class="list_label">電話番号（ハイフンなし）</p>
                                        <div class="list_field f_txt">
                                            <div class="phone-input">
                                                <input type="text" name="tell_number" maxlength="15"
                                                    value="<?= htmlspecialchars($old_input['tell_number'] ?? $tekijuku_commemoration['tell_number']) ?>"
                                                    pattern="[0-9]*" inputmode="numeric"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                                <?php if (!empty($errors['tell_number'])): ?>
                                                    <div class=" text-danger mt-2"><?= htmlspecialchars($errors['tell_number']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list_item08 req">
                                        <p class="list_label">メールアドレス</p>
                                        <div class="list_field f_txt">
                                            <input type="email" name="tekijuku_email" value="<?= htmlspecialchars($old_input['tekijuku_email'] ?? $tekijuku_commemoration['email']) ?>"
                                                inputmode="email"
                                                autocomplete="email"
                                                oninput="this.value = this.value.replace(/[^a-zA-Z0-9@._-]/g, '');">
                                            <?php if (!empty($errors['tekijuku_email'])): ?>
                                                <div class=" text-danger mt-2"><?= htmlspecialchars($errors['tekijuku_email']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item10">
                                        <p class="list_label">備考</p>
                                        <div class="list_field f_txt">
                                            <textarea name="note" rows="5"><?= htmlspecialchars($old_input['note'] ?? $tekijuku_commemoration['note'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                            <?php if (!empty($errors['note'])): ?>
                                                <div class=" text-danger mt-2"><?= htmlspecialchars($errors['note']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item11">
                                        <div class="list_field">
                                            <label class="checkbox_label">
                                                <input class="checkbox_input" type="checkbox" name="is_university_member" id="is_university_member" value="1" <?php echo ($old_input['is_university_member'] ?? $tekijuku_commemoration['is_university_member']) == '1' ? 'checked' : ''; ?>>
                                                <label class="checkbox_label" id="is_university_member_label" for="is_university_member">大阪大学教職員・学生の方はこちらにチェックしてください。</label>
                                            </label>
                                        </div>
                                    </li>
                                    <li class="list_item12 req" id="department_field">
                                        <p class="list_label">所属部局（学部・研究科）</p>
                                        <div class="list_field f_txt">
                                            <input type="text" name="department" value="<?= htmlspecialchars($old_input['department'] ?? $tekijuku_commemoration['department']); ?>">
                                            <?php if (!empty($errors['department'])): ?>
                                                <div class="text-danger mt-2"><?= htmlspecialchars($errors['department']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item13" id="major_field">
                                        <p class="list_label">講座/部課/専攻名</p>
                                        <div class="list_field f_txt">
                                            <input type="text" name="major" value="<?= htmlspecialchars($old_input['major'] ?? $tekijuku_commemoration['major']); ?>">
                                            <?php if (!empty($errors['major'])): ?>
                                                <div class="text-danger mt-2"><?= htmlspecialchars($errors['major']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item14 req" id="official_field">
                                        <p class="list_label">職名・学年</p>
                                        <div class="list_field f_txt">
                                            <input type="text" name="official" value="<?= htmlspecialchars($old_input['official'] ?? $tekijuku_commemoration['official']); ?>">
                                            <?php if (!empty($errors['official'])): ?>
                                                <div class="text-danger mt-2"><?= htmlspecialchars($errors['official']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item15">
                                        <div class="area name">
                                            <label class="checkbox_label" for="">
                                                <input type="hidden" name="is_published" value="0">
                                                <input class="checkbox_input" type="checkbox" name="is_published" value="1" <?php echo ($old_input['is_published'] ?? $tekijuku_commemoration['is_published']) == '1' ? 'checked' : ''; ?>>
                                                <label class="checkbox_label">氏名掲載を許可します</label>
                                            </label>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="form_btn">
                            <input type="hidden" name="post_kbn" value="update_membership">
                            <a class="btn btn_red box_bottom_btn submit_btn" href="javascript:void(0);" id="tekijuku_form_button">変更を確定する</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>


        <?php if ($tekijuku_commemoration !== false): ?>
            <div id="tekijuku_payment_form">
                <div id="form" class="mypage_cont">
                    <h3 class="mypage_head">
                        適塾記念会 決済情報
                        <!-- var_dump($paymentStatus) -->
                        <?php if ($paymentStatus):  ?>
                            <span class="payment-status <?php echo $paymentStatus['status']; ?>">
                                <?php echo htmlspecialchars($paymentStatus['label']); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ((int)$tekijuku_commemoration['is_delete'] === TEKIJUKU_COMMEMORATION_IS_DELETE['INACTIVE']) : ?>
                            <div class="inactive-text">（退会済み）</div>
                        <?php endif; ?>
                    </h3>

                    <!-- 決済状態データ (JavaScript用) -->
                    <div id="payment-status-data"
                        data-has-paid-date="<?php echo !empty($tekijuku_commemoration['paid_date']) ? '1' : '0'; ?>"
                        data-is-deposit="<?php echo (isset($deposit_column) && array_key_exists($deposit_column, $tekijuku_commemoration) && $tekijuku_commemoration[$deposit_column] == '1') ? '1' : '0'; ?>"
                        data-has-paid-status="<?php echo $tekijuku_commemoration['paid_status'] ?>"
                        style="display: none;"></div>

                    <form method="POST" action="/custom/app/Controllers/mypage/mypage_update_controller.php" id="tekijuku_payment_edit_form">
                        <input type="hidden" name="tekijuku_commemoration_id" value=<?php echo htmlspecialchars($tekijuku_commemoration['id']) ?>>
                        <input type="hidden" name="price" value=<?php echo htmlspecialchars($tekijuku_commemoration['price']) ?>>
                        <input type="hidden" name="paid_status" value=<?php echo htmlspecialchars($tekijuku_commemoration['paid_status']) ?>>
                        <div class="whitebox form_cont <?php echo $disabledAttr ? 'disabled' : ''; ?>">
                            <div class="inner_m">
                                <?php if (!empty($payment_error)) { ?><p class="error"> <?= htmlspecialchars($payment_error) ?></p><?php } ?>
                                <?php if (!empty($message_membership_error)) { ?><p class="error"> <?= htmlspecialchars($message_membership_error) ?></p><?php } ?>
                                <?php if (!empty($message_membership_success)) { ?><p id="main_success_message"> <?= htmlspecialchars($message_membership_success) ?></p><?php } ?>
                                <ul class="list">
                                    <li class="list_item01 req">
                                        <p class="list_label">支払方法</p>
                                        <div class="list_field f_txt radio-group">
                                            <?php foreach ($payment_select_list as $key => $value) { ?>
                                                <input class="radio_input" id="payment_method_<?= $key ?>"
                                                    style="vertical-align: middle;"
                                                    type="radio"
                                                    name="payment_method"
                                                    value="<?= $key ?>"
                                                    <?= $disabledAttr ?>
                                                    <?php
                                                    // デフォルトの選択
                                                    if ((isset($old_input['payment_method']) && !$old_input['payment_method'] && $key == 1) ||
                                                        isSelected($key, $old_input['payment_method'] ?? $tekijuku_commemoration['payment_method'], null)
                                                    ) {
                                                        echo 'checked';
                                                    }
                                                    ?> />
                                                <label for="payment_method_<?= $key ?>" class="radio_label"><?= $value ?></label>
                                            <?php } ?>
                                            <?php if (!empty($errors['payment_method'])): ?>
                                                <div class="text-danger mt-2"><?= htmlspecialchars($errors['payment_method']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item02 is_subscription_area" style="display: none;">
                                        <div class="area plan">
                                            <label class="checkbox_label" for="">
                                                <input type="hidden" name="is_subscription" value="0">
                                                <input class="checkbox_input"
                                                    id="payment_is_subscription_checkbox"
                                                    type="checkbox"
                                                    name="is_subscription"
                                                    value="1"
                                                    <?= $disabledAttr ?>
                                                    <?php echo ($old_input['is_subscription'] ?? $tekijuku_commemoration['is_subscription']) == '1' ? 'checked' : ''; ?>>
                                                <label class="checkbox_label" for="payment_is_subscription_checkbox">定額課金プランを利用する</label>
                                            </label>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="form_btn">
                            <input type="hidden" name="post_kbn" value="update_payment_method">
                            <a class="btn btn_red box_bottom_btn submit_btn <?php echo $disabledAttr ? 'disabled' : ''; ?>"
                                href="javascript:void(0);"
                                id="tekijuku_payment_button"
                                <?php echo $disabledAttr ? 'disabled' : ''; ?>>
                                決済情報を更新する
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="mypage_cont reserve">
            <h3 id="event_application" class="mypage_head">予約情報</h3>
            <?php $allCourseDateNull = true; ?>
            <?php if (!empty($event_applications['data'])): ?>
                <?php foreach ($event_applications['data'] as $application): ?>
                    <?php
                    if (is_null($application->course_date)) {
                        continue;
                    }
                    $allCourseDateNull = false;
                    $event_name = '【第' . $application->no . '回】' . $application->event_name;
                    $date = date('Y/m/d', strtotime($application->course_date));
                    $weekday = $weekdays[date('w', strtotime($date))];
                    $format_date = $date . " ($weekday)";
                    $package_types = '';
                    switch ($application->event_application_package_types) {
                        case EVENT_APPLICATION_PACKAGE_TYPE['SINGLE']:
                            $package_types = '';
                            break;
                        case EVENT_APPLICATION_PACKAGE_TYPE['BUNDLE']:
                            $package_types = '（一括申し込み）';
                            break;
                        default:
                            $package_types = '';
                            break;
                    }

                    $price = $application->price > 0 ? '￥' . number_format($application->price) . '円' . $package_types : '無料';
                    // QR表示判定
                    $qr_class = '';
                    if (($application->lecture_format_id == 1 && !empty($application->payment_date)) || $application->price == 0) {
                        $qr_class = 'js_pay';
                    }
                    ?>
                    <div class="info_wrap <?= $qr_class ?>">
                        <form action="/custom/app/Views/event/reserve.php" method="POST" class="info_wrap_cont">

                            <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($application->event_id) ?>">
                            <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($application->course_id) ?>">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($application->event_application_id) ?>">
                            <button type="submit" class="info_wrap_cont_btn">
                                <p class="date">
                                    <?php echo htmlspecialchars(date('Y/m/d', strtotime($application->course_date))); ?>
                                </p>
                                <div class="txt">
                                    <p class="txt_ttl">
                                        <?php echo htmlspecialchars('【第' . $application->no . '回】' . $application->event_name) ?>
                                    </p>
                                    <ul class="txt_other">
                                        <li>【会場】<span class="txt_other_place"><?php echo htmlspecialchars($application->venue_name) ?></span></li>
                                        <li>【受講料】<span class="txt_other_money"><?php echo htmlspecialchars($price) ?></span></li>
                                        <li>【購入枚数】<span class="txt_other_num"><?php echo htmlspecialchars($application->ticket_count) ?> 枚</span></li>
                                        <?php if ($application->price != 0) : ?>
                                            <li>【決済】<span class=" <?= htmlspecialchars(empty($application->payment_date) ? 'txt_other_pay' : '') ?>"><?= !empty($application->payment_date) ? '決済済' : '未決済' ?></span></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </button>
                        </form>
                        <a href="#" class="info_wrap_qr" data-event-application-course-info-id="<?= $application->encrypted_eaci_id ?>" data-name="<?= $event_name ?>" data-date="<?= $format_date ?>">
                            <object type="image/svg+xml" data="/custom/public/assets/common/img/icon_qr_pay.svg" class="obj obj_pay"></object>
                            <object type="image/svg+xml" data="/custom/public/assets/common/img/icon_qr.svg" class="obj obj_no"></object>
                            <p class="txt">デジタル<br class="nosp" />チケットを<br />表示する</p>
                        </a>
                    </div>
                <?php endforeach; ?>
                <div class="pagination">
                    <?php if ($event_applications['pagination']['current_page'] > 1): ?>
                        <a href="?event_application_page=<?php echo htmlspecialchars($event_applications['pagination']['current_page'] - 1) ?>&event_history_page=<?php echo htmlspecialchars($event_histories['pagination']['current_page']) ?>#event_application" class="prev">← 前へ</a>
                    <?php endif; ?>
                    <span class="page-info">Page <?php echo htmlspecialchars($event_applications['pagination']['current_page']); ?> / <?php echo htmlspecialchars($event_applications['pagination']['total_pages']); ?></span>
                    <?php if ($event_applications['pagination']['current_page'] < $event_applications['pagination']['total_pages']): ?>
                        <a href="?event_application_page=<?php echo htmlspecialchars($event_applications['pagination']['current_page'] + 1) ?>&event_history_page=<?php echo htmlspecialchars($event_histories['pagination']['current_page']) ?>#event_application" class="next">次へ →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($allCourseDateNull): ?>
            <div>現在お申込みされているイベントはございません。下記申し込みイベント一覧からお申込みください。</div>
        <?php endif; ?>
        <a href="/custom/app/Views/event/register.php" class="btn btn_blue box_bottom_btn arrow">申し込みイベント一覧</a>

        <div class="mypage_cont history">
            <h3 id="event_histories" class="mypage_head btn_acc">イベント履歴<span class="acc"></span></h3>
            <div class="acc_wrap">
                <?php $allHistoryCourseDateNull = true; ?>
                <?php if (!empty($event_histories['data'])): ?>
                    <?php foreach ($event_histories['data'] as $history): ?>
                        <?php
                        if (is_null($history->course_date)) {
                            continue;
                        }
                        $allHistoryCourseDateNull = false;
                        $history_price = $history->price > 0 ? '￥' . number_format($history->price) . '円' : '無料';
                        ?>

                        <div class="info_wrap js_pay">
                            <form action="/custom/app/Views/event/history.php" method="POST" class="info_wrap_cont">
                                <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($history->event_id) ?>">
                                <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($history->course_id) ?>">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($history->event_application_id) ?>">
                                <button type="submit" class="info_wrap_cont_btn">
                                    <p class="date">
                                        <?php echo htmlspecialchars(date('Y/m/d', strtotime($history->course_date))); ?>
                                    </p>
                                    <div class="txt">
                                        <p class="txt_ttl">
                                            <?php echo htmlspecialchars('【第' . $history->no . '回】' . $history->event_name) ?>
                                        </p>
                                        <ul class="txt_other">
                                            <li>【会場】<span class="txt_other_place"><?php echo htmlspecialchars($history->venue_name) ?></span></li>
                                            <li>【受講料】<span class="txt_other_money"><?php echo htmlspecialchars($history_price) ?></span></li>
                                        </ul>
                                    </div>
                                </button>
                            </form>
                        </div>

                    <?php endforeach; ?>

                    <div class="pagination">
                        <?php if ($event_histories['pagination']['current_page'] > 1): ?>
                            <a href="?event_application_page=<?php echo htmlspecialchars($event_applications['pagination']['current_page']) ?>&event_history_page=<?php echo htmlspecialchars($event_histories['pagination']['current_page'] - 1) ?>#event_histories" class="prev">← 前へ</a>
                        <?php endif; ?>
                        <span class="page-info">Page <?php echo htmlspecialchars($event_histories['pagination']['current_page']); ?> / <?php echo htmlspecialchars($event_histories['pagination']['total_pages']); ?></span>
                        <?php if ($event_histories['pagination']['current_page'] < $event_histories['pagination']['total_pages']): ?>
                            <a href="?event_application_page=<?php echo htmlspecialchars($event_applications['pagination']['current_page']) ?>&event_history_page=<?php echo htmlspecialchars($event_histories['pagination']['current_page'] + 1) ?>#event_histories" class="next">次へ →</a>
                        <?php endif; ?>
                    </div>
            </div>
        <?php endif; ?>
        </div>
        <?php if ($allHistoryCourseDateNull): ?>
            <div>現在までにお申込みされたイベントはございません。</div>
        <?php endif; ?>
        <div class="mypage_cont setting">
            <h3 class="mypage_head">お知らせメール設定</h3>
            <p class="sent">
                ご登録いただいたアドレス宛にイベントの最新情報やメールマガジンをお送りいたします。<br />
                こちらで受信の設定が可能です。不要な方はチェックを外してください。
            </p>
            <label class="set_check">
                <input type="checkbox" id="email-notifications" <?php echo ($user->notification_kbn == 1) ? 'checked' : ''; ?> /> 受け取る
            </label>
            <div id="notification-message" style="display:none;"></div>
            <a href="/custom/app/Views/logout/index.php" class="btn btn_red box_bottom_btn arrow">ログアウト</a>
        </div>
    </section>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>マイページ</li>
</ul>

<div id="modal" class="modal_ticket">
    <div class="modal_bg js_close"></div>
    <div class="modal_cont">
        <!-- <span class="cross js_close"></span> -->
        <p id="moodle_ticket_date" class="ticket_date">2025/00/00（金）</p>
        <p id="modal_event_name" class="ticket_ttl">中之島芸術センター 演劇公演<br />『中之島デリバティブⅢ』</p>
        <div id="qrcode" class="ticket_qr"><img id="qrImage" src="" alt="" /></div>
        <p class="ticket_txt">こちらの画面を受付でご提示ください。</p>
    </div>
</div>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>

<script>
    $(".info_wrap_qr").on("click", function(e) {
        e.preventDefault();
        if ($(this).parents('div').hasClass('js_pay')) {
            srlpos = $(window).scrollTop();
            $("#modal").fadeIn();
            $("body").addClass("modal_fix").css({
                top: -srlpos
            });
            const encrypted_eaci_id = $(this).data("event-application-course-info-id");
            const name = $(this).data("name");
            const date = $(this).data("date");

            $('#moodle_ticket_date').text(date);
            $('#modal_event_name').text(name);

            // QRコード画像をセット
            $("#qrImage").attr("src", "/custom/app/Views/event/qr_generator.php?eaci_id=" + encrypted_eaci_id);

            return false;
        }
    });

    $(".js_close").on("click", function() {
        $("#modal").fadeOut();
        $("body").removeClass("modal_fix").css({
            top: 0
        });
        $(window).scrollTop(srlpos);
    });

    async function fetchAddress() {
        const zip = document.getElementById("zip").value; // スペースを削除
        if (!/^\d{7}$/.test(zip)) {
            alert("7桁の数字を入力してください");
            return;
        }

        try {
            const response = await fetch(`https://zipcloud.ibsnet.co.jp/api/search?zipcode=${zip}`);
            const data = await response.json();
            if (data.status === 200 && data.results) {
                document.getElementById("address").value = `${data.results[0].address1} ${data.results[0].address2} ${data.results[0].address3}`;
            } else {
                alert("住所が見つかりませんでした");
            }
        } catch (error) {
            alert("エラーが発生しました");
        }
    }

    $(document).ready(function() {
        // 決済方法取得
        paymentMethod($('input[name="payment_method"]:checked').val());
        $('input[name="payment_method"]').on('change', function() {
            paymentMethod($(this).val());
        });

        function paymentMethod(val) {
            if (val === "2") {
                $('.is_subscription_area').css('display', 'block');
            } else {
                $('.is_subscription_area').css('display', 'none');
                $('#is_subscription_checkbox, #payment_is_subscription_checkbox').prop('checked', false);
            }
        }
    });

    function displayRange(birthdate) {
        $('#parents_input_area').css('display', 'none');
        $('#parents_check_area').css('display', 'none');
        if (birthdate) {
            const age = calculateAge(birthdate);
            if (age < 13) {
                $('#parents_input_area').css('display', 'block');
            } else if (age < 19) {
                $('#parents_check_area').css('display', 'block');
            }
        }
        // 同意チェック
        checkParentAgree();
    }

    $(document).ready(function() {
        $('input[name="birthday"]').on('change', function() {
            const birthday = $(this).val();
            displayRange(birthday);
        });

        // 年齢計算
        function calculateAge(birthday) {
            const birthdayObj = new Date(birthday);
            const today = new Date();

            let age = today.getFullYear() - birthdayObj.getFullYear();
            const monthDiff = today.getMonth() - birthdayObj.getMonth();
            const dayDiff = today.getDate() - birthdayObj.getDate();

            // 誕生日がまだ来ていない場合、年齢を1引く
            if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
                age--;
            }

            return age;
        }

        // 処理中フラグ
        let processing = false;
        displayRange($('input[name="birthday"]').val());

        function displayRange(birthday) {
            $('#parents_input_area').css('display', 'none');
            $('#parents_check_area').css('display', 'none');
            if (birthday) {
                const age = calculateAge(birthday);
                if (age < 13) {
                    $('#parents_input_area').css('display', 'block');
                } else if (age < 19) {
                    $('#parents_check_area').css('display', 'block');
                }
            }
            // 同意チェック
            checkParentAgree();
        }

        // 初回の確認
        checkAgree();
        $(document).on('change', '#agree', checkAgree);
        $(document).on('change', '#parent_agree', checkParentAgree);

        // 利用規約同意チェック
        function checkAgree() {
            if (processing) return; // 処理中は再実行しない

            // 利用規約の同意がチェックされている場合
            if ($('#agree').prop('checked')) {
                checkParentAgree();
            } else {
                $('#submit').prop('disabled', true);
            }
        }

        // 保護者の同意チェック
        function checkParentAgree() {
            if (processing) return;

            if ($('#parents_check_area').css('display') !== 'none') {
                // 両方がチェックされている場合にボタンを有効化
                if ($('#parent_agree').prop('checked')) {
                    $('.submit_btn').prop('disabled', false); // 両方チェックされている場合は有効化
                } else {
                    $('.submit_btn').prop('disabled', true);
                }
            } else {
                $('.submit_btn').prop('disabled', false);
            }
        }
    });

    $(document).ready(function() {
        $('#email-notifications').change(function() {
            var isChecked = $(this).is(':checked'); // チェックの状態を取得
            $.ajax({
                url: '/custom/app/Controllers/mypage/mypage_update_controller.php',
                method: 'POST',
                data: {
                    email_notification: isChecked ? 1 : 0,
                    post_kbn: 'email_notification'
                },
                success: function(response) {
                    // サーバーからのレスポンスに基づきフィードバック
                    $('#notification-message').text('設定が保存されました').show();
                },
                error: function(xhr, status, error) {
                    // エラー時の処理
                    $('#notification-message').text('設定の保存に失敗しました').show();
                }
            });
        });
    });


    $(document).ready(function() {
        // 退会ボタンクリック時
        var exec = '';
        $('#user_form_button').on('click', function() {
            exec = 'user';
            showModal('知の広場 会員情報', '変更を確定してもよろしいですか？');
        });
        $('#tekijuku_form_button').on('click', function() {
            exec = 'tekijuku';
            showModal('適塾記念会 会員情報 ', '変更を確定してもよろしいですか？');
        });

        // 決済情報更新ボタンクリック時の処理を追加
        $('#tekijuku_payment_button').on('click', function() {
            exec = 'payment';
            showModal('適塾記念会 決済情報', '決済情報を更新してもよろしいですか？');
        });

        $(document).on('click', '.edit', function() {
            switch (exec) {
                case "user":
                    $('#user_edit_form').submit();
                    break
                case "tekijuku":
                    $('#tekijuku_edit_form').submit();
                    break
                case "payment":
                    $('#tekijuku_payment_edit_form').submit();
                    break
                default:
                    break
            }
        });
    });

    // モーダル表示
    function showModal(title, message) {
        var modalHtml = `
        <div id="confirmation-modal">
            <div class="modal_cont">
                <h2>${title}</h2>
                <p>${message}</p>
                <div class="modal-buttons">
                    <button class="modal-withdrawal edit">確定する</button>
                    <button class="modal-close">いいえ</button>
                </div>
            </div>
        </div>
    `;
        $('body').append(modalHtml);
        $('#confirmation-modal').show();
    }

    // モーダルの閉じるボタン
    $(document).on('click', '.modal-close', function() {
        $('#confirmation-modal').remove();
    });

    // アコーディオン
    $(function() {
        $(function() {
            // URLのハッシュ部分を取得
            const hash = window.location.hash;

            // URLのハッシュに#event_historiesが含まれていればアコーディオンを開く
            if (hash === '#event_histories') {
                $(".btn_acc").addClass("js-open");
                $(".acc_wrap").show();
            }

            // アコーディオンのトグル
            $(".btn_acc").click(function() {
                $(".acc_wrap").slideToggle();
                $(this).toggleClass("js-open");
            });
        });
    });

    //**
    // 大阪大学教職員・学生の方はこちらにチェックしてください。のチェックボックス動き
    //  */
    document.addEventListener("DOMContentLoaded", function() {
        const checkbox = document.querySelector('input[name="is_university_member"]');
        const fields = document.querySelectorAll("#department_field, #major_field, #official_field");

        function toggleFields() {
            fields.forEach(field => {
                if (checkbox.checked) {
                    field.classList.remove("hidden");
                } else {
                    field.classList.add("hidden");
                    // 入力値をクリア
                    field.querySelector("input").value = "";
                }
            });
        }

        // 初期状態を設定
        toggleFields();

        // チェック状態が変更されたら切り替え
        checkbox.addEventListener("change", toggleFields);
    });

    document.addEventListener('DOMContentLoaded', function() {
        /**
         * 適塾記念会 決済情報の制御処理
         */
        function initPaymentFormControl() {
            // 決済状況の判定
            const statusData = document.getElementById('payment-status-data');
            if (!statusData) return;

            const status = {
                hasPaidDate: statusData.dataset.hasPaidDate === '1',
                isDeposit: statusData.dataset.isDeposit === '1',
                hasPaidStatus: statusData.dataset.hasPaidStatus
            };

            const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
            const subscriptionCheckbox = document.getElementById('payment_is_subscription_checkbox');
            const updateButton = document.getElementById('tekijuku_payment_button');

            // 決済状況に応じた制御
            if (status.isDeposit || status.hasPaidDate) {
                // 決済済: 基本非活性
                disableFormElements(paymentMethodRadios, null, null);
            } else if (!status.hasPaidDate && !status.isDeposit && status.hasPaidStatus == 2) {
                // 決済中: 全て非活性
                disableFormElements(paymentMethodRadios, subscriptionCheckbox, updateButton);
            } else if (!status.hasPaidDate && !status.isDeposit && status.hasPaidStatus == 1) {
                // 未決済: 支払い方法選択で活性化
                enablePaymentMethodSelection(paymentMethodRadios, subscriptionCheckbox, updateButton);
            }

            // 初期表示時のサブスク選択表示制御
            updateSubscriptionVisibility(paymentMethodRadios);
        }

        // フォーム要素を無効化
        function disableFormElements(radios, checkbox, button) {
            if (radios) {
                radios.forEach(radio => {
                    radio.disabled = true;
                });
            }

            if (checkbox) {
                checkbox.disabled = true;
            }

            if (button) {
                button.disabled = true;
                button.classList.add('disabled');
            }
        }

        // サブスク選択の表示/非表示を更新
        function updateSubscriptionVisibility(radios) {
            const subscriptionArea = document.querySelector('.is_subscription_area');
            if (!subscriptionArea) return;

            let isCreditCard = false;
            radios.forEach(radio => {
                if (radio.checked && radio.value === "2") {
                    isCreditCard = true;
                }
            });

            subscriptionArea.style.display = isCreditCard ? 'block' : 'none';
        }

        // 未決済時の支払い方法選択制御
        function enablePaymentMethodSelection(radios, checkbox, button) {
            // 支払い方法ラジオボタンを有効化
            if (radios) {
                radios.forEach(radio => {
                    radio.disabled = false;

                    // 支払い方法変更イベント
                    radio.addEventListener('change', function() {
                        if (button) {
                            button.disabled = false;
                            button.classList.remove('disabled');
                        }

                        // サブスク選択の表示/非表示を更新
                        updateSubscriptionVisibility(radios);

                        // 支払い方法がクレジットカード(value="2")の場合、サブスク選択を有効化
                        if (checkbox) {
                            if (this.value === "2") {
                                checkbox.disabled = false;
                            } else {
                                checkbox.disabled = true;
                                checkbox.checked = false;
                            }
                        }
                    });
                });
            }

            // 最初はボタン無効
            if (button) {
                button.disabled = true;
                button.classList.add('disabled');
            }

            // 支払い方法が選択されているか確認
            const isPaymentMethodSelected = radios && Array.from(radios).some(radio => radio.checked);

            // 支払い方法選択でボタン活性化
            if (isPaymentMethodSelected && button) {
                button.disabled = false;
                button.classList.remove('disabled');
            }

            // サブスク選択の初期状態設定
            if (checkbox) {
                const isCreditCardSelected = radios && Array.from(radios).some(radio => radio.checked && radio.value === "2");
                checkbox.disabled = !isCreditCardSelected;
                if (!isCreditCardSelected) {
                    checkbox.checked = false;
                }
            }
        }

        // サブミット前の検証
        function validatePaymentForm() {
            const form = document.getElementById('tekijuku_payment_edit_form');
            if (!form) return;

            form.addEventListener('submit', function(e) {
                const statusData = document.getElementById('payment-status-data');
                if (!statusData) return;

                const status = {
                    hasPaidDate: statusData.dataset.hasPaidDate === '1',
                    isDeposit: statusData.dataset.isDeposit === '1',
                    hasPaidStatus: statusData.dataset.hasPaidStatus
                };

                // 決済済または決済中の場合は送信をキャンセル
                if (status.isDeposit || (status.hasPaidDate && status.hasPaidStatus == 2) ||
                    (!status.hasPaidDate && !status.isDeposit && status.hasPaidStatus == 3)) {
                    e.preventDefault();
                    alert('現在の決済状態では変更できません。');
                    return false;
                }

                // 支払い方法が選択されているか確認
                const paymentMethodSelected = Array.from(document.querySelectorAll('input[name="payment_method"]'))
                    .some(radio => radio.checked);

                if (!paymentMethodSelected) {
                    e.preventDefault();
                    alert('支払い方法を選択してください。');
                    return false;
                }

                return true;
            });
        }

        // 初期化
        initPaymentFormControl();
        validatePaymentForm();

        // 決済方法による表示切替を既存コードから置き換え
        $('input[name="payment_method"]').on('change', function() {
            const value = $(this).val();
            if (value === "2") {
                $('.is_subscription_area').css('display', 'block');
            } else {
                $('.is_subscription_area').css('display', 'none');
                $('#payment_is_subscription_checkbox').prop('checked', false);
            }
        });
    });
</script>