<?php
  include('../layouts/header.php');
?>
<link rel="stylesheet" type="text/css" href="/custom/public/css/contact.css" />
    <main id="subpage">
        <section id="heading" class="inner_l">
            <h2 class="head_ttl" data-en="CONTACT">お問い合わせ</h2>
        </section>

        <div class="inner_l">
            <section id="contact">
                <p class="sent">
                ご相談やお問い合わせがありましたら、以下のフォームより必要事項を送信ください。<br />
                内容を確認後、担当者よりご連絡をさせていただき、直接ご相談を承ります。<br />
                なお、内容によっては、ご連絡までお時間がかかるものがございますので、あらかじめご了承ください。
                </p>
                <form method="" action="" class="whitebox contact_form">
                <div class="inner_s">
                    <ul class="list">
                    <li>
                        <p class="list_label">お名前</p>
                        <div class="list_field f_txt">
                        <input type="text" />
                        </div>
                    </li>
                    <li>
                        <p class="list_label">メールアドレス</p>
                        <div class="list_field f_txt">
                        <input type="email" />
                        </div>
                    </li>
                    <li>
                        <p class="list_label">メールアドレス（確認用）</p>
                        <div class="list_field f_txt">
                        <input type="email" />
                        </div>
                    </li>
                    <li>
                        <p class="list_label">お問い合わせの項目</p>
                        <div class="list_field f_select select">
                        <select>
                            <option value="" disabled selected>選択してください</option>
                            <option></option>
                        </select>
                        </div>
                    </li>
                    <li>
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
                    <input type="submit" class="btn btn_red" value="入力内容の確認" />
                </div>
                </form>
            </section>
        </div>
    </main>

    <ul id="pankuzu" class="inner_l">
      <li><a href="/custom/app/Views/index.php">トップページ</a></li>
      <li>お問い合わせ</li>
    </ul>

<?php
  include('../layouts/footer.php');
?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script src="../assets/common/js/common.js"></script>
</html>
