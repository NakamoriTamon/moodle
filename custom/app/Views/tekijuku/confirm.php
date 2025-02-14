<?php
session_start();
require_once('/var/www/html/moodle/config.php');
$values = $_SESSION['old_input'];
$type_code_list = [1 => "普通会員", 2 => "賛助会員"];
$sex_list = [1 => "男性", 2 => "女性", 3 => "その他"];
$payment_mehod_list = PAYMENT_SELECT_LIST;
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

    p {
        margin: 0px;
        padding: 0px;
        font-size: 20px;
    }
</style>

<div class="area">
    <label>会員種別</label>
    <p><?= htmlspecialchars($type_code_list[$values['type_code']]) ?></p>
</div>
<div class="area">
    <label>氏名</label>
    <p><?= htmlspecialchars($values['name']) ?></p>
</div>
<div class="area">
    <label>フリガナ</label>
    <p><?= htmlspecialchars($values['kana']) ?></p>
</div>
<div class="area">
    <label>性別</label>
    <p><?= htmlspecialchars($sex_list[$values['sex']]) ?></p>
</div>
<div class="area">
    <label>郵便番号（ハイフンなし）</label>
    <p><?= htmlspecialchars($values['post_code']) ?></p>
</div>
<div class="area">
    <label for="address">住所</label>
    <p><?= htmlspecialchars($values['address']) ?></p>
</div>
<div class="area">
    <label>電話番号</label>
    <p><?= htmlspecialchars($values['combine_tell_number']) ?></p>
</div>
<div class="area">
    <label>メールアドレス</label>
    <p><?= htmlspecialchars($values['email']) ?></p>
</div>
<div class="area">
    <label style="margin-bottom: 20px">支払方法</label>
    <p><?= htmlspecialchars($payment_mehod_list[$values['payment_method']]) ?></p>
</div>
<div class="area">
    <label>備考</label>
    <p><?= htmlspecialchars($values['note']) ?></p>
</div>
<div class="area">
    <input class="checkbox_input" type="checkbox" disabled name="is_published" <?php if ($values['is_published'] === '1') { ?>checked <?php } ?>>
    <label class="checkbox_label">氏名掲載を許可します</label>
</div>
<div class="area">
    <input class="checkbox_input" type="checkbox" disabled name="is_subscription" <?php if ($values['is_subscription'] === '1') { ?>checked <?php } ?>>
    <label class="checkbox_label">定額課金プランを利用する</label>
</div>

<button type="button" onclick="location.href='/custom/app/Controllers/tekijuku/tekijuku_upsert_contoroller.php';">登録する</button>
<button type="button" onclick="location.href='/custom/app/Views/tekijuku/registrate.php';">登録内容を変更する</button>

</html>