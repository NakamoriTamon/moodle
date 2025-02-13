<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<link rel="stylesheet" type="text/css" href="/custom/public/assets/css/guide.css" />

<main id="subpage">
    <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="ATTENDANCE GUIDE">受講ガイド</h2>
    </section>
    <!-- heading -->

    <div class="inner_l">
        <section id="guide">
            <div class="guide_block struct">
                <h2 class="block_ttl">受講形式について</h2>
                <p class="struct_txt">本学がこのサイトで募集する講義には、以下の形式があります。</p>
                <ul class="struct_cont">
                    <li>
                        <img src="/custom/public/assets/common/img/guide01.png" alt="会場での受講" />
                        <div class="info">
                            <p class="info_ttl">会場での受講</p>
                            <p class="info_desc sent">講義会場に直接ご来場いただいてからの受講になります。</p>
                        </div>
                    </li>
                    <li>
                        <img src="/custom/public/assets/common/img/guide02.png" alt="オンデマンド受講" />
                        <div class="info">
                            <p class="info_ttl">オンデマンド受講</p>
                            <p class="info_desc sent">
                                既にリアルタイムでの開催が終わった講義を収録したビデオを後日編集した動画を本プラットフォーム内にアップロードします。（リアルタイム開催の７日間～10日間後になります）
                            </p>
                        </div>
                    </li>
                    <li>
                        <img src="/custom/public/assets/common/img/guide03.png" alt="オンライン／ハイブリッド受講" />
                        <div class="info">
                            <p class="info_ttl">オンライン／ハイブリッド受講</p>
                            <p class="info_desc sent">
                                会場で開催中の講義をZoomやWebexなどのオンライン会議システムを通じて同時配信します。<br />
                                リアルタイムで行われている講義を場所を問わずどこでも受講が可能です。
                            </p>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="guide_block user">
                <h2 class="block_ttl">ユーザ登録について</h2>
                <ul class="guide_list">
                    <li class="sent">講義形式を問わず、必ずユーザー登録が必要となります</li>
                    <li class="sent">
                        ユーザー登録後、確認のメールがご登録時のメールアドレス宛に届きますので、表示されたURLリンクをクリックして承認してください。承認後、本登録が完了となります。
                    </li>
                    <li class="sent">
                        ユーザー登録は、13歳未満のお子様や13歳以上18歳未満の中高生でも登録が可能です。<br />
                        ※ただし、13歳未満の登録者は、必ず保護者の了承を得て、登録フォームの所定の欄に保護者名と連絡先電話番号を記入してください。
                    </li>
                </ul>
                <ul class="guide_notes sent">
                    <li>
                        ※ユーザーIDは、お一人の登録者様専用のものとなります。他の方とのIDの共有はご遠慮ください。
                    </li>
                    <li>
                        ※同じ登録者の方が複数のメールアドレスで登録することはご遠慮ください。（システム上で警告画面が表示されます
                    </li>
                </ul>
            </div>
            <div class="guide_block event">
                <h2 class="block_ttl">イベントのお申し込みについて</h2>
                <ul class="guide_list">
                    <li class="sent">
                        お申し込みを希望する講義のページより、「お申込み」ボタンをクリックしてください。
                    </li>
                    <li class="sent">
                        申し込み画面で、チケットの枚数（お申込みの人数）等の情報を入力してください。
                    </li>
                    <li class="sent">
                        申し込み内容確認画面が表示されます。「決済」ボタンをクリックすると、QRコードのデジタルチケットが表示されます。<br />
                        複数人分をお申込みの場合は、お連れ様のメールアドレスを入力していただくと、お連れ様宛にもお申込み完了、デジタルチケット（オンライン講義の場合は、URLの書かれた招待状）の案内がメールで送信されます。
                    </li>
                    <li>
                        支払いが完了後、「お申込みを受け付けました（後日確認要）」という表題のメールが届きます。また、講義の直前（〇日前）にもリマインドのご案内を送付いたします。
                    </li>
                </ul>
                <ul class="guide_notes sent">
                    <li>
                        ※なお、支払い方法に「コンビニ払い」を選択された方は、必ず72時間以内に代金をお支払いください。お支払いのない場合、お申込みは自動的にキャンセル扱いとなりますので、ご注意ください。
                    </li>
                </ul>
            </div>
        </section>
        <!-- faq -->
    </div>
</main>

<ul id="pankuzu" class="inner_l">
    <li><a href="../index.php">トップページ</a></li>
    <li>受講ガイド</li>
</ul>

<?php include('/var/www/html/moodle/custom/app/Views/common/footer.php'); ?>