<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="APPLICATION">イベント申し込み</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="form" class="event entry">
            <ul id="flow">
                <li class="active">入力</li>
                <li>確認</li>
                <li>完了</li>
            </ul>
            <form method="POST" action="confirm.php" class="whitebox form_cont">
                <div class="inner_m">
                    <ul class="list">
                        <li class="list_item01">
                            <p class="list_label">お名前</p>
                            <p class="list_field">阪大太郎</p>
                        </li>
                        <li class="list_item02">
                            <p class="list_label">フリガナ</p>
                            <p class="list_field">ハンダイタロウ</p>
                        </li>
                        <li class="list_item03">
                            <p class="list_label">チケット名称</p>
                            <p class="list_field">
                                大阪大学ミュージアム・リンクス講座 「大阪文化の多様性と創造性をさぐる
                                －地域の歴史に即して－」　船場と美術　伝統と今が出会う街
                            </p>
                        </li>
                        <li class="list_item04 req">
                            <p class="list_label">枚数選択</p>
                            <div class="list_field f_num">
                                <button type="button" class="num_min">ー</button>
                                <input type="text" value="0" class="num_txt" />
                                <button type="button" class="num_plus">＋</button>
                            </div>
                        </li>
                        <li class="list_item05">
                            <p class="list_label">金額</p>
                            <p class="list_field">0,000円</p>
                        </li>
                        <li class="list_item06 req">
                            <p class="list_label">お支払方法</p>
                            <div class="list_field list_row">
                                <label class="f_radio"><input type="radio" name="" />コンビニ決済</label>
                                <label class="f_radio"><input type="radio" name="" />クレジット決済</label>
                                <label class="f_radio"><input type="radio" name="" />銀行振込</label>
                            </div>
                        </li>
                        <li class="list_item07">
                            <p class="list_label">複数チケット申し込みの場合お連れ様のメールアドレス</p>
                            <div class="list_field f_txt">
                                <input type="text" />
                            </div>
                        </li>
                        <li class="list_item08 req">
                            <p class="list_label">
                                今後大阪大学からのメールによるイベントのご案内を希望されますか？
                            </p>
                            <div class="list_field list_row">
                                <label class="f_radio"><input type="radio" name="" />希望する</label>
                                <label class="f_radio"><input type="radio" name="" />希望しない</label>
                            </div>
                        </li>
                        <li class="list_item09 long_item">
                            <p class="list_label">
                                本イベントはどのようにお知りになりましたか？<span>※複数選択可</span>
                            </p>
                            <div class="list_field list_col">
                                <p class="f_check">
                                    <label><input type="checkbox" name="" />チラシ</label>
                                    <span class="comment">※「その他」の欄にどこでご覧になったか具体的にご記載ください</span>
                                </p>
                                <p class="f_check">
                                    <label><input type="checkbox" name="" />ウェブサイト</label>
                                    <span class="comment">※「その他」の欄にどこでご覧になったか具体的にご記載ください</span>
                                </p>
                                <p class="f_check">
                                    <label><input
                                            type="checkbox"
                                            name="" />大阪大学公開講座「知の広場」からのメール</label>
                                </p>
                                <p class="f_check">
                                    <label><input type="checkbox" name="" />SNS（X, Instagram, Facebookなど）</label>
                                </p>
                                <p class="f_check">
                                    <label><input type="checkbox" name="" />21世紀懐徳堂からのメールマガジン</label>
                                </p>
                                <p class="f_check">
                                    <label><input type="checkbox" name="" />大阪大学卒業生メールマガジン</label>
                                </p>
                                <p class="f_check">
                                    <label><input type="checkbox" name="" />大阪大学入試課からのメール</label>
                                </p>
                                <p class="f_check">
                                    <label><input type="checkbox" name="" />Peatixからのメール</label>
                                </p>
                                <p class="f_check">
                                    <label><input type="checkbox" name="" />知人からの紹介</label>
                                </p>
                                <p class="f_check">
                                    <label><input type="checkbox" name="" />講師・スタッフからの紹介</label>
                                </p>
                                <p class="f_check">
                                    <label><input type="checkbox" name="" />自治体の広報・掲示</label>
                                </p>
                                <p class="f_check">
                                    <label><input type="checkbox" name="" />スマートニュース広告</label>
                                </p>
                                <p class="f_check f_other">
                                    <label><input type="checkbox" name="" />その他</label>
                                    <input type="text" />
                                </p>
                            </div>
                        </li>
                        <li class="list_item10 long_item">
                            <p class="list_label">備考欄</p>
                            <div class="list_field f_txtarea">
                                <textarea></textarea>
                            </div>
                        </li>
                    </ul>

                    <div class="form_btn">
                        <input type="submit" class="btn btn_red" value="確認画面へ進む" />
                    </div>
                </div>
            </form>
        </section>
        <!-- contact -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>イベント申し込み</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>

<script>
    $(function() {
        var number, total_numner;
        var max = 5; //合計最大数
        var $input = $(".f_num .num_txt"); //カウントする箇所
        var $plus = $(".f_num .num_plus"); //アップボタン
        var $minus = $(".f_num .num_min"); //ダウンボタン
        //合計カウント用関数
        function total() {
            total_numner = 0;
            $input.each(function(val) {
                val = Number($(this).val());
                total_numner += val;
            });
            return total_numner;
        }
        //ロード時
        $(window).on("load", function() {
            $input.each(function() {
                number = Number($(this).val());
                if (number == max) {
                    $(this).next($plus).prop("disabled", true);
                } else if (number == 0) {
                    $(this).prev($minus).prop("disabled", true);
                }
            });
            total();
            if (total_numner == max) {
                $plus.prop("disabled", true);
            } else {
                $plus.prop("disabled", false);
            }
        });
        //ダウンボタンクリック時
        $minus.on("click", function() {
            total();
            number = Number($(this).next($input).val());
            if (number > 0) {
                $(this)
                    .next($input)
                    .val(number - 1);
                if (number - 1 == 0) {
                    $(this).prop("disabled", true);
                }
                $(this).next().next($plus).prop("disabled", false);
            } else {
                $(this).prop("disabled", true);
            }
            total();
            if (total_numner < max) {
                $plus.prop("disabled", false);
            }
        });
        //アップボタンクリック時
        $plus.on("click", function() {
            number = Number($(this).prev($input).val());
            if (number < max) {
                $(this)
                    .prev($input)
                    .val(number + 1);
                if (number + 1 == max) {
                    $(this).prop("disabled", true);
                }
                $(this).prev().prev($minus).prop("disabled", false);
            } else {
                $(this).prop("disabled", true);
            }
            total();
            if (total_numner == max) {
                $plus.prop("disabled", true);
            } else {
                $plus.prop("disabled", false);
            }
        });
    });
</script>