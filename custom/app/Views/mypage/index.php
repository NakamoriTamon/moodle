<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Controllers/mypage/mypage_controller.php');

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
$tekijuku_commemoration = $mypage_controller->getTekijukuCommemoration(); // 適塾の情報を引っ張ってくる
$event_applications = $mypage_controller->getEventApplications($event_application_offset, $perPage, $event_application_page); // 予約情報を引っ張ってくる
$event_histories = $mypage_controller->getEventApplications($event_history_offset, $perPage, $event_history_page, 'histories'); // イベント履歴を引っ張ってくる
$user_id = sprintf('%08d', $user->id); // IDのゼロ埋め
$birthday = substr($user->birthday, 0, 10); // 生年月日を文字列化

$errors = $_SESSION['errors'] ?? []; // バリデーションエラー
$success = $_SESSION['message_success'] ?? [];
$currentDate = date('Y-m-d');
// 今は4/1で固定
$startDate = date('Y') . '-' . MEMBERSHIP_START_DATE;
if ($currentDate < $startDate) {
    // 4/1以前なら去年
    $currentYear = date('y') - 1;
} else {
    $currentYear = date('y');
}

include('/var/www/html/moodle/custom/app/Views/common/header.php');
unset($_SESSION['old_input'], $_SESSION['message_success'], $_SESSION['message_']);
?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/mypage.css" />
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="MEMBER'S PAGE">マイページ</h2>
    </section>

    <!-- heading -->
    <section id="mypage" class="inner_l">
        <?php if ($tekijuku_commemoration !== false): ?>
        <div class="card-wrapper">
            <div id="card">
                <p class="card_head">適塾記念会デジタル会員証</p>
                <p class="card_year"><?php echo $currentYear; ?>年度の<br class="nopc" />本会会員ということを証明する</p>
                <p class="card_name"><?php echo $tekijuku_commemoration->name ?? ''; ?></p>
                <p class="card_id"><?php echo $tekijuku_commemoration->number ? sprintf('%08d', $tekijuku_commemoration->number) : ''; ?></p>
                <ul class="card_desc">
                    <li>・本会員証は他人への貸与や譲渡はできません。</li>
                    <li>・この会員証を提示すると適塾に何度でも参観できます。</li>
                </ul>
                <div class="card_pres">
                    <p class="card_pres_pos">適塾記念会会長</p>
                    <p class="card_pres_name">熊ノ郷 淳</p>
                </div>
            </div>
            <?php if ((int)$tekijuku_commemoration->is_delete === TEKIJUKU_COMMEMORATION_IS_DELETE['INACTIVE']) : ?>
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
                            <!-- 仮ですが何か出さないと結果がわからないので・・・　デザインは考えます -->
                            <?php if (!empty($basic_error)) { ?><p class="error"> <?= $basic_error ?></p><?php } ?>
                            <?php if (!empty($success)) { ?><p class="success"> <?= $success ?></p><?php } ?>
                            <ul class="list">
                                <li class="list_item01">
                                    <p class="list_label">ユーザーID</p>
                                    <div class="list_field f_txt"><?php echo $user_id; ?></div>
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
                                <li class="list_item09 long_item">
                                    <p class="list_label">備考</p>
                                    <div class="list_field f_txtarea">
                                        <textarea name="description"><?php echo htmlspecialchars($old_input['description'] ?? $user->description); ?></textarea>
                                        <?php if (!empty($errors['description'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['description']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <div id="parents_input_area">
                                    <li class="list_item10 req">
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
                                    <li class="list_item11 req">
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

        <?php if ($tekijuku_commemoration !== false): ?>
            <div id="tekijuku_form">
                <div id="form" class="mypage_cont">
                    <h3 class="mypage_head">適塾記念会 会員情報 
                        <?php if ((int)$tekijuku_commemoration->is_delete === TEKIJUKU_COMMEMORATION_IS_DELETE['INACTIVE']) : ?>
                            <div class="inactive-text">（退会済み）</div>
                        <?php endif; ?>
                    </h3>
                    <form method="POST" action="/custom/app/Controllers/mypage/mypage_update_controller.php" id="tekijuku_edit_form">
                        <input type="hidden" name="tekijuku_commemoration_id" value=<?php echo $tekijuku_commemoration->id ?>>
                        <div class="whitebox form_cont">
                            <div class="inner_m">
                                <ul class="list">
                                    <li class="list_item01">
                                        <p class="list_label">ユーザーID</p>
                                        <div class="list_field f_txt"><?php echo $tekijuku_commemoration->number ? sprintf('%08d', $tekijuku_commemoration->number) : ''; ?></div>
                                    </li>
                                    <li class="list_item02 req">
                                        <p class="list_label">会員種別</p>
                                        <div class="list_field f_txt"id="type_code" data-type-code="<?= htmlspecialchars($tekijuku_commemoration->type_code) ?>"><?php echo TYPE_CODE_LIST[$tekijuku_commemoration->type_code] ?></div>
                                    </li>
                                    <li class="list_item03 req">
                                        <p class="list_label">お名前</p>
                                        <div class="list_field f_txt">
                                            <input type="text" name="tekijuku_name" value="<?= htmlspecialchars($old_input['tekijuku_name'] ?? $tekijuku_commemoration->name); ?>">
                                            <?php if (!empty($errors['tekijuku_name'])): ?>
                                                <div class=" text-danger mt-2"><?= htmlspecialchars($errors['tekijuku_name']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item04 req">
                                        <p class="list_label">フリガナ</p>
                                        <div class="list_field f_txt">
                                            <input type="text" name="kana" value="<?= htmlspecialchars($old_input['kana'] ?? $tekijuku_commemoration->kana) ?>">
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
                                                    value="<?= htmlspecialchars($old_input['post_code'] ?? $tekijuku_commemoration->post_code) ?>"
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
                                            <input type="text" id="address" name="address" value="<?= htmlspecialchars($old_input['address'] ?? $tekijuku_commemoration->address) ?>">
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
                                                    value="<?= htmlspecialchars($old_input['tell_number'] ?? $tekijuku_commemoration->tell_number) ?>"
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
                                            <input type="email" name="tekijuku_email" value="<?= htmlspecialchars($old_input['tekijuku_email'] ?? $tekijuku_commemoration->email) ?>"
                                                inputmode="email"
                                                autocomplete="email"
                                                oninput="this.value = this.value.replace(/[^a-zA-Z0-9@._-]/g, '');">
                                            <?php if (!empty($errors['tekijuku_email'])): ?>
                                                <div class=" text-danger mt-2"><?= htmlspecialchars($errors['tekijuku_email']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item09 req">
                                        <p class="list_label">支払方法</p>
                                        <div class="list_field f_txt radio-group">
                                            <?php foreach ($payment_select_list as $key => $value) { ?>
                                                <input class="radio_input" id="payment_<?= $key ?>" style="vertical-align: middle;" type="radio" name="payment_method" value="<?= $key ?>"
                                                    <?php
                                                    // デフォルトの選択
                                                    if ((!$old_input['payment_method'] && $key == 1) ||
                                                        isSelected($key, $old_input['payment_method'] ?? $tekijuku_commemoration->payment_method, null)
                                                    ) {
                                                        echo 'checked';
                                                    }
                                                    ?> />
                                                <label for="payment_<?= $key ?>" class="radio_label"><?= $value ?></label>
                                            <?php } ?>
                                        </div>
                                    </li>
                                    <li class="list_item10">
                                        <p class="list_label">備考</p>
                                        <div class="list_field f_txt">
                                            <textarea name="note" rows="5"><?= htmlspecialchars($old_input['note'] ?? $tekijuku_commemoration->note, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                            <?php if (!empty($errors['note'])): ?>
                                                <div class=" text-danger mt-2"><?= htmlspecialchars($errors['note']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item11">
                                        <div class="list_field">
                                            <label class="checkbox_label">
                                                <input class="checkbox_input" type="checkbox" name="is_university_member" id="is_university_member" value="1" <?php echo ($old_input['is_university_member'] ?? $tekijuku_commemoration->is_university_member) == '1' ? 'checked' : ''; ?>>
                                                <label class="checkbox_label" for="is_university_member">大阪大学教職員・学生の方はこちらにチェックしてください。</label>
                                            </label>
                                        </div>
                                    </li>
                                    <li class="list_item12 req" id="department_field">
                                        <p class="list_label">所属部局（学部・研究科）</p>
                                        <div class="list_field f_txt">
                                            <input type="text" name="department" value="<?= htmlspecialchars($old_input['department'] ?? $tekijuku_commemoration->department); ?>">
                                            <?php if (!empty($errors['department'])): ?>
                                                <div class="text-danger mt-2"><?= htmlspecialchars($errors['department']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item13" id="major_field">
                                        <p class="list_label">講座/部課/専攻名</p>
                                        <div class="list_field f_txt">
                                            <input type="text" name="major" value="<?= htmlspecialchars($old_input['major'] ?? $tekijuku_commemoration->major); ?>">
                                            <?php if (!empty($errors['major'])): ?>
                                                <div class="text-danger mt-2"><?= htmlspecialchars($errors['major']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item14 req" id="official_field">
                                        <p class="list_label">職名・学年</p>
                                        <div class="list_field f_txt">
                                            <input type="text" name="official" value="<?= htmlspecialchars($old_input['official'] ?? $tekijuku_commemoration->official); ?>">
                                            <?php if (!empty($errors['official'])): ?>
                                                <div class="text-danger mt-2"><?= htmlspecialchars($errors['official']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="list_item15">
                                        <div class="area name">
                                            <label class="checkbox_label" for="">
                                                <input type="hidden" name="is_published" value="0">
                                                <input class="checkbox_input" type="checkbox" name="is_published" value="1" <?php echo ($old_input['is_published'] ?? $tekijuku_commemoration->is_published) == '1' ? 'checked' : ''; ?>>
                                                <label class="checkbox_label">氏名掲載を許可します</label>
                                            </label>
                                        </div>
                                    </li>
                                    <li class="list_item16 is_subscription_area">
                                        <div class="area plan">
                                            <label class="checkbox_label" for="">
                                                <input type="hidden" name="is_subscription" value="0">
                                                <input class="checkbox_input" id="is_subscription_checkbox" type="checkbox" name="is_subscription" value="1" <?php echo ($old_input['is_subscription'] ?? $tekijuku_commemoration->is_subscription) == '1' ? 'checked' : ''; ?>>
                                                <label class="checkbox_label" for="is_subscription_checkbox">定額課金プランを利用する</label>
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
                    ?>
                    <div class="info_wrap js_pay">
                        <form action="/custom/app/Views/event/reserve.php?id=<?php echo $application->event_application_id ?>" method="POST" class="info_wrap_cont">
                            <input type="hidden" name="event_id" value="<?php echo $application->event_id ?>">
                            <button type="submit" class="info_wrap_cont_btn">
                                <p class="date">
                                    <?php echo date('Y/m/d', strtotime($application->course_date)); ?>
                                </p>
                                <div class="txt">
                                    <p class="txt_ttl">
                                        <?php echo '【第' . $application->no . '回】' . $application->event_name ?>
                                    </p>
                                    <ul class="txt_other">
                                        <li>【会場】<span class="txt_other_place"><?php echo $application->venue_name ?></span></li>
                                        <li>【受講料】<span class="txt_other_money">￥ <?php echo $application->price ?></span></li>
                                        <li>【購入枚数】<span class="txt_other_num"><?php echo $application->ticket_count ?> 枚</span></li>
                                        <li>【決済】<span class="txt_other_pay"><?php echo $application->payment_date ? '決済済み' : '未決済' ?></span></li>
                                    </ul>
                                </div>
                            </button>
                        </form>
                        <a href="/custom/app/Views/event/reserve.php" class="info_wrap_qr">
                            <object type="image/svg+xml" data="/custom/public/assets/common/img/icon_qr_pay.svg" class="obj obj_pay"></object>
                            <object type="image/svg+xml" data="/custom/public/assets/common/img/icon_qr.svg" class="obj obj_no"></object>
                            <p class="txt">デジタル<br class="nosp" />チケットを<br />表示する</p>
                        </a>
                    </div>
                <?php endforeach; ?>
                <div class="pagination">
                    <?php if ($event_applications['pagination']['current_page'] > 1): ?>
                        <a href="?event_application_page=<?php echo $event_applications['pagination']['current_page'] - 1 ?>&event_history_page=<?php echo $event_histories['pagination']['current_page'] ?>#event_application" class="prev">← 前へ</a>
                    <?php endif; ?>
                    <span class="page-info">Page <?php echo $event_applications['pagination']['current_page']; ?> / <?php echo $event_applications['pagination']['total_pages']; ?></span>
                    <?php if ($event_applications['pagination']['current_page'] < $event_applications['pagination']['total_pages']): ?>
                        <a href="?event_application_page=<?php echo $event_applications['pagination']['current_page'] + 1 ?>&event_history_page=<?php echo $event_histories['pagination']['current_page'] ?>#event_application" class="next">次へ →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($allCourseDateNull): ?>
            <div>現在お申込みされているイベントはございません。下記申し込みイベント一覧からお申込みください。</div>
        <?php endif; ?>
        <a href="/custom/app/Views/event/register.php" class="btn btn_blue box_bottom_btn arrow">申し込みイベント一覧</a>

        <div class="mypage_cont history">
            <h3 id="event_histories" class="mypage_head">イベント履歴</h3>

            <?php $allHistoryCourseDateNull = true; ?>
            <?php if (!empty($event_histories['data'])): ?>
                <?php foreach ($event_histories['data'] as $history): ?>
                    <?php
                    if (is_null($history->course_date)) {
                        continue;
                    }
                    $allHistoryCourseDateNull = false;
                    ?>
                    <div class="info_wrap js_pay">
                        <form action="/custom/app/Views/event/history.php" method="POST" class="info_wrap_cont">
                            <input type="hidden" name="event_id" value="<?php echo $history->event_id ?>">
                            <button type="submit" class="info_wrap_cont_btn">
                                <p class="date">
                                    <?php echo date('Y/m/d', strtotime($history->course_date)); ?>
                                </p>
                                <div class="txt">
                                    <p class="txt_ttl">
                                        <?php echo '【第' . $history->no . '回】' . $history->event_name ?>
                                    </p>
                                    <ul class="txt_other">
                                        <li>【会場】<span class="txt_other_place"><?php echo $history->venue_name ?></span></li>
                                        <li>【受講料】<span class="txt_other_money">￥ <?php echo $history->price ?></span></li>
                                    </ul>
                                </div>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>

                <div class="pagination">
                    <?php if ($event_histories['pagination']['current_page'] > 1): ?>
                        <a href="?event_application_page=<?php echo $event_applications['pagination']['current_page'] ?>&event_history_page=<?php echo $event_histories['pagination']['current_page'] - 1 ?>#event_histories" class="prev">← 前へ</a>
                    <?php endif; ?>
                    <span class="page-info">Page <?php echo $event_histories['pagination']['current_page']; ?> / <?php echo $event_histories['pagination']['total_pages']; ?></span>
                    <?php if ($event_histories['pagination']['current_page'] < $event_histories['pagination']['total_pages']): ?>
                        <a href="?event_application_page=<?php echo $event_applications['pagination']['current_page'] ?>&event_history_page=<?php echo $event_histories['pagination']['current_page'] + 1 ?>#event_histories" class="next">次へ →</a>
                    <?php endif; ?>
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
        <p class="ticket_date">2025/00/00（金）</p>
        <p class="ticket_ttl">中之島芸術センター 演劇公演<br />『中之島デリバティブⅢ』</p>
        <div class="ticket_qr"><img src="/custom/public/assets/common/img/qr_dummy.png" alt="" /></div>
        <p class="ticket_txt">こちらの画面を受付でご提示ください。</p>
    </div>
</div>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>

<script>
    $(".info_wrap_qr").on("click", function() {
        srlpos = $(window).scrollTop();
        $("#modal").fadeIn();
        $("body").addClass("modal_fix").css({
            top: -srlpos
        });
        return false;
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
                $('#is_subscription_checkbox').prop('checked', false);
            }
        }
        // 登録成功文章を消す
        if ($('.success').length > 0) {
            setTimeout(function() {
                $('.success').fadeOut();
            }, 2000);
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
            showModal('知の広場 会員情報','編集します。本当によろしいですか？');
        });
        $('#tekijuku_form_button').on('click', function() {
            exec = 'tekijuku';
            showModal('適塾記念会 会員情報 ','編集します。本当によろしいですか？');
        });

        $(document).on('click', '.edit', function() {
            switch (exec) {
                case "user":
                    $('#user_edit_form').submit();
                    break
                case "tekijuku":
                        
                    $('#tekijuku_edit_form').submit();
                    break
                default :
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
                        <button class="modal-withdrawal edit">編集</button>
                        <button class="modal-close">閉じる</button>
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

    
    // 会員種別ごとの単価
    const PRICE_REGULAR_MEMBER = 2000;  // 普通会員単価
    const PRICE_SUPPORTING_MEMBER = 10000;  // 賛助会員単価

    // 現在選ばれている会員種別の単価を決定
    let currentUnitPrice = PRICE_REGULAR_MEMBER;

    // 会員種別が変更されたときの処理
    function updatePrice() {
        // 会員種別の選択を取得
        const typeCode = document.getElementById('type_code').getAttribute('data-type-code');
        // 会員種別によって単価を決定
        if (typeCode == 1) {
            currentUnitPrice = PRICE_REGULAR_MEMBER;
        } else if (typeCode == 2) {
            currentUnitPrice = PRICE_SUPPORTING_MEMBER;
        }

        // 枚数を取得
        const unitCount = parseInt(document.getElementById('unit').value) || 0;

        // 金額を計算
        const totalPrice = currentUnitPrice * unitCount;

        // 金額欄に表示
        document.getElementById('price').textContent = totalPrice.toLocaleString() + "円";
        document.getElementById('price_value').value = totalPrice;
    }

    // 枚数が増減したときの処理
    function updateUnitCount(delta) {
        const unitInput = document.getElementById('unit');
        let currentCount = parseInt(unitInput.value) || 0;
        currentCount += delta;

        // 0未満にはならないように、1を最低枚数に設定
        if (currentCount < 1) currentCount = 1;

        unitInput.value = currentCount;

        // 金額の再計算
        updatePrice();
    }

    // ページ読み込み時に金額を初期化
    window.onload = updatePrice;


    document.addEventListener("DOMContentLoaded", function () {
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

</script>