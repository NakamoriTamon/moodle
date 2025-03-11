<?php
require_once('/var/www/html/moodle/config.php');
// $values = $_SESSION['old_input'];
$type_code_list = [1 => "普通会員", 2 => "賛助会員"];
$sex_list = [1 => "男性", 2 => "女性", 3 => "その他"];
$payment_mehod_list = PAYMENT_SELECT_LIST;
include('/var/www/html/moodle/custom/app/Views/common/header.php');
global $old_input;
?>

<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<html>

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="CONFIRM">内容確認</h2>
    </section>
    <div class="inner_l">
        <section id="form" class="event entry ">
            <ul id="flow">
                <li>入力</li>
                <li class="active">確認</li>
                <li>完了</li>
            </ul>
            <div class="whitebox form_cont">
                <div class="inner_m">
                    <ul class="list">
                        <li class="list_item01">
                            <p class="list_label">会員種別</p>
                            <div class="list_field">
                                <p><?= htmlspecialchars($type_code_list[$old_input['type_code']]) ?></p>
                            </div>
                        </li>
                        <li class="list_item02">
                            <p class="list_label">氏名</p>
                            <div class="list_field">
                                <p><?= htmlspecialchars($old_input['name']) ?></p>
                            </div>
                        </li>
                        <li class="list_item03">
                            <p class="list_label">フリガナ</p>
                            <div class="list_field">
                                <p><?= htmlspecialchars($old_input['kana']) ?></p>
                            </div>
                        </li>
                        <li class="list_item05">
                            <p class="list_label">郵便番号</p>
                            <div class="list_field">
                                <p><?= htmlspecialchars($old_input['post_code']) ?></p>
                            </div>
                        </li>
                        <li class="list_item06">
                            <p class="list_label">住所</p>
                            <div class="list_field">
                                <p><?= htmlspecialchars($old_input['address']) ?></p>
                            </div>
                        </li>
                        <li class="list_item07">
                            <p class="list_label">電話番号</p>
                            <div class="list_field">
                                <p><?= htmlspecialchars($old_input['combine_tell_number']) ?></p>
                            </div>
                        </li>
                        <li class="list_item08">
                            <p class="list_label">メールアドレス</p>
                            <div class="list_field">
                                <p><?= htmlspecialchars($old_input['email']) ?></p>
                            </div>
                        </li>
                        <li class="list_item09">
                            <p class="list_label">支払方法</p>
                            <div class="list_field">
                                <p><?= htmlspecialchars($payment_mehod_list[$old_input['payment_method']]) ?></p>
                            </div>
                        </li>
                        <li class="list_item10">
                            <p class="list_label">備考</p>
                            <div class="list_field">
                                <p><?= nl2br($old_input['note']) ?></p>
                            </div>
                        </li>
                        <li class="list_item11">
                            <div class="list_field">
                                <label class="checkbox_label">
                                    <input class="checkbox_input" type="checkbox" disabled name="is_published" <?php if ($old_input['is_published'] === '1') { ?>checked <?php } ?>>
                                    氏名掲載を許可します
                                </label>
                            </div>
                        </li>
                        <?php if ($old_input['payment_method'] == 2) { ?>
                            <li class="list_item12">
                                <div class="list_field">
                                    <label class="checkbox_label">
                                        <input class="checkbox_input" type="checkbox" disabled name="is_subscription" <?php if ($old_input['is_subscription'] === '1') { ?>checked <?php } ?>>
                                        定額課金プランを利用する
                                    </label>
                                </div>
                            </li>
                        <?php } ?>

                        <div class="form_btn">
                            <button type="button" class="btn btn_red" onclick="location.href='/custom/app/Controllers/tekijuku/tekijuku_upsert_contoroller.php';">登録する</button>
                            <button type="button" class="btn btn_gray" onclick="location.href='/custom/app/Views/tekijuku/registrate.php';">登録内容を変更する</button>
                        </div>
                    </ul>
                </div>
            </div>
        </section>
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li><a href="/custom/app/Views/tekijuku/registrate.php">会員登録</a></li>
    <li>会員登録確認</li>
</ul>

</html>


<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>