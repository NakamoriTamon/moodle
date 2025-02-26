<?php 
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Controllers/mypage/mypage_controller.php');

$mypage_controller = new MypageController;
$userData = $mypage_controller->getUserData();
$tekijuku_commemoration = $mypage_controller->getTekijukuCommemoration();
$id = sprintf('%08d', $userData->id); // IDのゼロ埋め
$birthday = substr($userData->birthday, 0, 10); // 生年月日を文字列化

$errors = $_SESSION['errors'] ?? []; // バリデーションエラー
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
unset($_SESSION['old_input']);
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
        <?php endif; ?>
        <div id="user_form">
            <div id="form" class="mypage_cont">
                <h3 class="mypage_head">知の広場 会員情報</h3>
                <form method="POST" action="/custom/app/Controllers/mypage/mypage_update_controller.php">
                    <div class="whitebox form_cont">
                        <div class="inner_m">
                            <ul class="list">
                                <li class="list_item01">
                                    <p class="list_label">ユーザーID</p>
                                    <div class="list_field f_txt"><?php echo $id; ?></div>
                                </li>
                                <li class="list_item02 req">
                                    <p class="list_label">お名前</p>
                                    <div class="list_field f_txt">
                                        <input type="text" name="name" value="<?php echo htmlspecialchars($userData->name); ?>" />
                                        <?php if (!empty($errors['name'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['name']); ?></div>
                                        <?php endif; ?>    
                                    </div>
                                </li>
                                <li class="list_item03 req">
                                    <p class="list_label">フリガナ</p>
                                    <div class="list_field f_txt">
                                        <input type="text" name="name_kana" value="<?php echo htmlspecialchars($userData->name_kana); ?>" />
                                        <?php if (!empty($errors['name_kana'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['name_kana']); ?></div>
                                        <?php endif; ?>    
                                    </div>
                                </li>
                                <li class="list_item04 req">
                                    <p class="list_label">お住いの都道府県</p>
                                    <div class="list_field f_txt">
                                        <select name="city" class="select">
                                            <?php foreach ($prefectures as $prefecture): ?>
                                                <option value="<?php echo htmlspecialchars($prefecture); ?>" 
                                                    <?php echo ($userData->city == $prefecture) ? 'selected' : ''; ?>>
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
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($userData->email); ?>" 
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
                                            value="<?php echo htmlspecialchars($userData->phone1); ?>" 
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '');"/>
                                        <?php if (!empty($errors['phone'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['phone']); ?></div>
                                        <?php endif; ?> 
                                    </div>
                                </li>
                                <li class="list_item08 req">
                                    <p class="list_label">生年月日</p>
                                    <div class="list_field f_txt">
                                        <input type="date" name="birthday" value="<?php echo htmlspecialchars($birthday); ?>" />
                                        <?php if (!empty($errors['birthday'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['birthday']); ?></div>
                                        <?php endif; ?> 
                                    </div>
                                </li>
                                <li class="list_item09 long_item">
                                    <p class="list_label">備考</p>
                                    <div class="list_field f_txtarea">
                                        <textarea name="description"><?php echo htmlspecialchars($userData->description); ?></textarea>
                                        <?php if (!empty($errors['description'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['description']); ?></div>
                                        <?php endif; ?> 
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="form_btn">
                        <input type="submit" class="btn btn_red box_bottom_btn" value="知の広場会員情報の変更を確定する" name="update_user"/>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($tekijuku_commemoration !== false): ?>
            
        <div id="tekijuku_form">
            <div id="form" class="mypage_cont">
                <h3 class="mypage_head">適塾記念会 会員情報</h3>
                <form method="POST" action="/custom/app/Controllers/mypage/mypage_update_controller.php">
                    
                <input type="hidden" name="tekijuku_commemoration_id" value=<?php echo $tekijuku_commemoration->id ?>>
                    <div class="whitebox form_cont">
                        <div class="inner_m">
                            <ul class="list">
                                <li class="list_item01">
                                    <p class="list_label">ユーザーID</p>
                                    <div class="list_field f_txt"><?php echo $tekijuku_commemoration->number ? sprintf('%08d', $tekijuku_commemoration->number) : ''; ?></div>
                                </li>
                                <li class="list_item01 req">
                                    <p class="list_label">会員種別</p>
                                    <div class="list_field f_txt"><?php echo TYPE_CODE_LIST[$tekijuku_commemoration->type_code] ?></div>
                                </li>
                                <li class="list_item02 req">
                                    <p class="list_label">お名前</p>
                                    <div class="list_field f_txt">
                                        <input type="text" name="tekijuku_name" value="<?= htmlspecialchars($old_input['tekijuku_name'] ?? $tekijuku_commemoration->name); ?>"> 
                                        <?php if (!empty($errors['tekijuku_name'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['tekijuku_name']); ?></div>
                                        <?php endif; ?>                               
                                    </div>
                                </li>
                                <li class="list_item03 req">
                                    <p class="list_label">フリガナ</p>
                                    <div class="list_field f_txt">
                                        <input type="text" name="kana" value="<?= htmlspecialchars($old_input['kana'] ?? $tekijuku_commemoration->kana) ?>">
                                        <?php if (!empty($errors['kana'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['kana']); ?></div>
                                        <?php endif; ?>                             
                                    </div>
                                </li>
                                <li class="list_item04 req">
                                    <p class="list_label">性別</p>
                                    <div class="list_field f_select select">
                                        <select name="sex" class="select">
                                            <option selected value=1 <?= isSelected(1, $old_input['sex'] ?? null, null) ? 'selected' : '' ?>>男性</option>
                                            <option value=2 <?= isSelected(2, $old_input['sex'] ?? null, null) ? 'selected' : '' ?>>女性</option>
                                            <option value=3 <?= isSelected(3, $old_input['sex'] ?? null, null) ? 'selected' : '' ?>>その他</option>
                                        </select>
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
                                            <input class="radio_input" type="radio" name="payment_method" value="<?= $key ?>"
                                                <?php 
                                                // デフォルトの選択
                                                if ((!$old_input['payment_method'] && $key == 1) || 
                                                    isSelected($key, $old_input['payment_method'] ?? $tekijuku_commemoration->payment_method, null)) {
                                                    echo 'checked';
                                                }
                                                ?> />
                                            <label class="radio_label"><?= $value ?></label>
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
                                <div class="area name">
                                    <input type="hidden" name="is_published" value="0">
                                    <input class="checkbox_input" type="checkbox" name="is_published" value="1" <?php echo ($old_input['is_published'] ?? $tekijuku_commemoration->is_published) == '1' ? 'checked' : ''; ?>>
                                    <label class="checkbox_label">氏名掲載を許可します</label>
                                </div>
                                <div class="area plan">
                                    <input type="hidden" name="is_subscription" value="0">
                                    <input class="checkbox_input" id="is_subscription_checkbox" type="checkbox" name="is_subscription" value="1" <?php echo ($old_input['is_subscription'] ?? $tekijuku_commemoration->is_subscription) == '1' ? 'checked' : ''; ?>>
                                    <label class="checkbox_label" for="is_subscription_checkbox">定額課金プランを利用する</label>
                                </div>
                            </ul>
                        </div>
                    </div>
                    <div class="form_btn">
                        <input type="submit" class="btn btn_red box_bottom_btn" value="適塾記念会会員情報の変更を確定する" name="update_membership"/>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
        <div class="mypage_cont reserve">
            <h3 class="mypage_head">予約情報</h3>
            <div class="info_wrap js_pay">
                <a href="/custom/app/Views/event/reserve.php" class="info_wrap_cont">
                    <p class="date">0000/00/00</p>
                    <div class="txt">
                        <p class="txt_ttl">
                            大阪大学ミュージアム・リンクス講座 「大阪文化の多様性と創造性をさぐる
                            －地域の歴史に即して－」　船場と美術　伝統と今が出会う街
                        </p>
                        <ul class="txt_other">
                            <li>【会場】<span class="txt_other_place">大阪大学</span></li>
                            <li>【受講料】<span class="txt_other_money">￥0,000</span></li>
                            <li>【購入枚数】<span class="txt_other_num">2枚</span></li>
                            <li>【決済】<span class="txt_other_pay">決済済</span></li>
                        </ul>
                    </div>
                </a>
                <a href="/custom/app/Views/event/reserve.php" class="info_wrap_qr">
                    <object
                        type="image/svg+xml"
                        data="../assets/common/img/icon_qr_pay.svg"
                        class="obj obj_pay"></object>
                    <object
                        type="image/svg+xml"
                        data="../assets/common/img/icon_qr.svg"
                        class="obj obj_no"></object>
                    <p class="txt">デジタル<br class="nosp" />チケットを<br />表示する</p>
                </a>
            </div>
            <div class="info_wrap">
                <a href="/custom/app/Views/event/reserve.php" class="info_wrap_cont">
                    <p class="date">0000/00/00</p>
                    <div class="txt">
                        <p class="txt_ttl">
                            大阪大学ミュージアム・リンクス講座 「大阪文化の多様性と創造性をさぐる
                            －地域の歴史に即して－」　船場と美術　伝統と今が出会う街
                        </p>
                        <ul class="txt_other">
                            <li>【会場】<span class="txt_other_place">大阪大学</span></li>
                            <li>【受講料】<span class="txt_other_money">￥0,000</span></li>
                            <li>【購入枚数】<span class="txt_other_num">2枚</span></li>
                            <li>【決済】<span class="txt_other_pay">未決済</span></li>
                        </ul>
                    </div>
                </a>
                <a href="" class="info_wrap_qr">
                    <object
                        type="image/svg+xml"
                        data="../assets/common/img/icon_qr_pay.svg"
                        class="obj obj_pay"></object>
                    <object
                        type="image/svg+xml"
                        data="../assets/common/img/icon_qr.svg"
                        class="obj obj_no"></object>
                    <p class="txt">デジタル<br class="nosp" />チケットを<br />表示する</p>
                </a>
            </div>
        </div>

        <a href="/custom/app/Views/event/register.php" class="btn btn_blue box_bottom_btn arrow">申し込みイベント一覧</a>

        <div class="mypage_cont history">
            <h3 class="mypage_head">イベント履歴</h3>
            <div class="info_wrap">
                <a href="/custom/app/Views/event/history.php" class="info_wrap_cont">
                    <p class="date">0000/00/00</p>
                    <div class="txt">
                        <p class="txt_ttl">
                            大阪大学ミュージアム・リンクス講座 「大阪文化の多様性と創造性をさぐる
                            －地域の歴史に即して－」　船場と美術　伝統と今が出会う街
                        </p>
                        <ul class="txt_other">
                            <li>【会場】<span class="txt_other_place">大阪大学</span></li>
                            <li>【受講料】<span class="txt_other_money">￥0,000</span></li>
                        </ul>
                    </div>
                </a>
            </div>
            <div class="info_wrap">
                <a href="/custom/app/Views/event/history.php" class="info_wrap_cont">
                    <p class="date">0000/00/00</p>
                    <div class="txt">
                        <p class="txt_ttl">
                            大阪大学ミュージアム・リンクス講座 「大阪文化の多様性と創造性をさぐる
                            －地域の歴史に即して－」　船場と美術　伝統と今が出会う街
                        </p>
                        <ul class="txt_other">
                            <li>【会場】<span class="txt_other_place">大阪大学</span></li>
                            <li>【受講料】<span class="txt_other_money">￥0,000</span></li>
                        </ul>
                    </div>
                </a>
            </div>
        </div>

        <div class="mypage_cont setting">
            <h3 class="mypage_head">お知らせメール設定</h3>
            <p class="sent">
                ご登録いただいたアドレス宛にイベントの最新情報やメールマガジンをお送りいたします。<br />
                こちらで受信の設定が可能です。不要な方はチェックを外してください。
            </p>
            <label class="set_check"><input type="checkbox" />受け取る</label>
            <a href="" class="btn btn_blue box_bottom_btn arrow">前へ戻る</a>
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
    
    
    document.addEventListener("DOMContentLoaded", function () {
        const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
        const subscriptionCheckbox = document.getElementById('is_subscription_checkbox');  // idで取得
        const subscriptionArea = document.querySelector('.area.plan');

        function toggleSubscriptionArea() {
            const selectedValue = document.querySelector('input[name="payment_method"]:checked')?.value;
            if (selectedValue === "2") {
                subscriptionArea.style.display = "block"; // 表示
            } else {
                subscriptionCheckbox.checked = false;  // チェックを外す
                subscriptionArea.style.display = "none";  // 非表示
            }
        }

        // 初回実行（ページ読み込み時）
        toggleSubscriptionArea();

        // ラジオボタンの変更を監視
        paymentRadios.forEach(radio => {
            radio.addEventListener("change", toggleSubscriptionArea);
        });
    });
</script>