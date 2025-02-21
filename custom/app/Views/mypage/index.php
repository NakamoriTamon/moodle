<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/mypage.css" />
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="MEMBER'S PAGE">マイページ</h2>
    </section>
    <!-- heading -->

    <section id="mypage" class="inner_l">
        <div id="card">
            <p class="card_head">適塾記念会デジタル会員証</p>
            <p class="card_year">○○年度の<br class="nopc" />本会会員ということを証明する</p>
            <p class="card_name">阪大 花子</p>
            <p class="card_id">0000000000000</p>
            <ul class="card_desc">
                <li>・本会員証は他人への貸与や譲渡はできません。</li>
                <li>・この会員証を提示すると適塾に何度でも参観できます。</li>
            </ul>
            <div class="card_pres">
                <p class="card_pres_pos">適塾記念会会長</p>
                <p class="card_pres_name">熊ノ郷 淳</p>
            </div>
        </div>

        <div id="form" class="mypage_cont">
            <h3 class="mypage_head">知の広場 会員情報</h3>
            <form method="" action="">
                <div class="whitebox form_cont">
                    <div class="inner_m">
                        <ul class="list">
                            <li class="list_item01">
                                <p class="list_label">ユーザーID</p>
                                <div class="list_field f_txt">
                                    <input type="text" value="00000000" />
                                </div>
                            </li>
                            <li class="list_item02">
                                <p class="list_label">氏名</p>
                                <div class="list_field f_txt">
                                    <input type="text" value="阪大太郎" />
                                </div>
                            </li>
                            <li class="list_item03">
                                <p class="list_label">フリガナ</p>
                                <div class="list_field f_txt">
                                    <input type="text" value="ハンダイタロウ" />
                                </div>
                            </li>
                            <li class="list_item04">
                                <p class="list_label">生年月日</p>
                                <div class="list_field f_txt">
                                    <input type="text" value="0000/00/00" />
                                </div>
                            </li>
                            <li class="list_item05">
                                <p class="list_label">住所</p>
                                <div class="list_field f_txt">
                                    <input type="text" value="都道府県" />
                                </div>
                            </li>
                            <li class="list_item06">
                                <p class="list_label">メールアドレス</p>
                                <div class="list_field f_txt">
                                    <input type="email" value="abcdefg@gmail.com" />
                                </div>
                            </li>
                            <li class="list_item07">
                                <p class="list_label">電話番号</p>
                                <div class="list_field f_txt">
                                    <input type="tel" value="000-0000-0000" />
                                </div>
                            </li>
                            <li class="list_item08">
                                <p class="list_label">パスワード</p>
                                <div class="list_field f_txt">
                                    <input type="password" value="●●●●●●●●●" />
                                </div>
                            </li>
                            <li class="list_item08 long_item">
                                <p class="list_label">備考</p>
                                <div class="list_field f_txtarea">
                                    <textarea>
テキストが入ります。テキストが入りますテキストが入りますテキストが入りますテキストが入りますテキストが入りますテキストが入りますテキストが入ります
                        </textarea>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="form_btn">
                    <input type="submit" class="btn btn_blue box_bottom_btn" value="変更を確定する" />
                </div>
            </form>
        </div>

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
</script>