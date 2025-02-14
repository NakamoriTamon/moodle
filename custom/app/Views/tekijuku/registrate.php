<?php
session_start();
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
$payment_select_list = PAYMENT_SELECT_LIST;
$errors = $_SESSION['errors'] ?? [];
$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);
?>
<html>
<style>
    .area {
        display: flex;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    label {
        width: 100%;
        margin-bottom: 5px;
    }

    select,
    input,
    textarea {
        width: 300px
    }

    #post_button {
        margin-left: 5px;
    }

    .checkbox_input,
    .radio_input {
        width: 30px;
    }

    .checkbox_label {
        width: calc(100% - 50px);
    }

    .radio_label {
        width: 100px;
    }

    .text-danger {
        width: 100%;
        color: red;
    }

    .phone-input {
        display: flex;
        align-items: center;
    }

    .phone-input input {
        width: 60px;
        /* 入力欄の幅を調整 */
        text-align: center;
    }

    .phone-input span {
        margin: 0 5px;
    }
</style>
<form method="POST" action="/custom/app/Controllers/tekijuku/tekijuku_controller.php">
    <div class="area">
        <label>会員種別</label>
        <select name="type_code">
            <option selected value=1>普通会員</option>
            <option value=2>賛助会員</option>
        </select>
    </div>
    <div class="area">
        <label>氏名</label>
        <input name="name" value="<?= htmlspecialchars($old_input['name'] ?? '') ?>">
        <?php if (!empty($errors['name'])): ?>
            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['name']); ?></div>
        <?php endif; ?>
    </div>
    <div class="area">
        <label>フリガナ</label>
        <input name="kana" value="<?= htmlspecialchars($old_input['kana'] ?? '') ?>">
        <?php if (!empty($errors['kana'])): ?>
            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['kana']); ?></div>
        <?php endif; ?>
    </div>
    <div class="area">
        <label>性別</label>
        <select name="sex">
            <option selected value=1 <?= isSelected(1, $old_input['sex'] ?? null, null) ? 'selected' : '' ?>>男性</option>
            <option value=2 <?= isSelected(2, $old_input['sex'] ?? null, null) ? 'selected' : '' ?>>女性</option>
            <option value=3 <?= isSelected(3, $old_input['sex'] ?? null, null) ? 'selected' : '' ?>>その他</option>
        </select>
    </div>
    <div class="area">
        <label>郵便番号（ハイフンなし）</label>
        <input type="text" id="zip" name="post_code" maxlength="7" pattern="\d{7}" required
            value="<?= htmlspecialchars($old_input['post_code'] ?? '') ?>">
        <button id="post_button" type="button" onclick="fetchAddress()">住所検索</button>
        <?php if (!empty($errors['post_code'])): ?>
            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['post_code']); ?></div>
        <?php endif; ?>
    </div>
    <div class="area">
        <label for="address">住所</label>
        <input type="text" id="address" name="address" value="<?= htmlspecialchars($old_input['address'] ?? '') ?>">
        <?php if (!empty($errors['address'])): ?>
            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['address']); ?></div>
        <?php endif; ?>
    </div>
    <div class="area">
        <label>電話番号</label>
        <div class="phone-input">
            <input type="text" name="tell_number[]" maxlength="4" required value="<?= htmlspecialchars($old_input['tell_number'][0] ?? '') ?>">
            <span>-</span>
            <input type="text" name="tell_number[]" maxlength="4" required value="<?= htmlspecialchars($old_input['tell_number'][1] ?? '') ?>">
            <span>-</span>
            <input type="text" name="tell_number[]" maxlength="4" required value="<?= htmlspecialchars($old_input['tell_number'][2] ?? '') ?>">
        </div>
        <?php if (!empty($errors['tell_number'])): ?>
            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['tell_number']); ?></div>
        <?php endif; ?>
    </div>
    <div class="area">
        <label>メールアドレス</label>
        <input type="email" name="email" value="<?= htmlspecialchars($old_input['email'] ?? '') ?>">
        <?php if (!empty($errors['email'])): ?>
            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['email']); ?></div>
        <?php endif; ?>
    </div>
    <div class="area">
        <label style="margin-bottom: 20px">支払方法</label>
        <div class="radio-group">
            <?php foreach ($payment_select_list as $key => $value) { ?>
                <input class="radio_input" type="radio" name="payment_method" value=<?= $key ?>
                    <?php if (!$old_input['payment_method'] && $key === 1) { ?> checked
                    <?php } else { ?>
                    <?= isSelected($key, $old_input['payment_method'] ?? null, null) ? 'checked' : '';
                    } ?> />
                <label class="radio_label" for="convenience"><?= $value ?></label>
            <?php } ?>
        </div>
    </div>
    <div class="area">
        <label>備考</label>
        <textarea name="note" rows=5><?= htmlspecialchars($old_input['note']); ?></textarea>
        <?php if (!empty($errors['note'])): ?>
            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['note']); ?></div>
        <?php endif; ?>
    </div>
    <div class="area">
        <input type="hidden" name="is_published" value=0>
        <input class="checkbox_input" type="checkbox" name="is_published" value=1 <?php if ($old_input['is_published'] == '1') { ?>checked <?php } ?>>
        <label class="checkbox_label">氏名掲載を許可します</label>
    </div>
    <div class="area">
        <input type="hidden" name="is_subscription" value=0>
        <input class="checkbox_input" type="checkbox" name="is_subscription" value=1 <?php if ($old_input['is_subscription'] == '1') { ?>checked <?php } ?>>
        <label class="checkbox_label">定額課金プランを利用する</label>
    </div>

    <button type="submit">登録する</button>

</form>

</html>

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
</script>