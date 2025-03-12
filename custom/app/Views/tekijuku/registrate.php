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


<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="TEKIJUKU COMMEMORATION CENTER REGISTER">適塾記念会会員登録</h2>
    </section>
    <div class="inner_l">
        <section id="form" class="user tekijuku">
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
                                <select name="type_code" id="type_code" onchange="updatePrice()">
                                    <option value=1 <?= isSelected(1, $old_input['type_code'] ?? null, null) ? 'selected' : '' ?>>普通会員</option>
                                    <option value=2 <?= isSelected(2, $old_input['type_code'] ?? null, null) ? 'selected' : '' ?>>賛助会員</option>
                                </select>
                            </div>
                        </li>
                        <li class="list_item02 req">
                            <p class="list_label">お名前</p>
                            <div class="list_field f_txt">
                                <input type="text" readonly name="name" value="<?= htmlspecialchars($old_input['name'] ?? $user_data->name); ?>">
                                <?php if (!empty($errors['name'])): ?>
                                    <div class=" text-danger mt-2"><?= htmlspecialchars($errors['name']); ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item03 req">
                            <p class="list_label">フリガナ</p>
                            <div class="list_field f_txt">
                                <input type="text" readonly name="kana" value="<?= htmlspecialchars($old_input['kana'] ?? $user_data->name_kana) ?>">
                                <?php if (!empty($errors['kana'])): ?>
                                    <div class=" text-danger mt-2"><?= htmlspecialchars($errors['kana']); ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item04 req">
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
                        <li class="list_item05 req">
                            <p class="list_label">住所</p>
                            <div class="list_field f_txt">
                                <input type="text" id="address" name="address" value="<?= htmlspecialchars($old_input['address'] ?? '') ?>">
                                <?php if (!empty($errors['address'])): ?>
                                    <div class=" text-danger mt-2"><?= htmlspecialchars($errors['address']); ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item06 req">
                            <p class="list_label">電話番号（ハイフンなし）</p>
                            <div class="list_field f_txt">
                                <div class="phone-input">
                                    <input type="text" name="tell_number" maxlength="15"
                                        value="<?= htmlspecialchars($old_input['tell_number'] ?? $user_data->phone1) ?>"
                                        pattern="[0-9]*" input mode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                    <?php if (!empty($errors['tell_number'])): ?>
                                        <div class=" text-danger mt-2"><?= htmlspecialchars($errors['tell_number']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        <li class="list_item07 req">
                            <p class="list_label">メールアドレス</p>
                            <div class="list_field f_txt">
                                <input type="email" readonly name="email" value="<?= htmlspecialchars($old_input['email'] ?? $user_data->email) ?>"
                                    inputmode="email"
                                    autocomplete="email"
                                    oninput="this.value = this.value.replace(/[^a-zA-Z0-9@._-]/g, '');">
                                <?php if (!empty($errors['email'])): ?>
                                    <div class=" text-danger mt-2"><?= htmlspecialchars($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item08 req">
                            <p class="list_label">口数</p>
                            <div class="list_field f_num">
                                <button type="button" class="num_min" onclick="updateUnitCount(-1)">ー</button>
                                <input type="number" id="unit" name="unit" value="<?= htmlspecialchars($old_input['unit'] ?? 1) ?>" class="num_txt" onchange="updatePrice()"/>
                                <button type="button" class="num_plus" onclick="updateUnitCount(1)">＋</button>
                            </div>
                        </li>
                        <li class="list_item09">
                            <p class="list_label">金額</p>
                            <p class="list_field" id="price"><?= htmlspecialchars(number_format($price)) ?>円</p>
                            <input type="hidden" id="price_value" name="price" value="<?= $price ?>" />
                        </li>
                        <li class="list_item10 req">
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
                        <li class="list_item11">
                            <p class="list_label">備考</p>
                            <div class="list_field f_txt">
                                <textarea name="note" rows=5><?= htmlspecialchars($old_input['note']); ?></textarea>
                                <?php if (!empty($errors['note'])): ?>
                                    <div class=" text-danger mt-2"><?= htmlspecialchars($errors['note']); ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item12">
                            <div class="list_field">
                                <label class="checkbox_label">
                                    <input class="checkbox_input" type="checkbox" name="is_university_member" id="is_university_member" value="1" <?php if ($old_input['is_university_member'] == '1') { ?>checked <?php } ?>>
                                    <label class="checkbox_label" for="is_university_member">大阪大学教職員・学生の方はこちらにチェックしてください。</label>
                                </label>
                            </div>
                        </li>
                        <li class="list_item13 req" id="department_field">
                            <p class="list_label">所属部局（学部・研究科）</p>
                            <div class="list_field f_txt">
                                <input type="text" name="department" value="<?= htmlspecialchars($old_input['department']); ?>">
                                <?php if (!empty($errors['department'])): ?>
                                    <div class="text-danger mt-2"><?= htmlspecialchars($errors['department']); ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item14" id="major_field">
                            <p class="list_label">講座/部課/専攻名</p>
                            <div class="list_field f_txt">
                                <input type="text" name="major" value="<?= htmlspecialchars($old_input['major']); ?>">
                                <?php if (!empty($errors['major'])): ?>
                                    <div class="text-danger mt-2"><?= htmlspecialchars($errors['major']); ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item15 req" id="official_field">
                            <p class="list_label">職名・学年</p>
                            <div class="list_field f_txt">
                                <input type="text" name="official" value="<?= htmlspecialchars($old_input['official']); ?>">
                                <?php if (!empty($errors['official'])): ?>
                                    <div class="text-danger mt-2"><?= htmlspecialchars($errors['official']); ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item16">
                            <div class="list_field">
                                <label class="checkbox_label">
                                    <input class="checkbox_input" id="is_published" type="checkbox" name="is_published" value=1 <?php if ($old_input['is_published'] == '1') { ?>checked <?php } ?>>
                                    <label class="checkbox_label" for="is_published">氏名掲載を許可します</label>
                                </label>
                            </div>
                        </li>
                        <li class="list_item17 is_subscription_area">
                            <div class="list_field">
                                <label class="checkbox_label" for="">
                                    <input class="checkbox_input" id="is_subscription" type="checkbox" name="is_subscription" value=1 <?php if ($old_input['is_subscription'] == '1') { ?>checked <?php } ?>>
                                    <label class="checkbox_label" for="is_subscription">定額課金プランを利用する</label>
                                </label>
                            </div>
                        </li>
                        <div class="form_btn">
                            <input type="submit" class="btn btn_red box_bottom_btn" value="登録する" />
                        </div>
                    </ul>
                </div>
            </form>
        </section>
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="/custom/app/Views/index.php">トップページ</a></li>
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

    // 決済方法取得
    $(document).ready(function() {
        paymentMethod($('input[name="payment_method"]:checked').val());
        $('input[name="payment_method"]').on('change', function() {
            paymentMethod($(this).val());
        });

        function paymentMethod(val) {
            console.log(val);
            if (val === "2") {
                $('.is_subscription_area').css('display', 'block');
            } else {
                $('.is_subscription_area').css('display', 'none');
                $('#is_subscription').prop('checked', false);
            }
        }
    });

    // 会員種別ごとの単価
    const PRICE_REGULAR_MEMBER = 2000;  // 普通会員単価
    const PRICE_SUPPORTING_MEMBER = 10000;  // 賛助会員単価

    // 現在選ばれている会員種別の単価を決定
    let currentUnitPrice = PRICE_REGULAR_MEMBER;

    // 会員種別が変更されたときの処理
    function updatePrice() {
        // 会員種別の選択を取得
        const typeCode = document.getElementById('type_code').value;
        
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