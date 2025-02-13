<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/form.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="CONFIRM APPLICATION DETAILS">申し込み内容確認</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="form" class="event confirm">
            <ul id="flow">
                <li>入力</li>
                <li class="active">確認</li>
                <li>完了</li>
            </ul>
            <!-- 一旦申し込んだイベントリストへ飛ばす -->
            <form method="" action="register.php" class="whitebox form_cont">
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
                            <p class="list_field">1枚</p>
                        </li>
                        <li class="list_item05">
                            <p class="list_label">金額</p>
                            <p class="list_field f_txt">0,000円</p>
                        </li>
                        <li class="list_item06 req">
                            <p class="list_label">お支払方法</p>
                            <p class="list_field">項目A</p>
                        </li>
                        <li class="list_item07">
                            <p class="list_label">複数チケット申し込みの場合お連れ様のメールアドレス</p>
                            <p class="list_field">abcdefgh@gmail.com</p>
                        </li>
                        <li class="list_item08 req">
                            <p class="list_label">
                                今後大阪大学からのメールによるイベントのご案内を希望されますか？
                            </p>
                            <p class="list_field">希望する</p>
                        </li>
                        <li class="list_item09 long_item">
                            <p class="list_label">
                                本イベントはどのようにお知りになりましたか？<span>※複数選択可</span>
                            </p>
                            <p class="list_field">項目A、項目B</p>
                        </li>
                        <li class="list_item10 long_item">
                            <p class="list_label">備考欄</p>
                            <p class="list_field">
                                テキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキスト
                            </p>
                        </li>
                    </ul>
                    <p class="cancel">申し込み後のキャンセル（返金）はできません。</p>
                    <div class="form_btn">
                        <input type="submit" class="btn btn_red" value="決済画面へ進む" />
                        <input type="button" class="btn btn_gray" value="内容を修正する" onclick="location.href='apply.php';" />
                    </div>
                </div>
            </form>
        </section>
        <!-- contact -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>申し込み内容確認</li>
</ul>