<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="CONTACT">お問い合わせ</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="form" class="contact entry">
            <ul id="flow">
                <li class="active">入力</li>
                <li>確認</li>
                <li>完了</li>
            </ul>
            <form method="" action="confirm.php" class="whitebox form_cont">
                <div class="inner_m">
                    <ul class="list">
                        <li class="list_item01 req">
                            <p class="list_label">お名前</p>
                            <div class="list_field f_txt">
                                <input type="text" />
                            </div>
                        </li>
                        <li class="list_item02 req">
                            <p class="list_label">メールアドレス</p>
                            <div class="list_field f_txt">
                                <input type="email" />
                            </div>
                        </li>
                        <li class="list_item03 req">
                            <p class="list_label">メールアドレス（確認用）</p>
                            <div class="list_field f_txt">
                                <input type="email" />
                            </div>
                        </li>
                        <li class="list_item04">
                            <p class="list_label">お問い合わせの項目</p>
                            <div class="list_field f_select select">
                                <select>
                                    <option value="" disabled selected>選択してください</option>
                                    <option></option>
                                </select>
                            </div>
                        </li>
                        <li class="list_item05 long_item req">
                            <p class="list_label">お問い合わせ内容</p>
                            <div class="list_field f_txtarea">
                                <textarea></textarea>
                            </div>
                        </li>
                    </ul>
                    <div class="agree">
                        <p class="agree_txt">個人情報の取扱いについて</p>
                        <label for="agree"><input type="checkbox" id="agree" />同意する</label>
                    </div>
                    <div class="form_btn">
                        <input type="submit" class="btn btn_red" value="入力内容の確認" />
                    </div>
                </div>
            </form>
        </section>
        <!-- contact -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>お問い合わせ</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>