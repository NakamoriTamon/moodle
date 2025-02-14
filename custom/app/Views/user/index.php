<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="USER REGISTRATION">ユーザー登録</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="form" class="user entry">
            <ul id="flow">
                <li class="active">入力</li>
                <li>完了</li>
            </ul>
            <form method="" action="complete.php" class="whitebox form_cont">
                <div class="inner_m">
                    <ul class="list">
                        <li class="list_item01 req">
                            <p class="list_label">お名前</p>
                            <div class="list_field f_txt">
                                <input type="text" />
                            </div>
                        </li>
                        <li class="list_item02 req">
                            <p class="list_label">フリガナ</p>
                            <div class="list_field f_txt">
                                <input type="text" />
                            </div>
                        </li>
                        <li class="list_item03 req">
                            <p class="list_label">お住いの都道府県</p>
                            <div class="list_field f_select select">
                                <select>
                                    <option value="" disabled selected>都道府県を選択</option>
                                    <option>ff</option>
                                </select>
                            </div>
                        </li>
                        <li class="list_item04 req">
                            <p class="list_label">メールアドレス</p>
                            <div class="list_field f_txt">
                                <input type="email" />
                            </div>
                        </li>
                        <li class="list_item05 req">
                            <p class="list_label">メールアドレス（確認用）</p>
                            <div class="list_field f_txt">
                                <input type="email" />
                            </div>
                        </li>
                        <li class="list_item06 req">
                            <p class="list_label">パスワード</p>
                            <div class="list_field f_txt">
                                <input type="password" />
                                <p class="note">
                                    8文字以上20文字以内、数字・アルファベットを組み合わせてご入力ください。
                                </p>
                                <p class="note">使用できる記号!"#$%'()*+,-./:;<=>?@[¥]^_{|}~</p>
                            </div>
                        </li>
                        <li class="list_item07 req">
                            <p class="list_label">パスワード（確認用）</p>
                            <div class="list_field f_txt">
                                <input type="password" />
                            </div>
                        </li>
                        <li class="list_item08 req">
                            <p class="list_label">メールアドレス（確認用）</p>
                            <div class="list_field f_txt">
                                <input type="email" />
                            </div>
                        </li>
                        <li class="list_item09 req">
                            <p class="list_label">電話番号（携帯もしくは自宅）</p>
                            <div class="list_field f_txt">
                                <input type="tel" />
                            </div>
                        </li>
                        <li class="list_item10 req">
                            <p class="list_label">生年月日</p>
                            <div class="list_field f_txt">
                                <input type="text" />
                            </div>
                        </li>
                    </ul>
                    <div class="agree">
                        <p class="agree_txt">
                            個人情報の提供について、大阪大学の個人情報保護に関する<a href="">プライバシーポリシー</a>を確認し、同意します。
                        </p>
                        <label for="agree"><input type="checkbox" id="agree" />同意する</label>
                    </div>
                    <div class="form_btn">
                        <input type="submit" class="btn btn_red" value="この内容で仮登録する" />
                    </div>
                </div>
            </form>
        </section>
        <!-- contact -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>ユーザー登録</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>