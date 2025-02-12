<?php
session_start();
$_SESSION['old_input'] = $_POST;
$values = $_POST;
// config等に最終的にまとめる
$type_code_list = [1 => "普通会員", 2 => "賛助会員"];
$sex_list = [1 => "男性", 2 => "女性", 3 => "その他"];
$payment_mehod_list = [1 => "口座振替", 2 => "クレジット", 3 => "銀行振込"];
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
    <p><?= htmlspecialchars($values['tell_number']) ?></p>
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
    <textarea name="note" rows=5><?= htmlspecialchars($values['note']) ?></textarea>
</div>

<div class="area">
    <input class="checkbox_input" type="checkbox" name="is_published" <?php if ($values['is_published']) { ?>checked <?php } ?>>
    <label class="checkbox_label">氏名掲載を許可します</label>
</div>

<button type="button" onclick="location.href='/custom/app/Controllers/tekijuku/tekijuku_upsert_contoroller.php';">登録する</button>


</html>