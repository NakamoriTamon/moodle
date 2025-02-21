<footer id="footer">
    <div class="footer_top">
        <div class="footer_cont inner_l">
            <p class="logo">
                <img
                    src="/custom/public/assets/common/img/logo_footer.svg"
                    alt="大阪大学「知の広場」ハンダイ市民講座" />
            </p>
            <div class="menu">
                <ul class="menu_top">
                    <li><a href="/custom/app/Views/index.php">ホーム</a></li>
                    <li><a href="/custom/app/Views/event/index.php">イベント一覧</a></li>
                    <li><a href="/custom/app/Views/guide/index.php">受講ガイド</a></li>
                    <li><a href="/custom/app/Views/faq/index.php">よくある質問</a></li>
                    <li><a href="/custom/app/Views/tekijuku/index.php">適塾記念会について</a></li>
                    <li><a href="/custom/app/Views/contact/index.php">お問い合わせ</a></li>
                    <!-- <li><a href="quest/index.html">アンケート</a></li> -->
                    <li><a href="/custom/app/Views/user/index.php">ユーザー登録</a></li>
                    <li><a href=<?= empty($login_id) ? "/custom/app/Views/login/index.php" : "/custom/app/Views/mypage/index.php" ?>>ログイン</a></li>
                </ul>
                <ul class="menu_btm">
                    <li><a href="">利用規約</a></li>
                    <li><a href="">プライバシーポリシー</a></li>
                    <li><a href="/custom/app/Views/regulate/index.php">特定商取引法に基づく表記</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer_btm">
        <p class="copy">Copyright &copy; 2009 OSAKA UNIVERSITY. All Rights Reserved.</p>
    </div>
</footer>
<!-- footer -->

<!-- JavaScript -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script src="/custom/public/assets/common/js/common.js"></script>