<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
$payment_select_list = PAYMENT_SELECT_LIST;
$errors = $_SESSION['errors'] ?? [];
// $old_input = $_SESSION['old_input'] ?? [];

include('/var/www/html/moodle/custom/app/Views/common/header.php');
$user_data = $_SESSION['USER'];
unset($_SESSION['errors'], $_SESSION['old_input']);
?>

<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<html>
<style>
.area {
  display: flex; /* フレックスボックスを使用 */
  justify-content: flex-start; /* 左寄せに配置 */
  align-items: center; /* 縦方向中央揃え */
  margin-bottom: 24px; /* 下部マージン */
}
.text-danger {
    color: red;
}
.checkbox_input {
    margin-right: 8px; /* 好きな間隔に調整 */
}


</style>

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="TEKIJUKU COMMEMORATION CENTER REGISTER">適塾記念会会員登録</h2>
    </section>
    <div class="inner_l">
        <section id="form" class="user confirm">
            <ul id="flow">
                <li class="active">入力</li>
                <li>確認</li>
                <li>完了</li>
            </ul>
            <form method="POST" action="/custom/app/Controllers/tekijuku/tekijuku_controller.php" class="whitebox form_cont">
                <div class="inner_m">
                    <ul class="list">
                        <li class="list_item01 req">
                            <p class="list_label">会員種別</p>
                            <div class="list_field f_select select">
                                <select name="type_code" class="select">
                                    <option selected value=1>普通会員</option>
                                    <option value=2>賛助会員</option>
                                </select>
                            </div>
                        </li>
                        <li class="list_item02 req">
                            <p class="list_label">お名前</p>
                            <div class="list_field f_txt">
                                <input type="text" name="name" value="<?= htmlspecialchars($old_input['name'] ?? $user_data->name); ?>"> 
                                <?php if (!empty($errors['name'])): ?>
                                    <div class=" text-danger mt-2"><?= htmlspecialchars($errors['name']); ?></div>
                                <?php endif; ?>                               
                            </div>
                        </li>
                        <li class="list_item03 req">
                            <p class="list_label">フリガナ</p>
                            <div class="list_field f_txt">
                                <input type="text" name="kana" value="<?= htmlspecialchars($old_input['kana'] ?? $user_data->name_kana) ?>">
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
                                        value="<?= htmlspecialchars($old_input['post_code'] ?? '') ?>"
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
                                <input type="text" id="address" name="address" value="<?= htmlspecialchars($old_input['address'] ?? '') ?>">
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
                                        value="<?= htmlspecialchars($old_input['tell_number'] ?? $user_data->phone1) ?>"
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
                                <input type="email" name="email" value="<?= htmlspecialchars($old_input['email'] ?? $user_data->email) ?>"
                                    inputmode="email" 
                                    autocomplete="email" 
                                    oninput="this.value = this.value.replace(/[^a-zA-Z0-9@._-]/g, '');">
                                <?php if (!empty($errors['email'])): ?>
                                    <div class=" text-danger mt-2"><?= htmlspecialchars($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item09 req">
                            <p class="list_label">支払方法</p>
                            <div class="list_field f_txt radio-group">
                                <?php foreach ($payment_select_list as $key => $value) { ?>
                                    <input class="radio_input" type="radio" name="payment_method" value=<?= $key ?>
                                        <?php if (!$old_input['payment_method'] && $key === 1) { ?> checked
                                        <?php } else { ?>
                                        <?= isSelected($key, $old_input['payment_method'] ?? null, null) ? 'checked' : '';
                                        } ?> />
                                    <label class="radio_label" for="convenience"><?= $value ?></label>
                                <?php } ?>
                            </div>
                        </li>
                        <li class="list_item10">
                            <p class="list_label">備考</p>
                            <div class="list_field f_txt">
                                <textarea name="note" rows=5><?= htmlspecialchars($old_input['note']); ?></textarea>
                                <?php if (!empty($errors['note'])): ?>
                                    <div class=" text-danger mt-2"><?= htmlspecialchars($errors['note']); ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <div class="area name">
                            <input type="hidden" name="is_published" value=0>
                            <input class="checkbox_input" type="checkbox" name="is_published" value=1 <?php if ($old_input['is_published'] == '1') { ?>checked <?php } ?>>
                            <label class="checkbox_label">氏名掲載を許可します</label>
                        </div>
                        <div class="area plan">
                            <input type="hidden" name="is_subscription" value=0>
                            <input class="checkbox_input" type="checkbox" name="is_subscription_open" value=1 <?php if ($old_input['is_subscription'] == '1') { ?>checked <?php } ?>>
                            <label class="checkbox_label">定額課金プランを利用する</label>
                        </div>
                        <div class="form_btn">
                            <input type="submit" class="btn btn_red box_bottom_btn" value="登録する" />
                        </div>
                    </div>
                </ul>
            </form>
        </section>
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>会員登録</li>
</ul>
</html>



<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>

<script>
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
    const subscriptionArea = document.querySelector('.area.plan');

    function toggleSubscriptionArea() {
        const selectedValue = document.querySelector('input[name="payment_method"]:checked')?.value;
        const subscriptionCheckbox = document.querySelector('input[name="is_subscription_open"]');
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