<head>
    <link rel="stylesheet" type="text/css" href="/custom/public/assets/css/footer.css" />
</head>
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
                    <li><a href=<?= empty($login_id) ? "/custom/app/Views/login/index.php" : "/custom/app/Views/logout/index.php" ?>><?=empty($login_id) ? 'ログイン' : 'ログアウト'?></a></li>
                        <?php if ($footre_tekijuku_commemoration->id !== 0 && !is_null($footre_tekijuku_commemoration->id) && $footre_tekijuku_commemoration->is_delete !== '1') :?>
                    <li><a href="javascript:void(0);" id="withdrawal-button">適塾記念会退会</a></li>
                    <?php endif; ?>
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
<script>
    $(document).ready(function() {
        var tekijuku_commemoration = <?php echo json_encode($footre_tekijuku_commemoration); ?>;
        // 退会ボタンクリック時
        $('#withdrawal-button').on('click', function() {
            showModalInactive('適塾記念会退会フォーム','退会後も期限内は利用が可能です。また退会すると元には戻せません。本当によろしいですか？');
        });

        // モーダルの「退会」ボタンがクリックされたとき
        $(document).on('click', '.inactive', function() {
            // APIを使って退会処理を実行
            $.ajax({
                url: '/custom/app/Controllers/tekijuku/tekijuku_index_controller.php',
                method: 'POST',
                data: {
                    id: tekijuku_commemoration.id,
                    post_kbn: 'tekijuku_delete'
                },
                success: function(response) {
                    alert('退会が完了しました。');
                    window.location.reload(); // ログイン状態をリセットした後、ページをリロード
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : '退会処理に失敗しました。';
                    alert(errorMessage);
                }
            });
        });
    });

    // モーダル表示
    function showModalInactive(title,message) {
        var modalHtml = `
            <div id="confirmation-modal">
                <div class="modal_cont">
                    <h2>${title}</h2>
                    <p>${message}</p>
                    <div class="modal-buttons">
                        <button class="modal-withdrawal inactive">退会</button>
                        <button class="modal-close">閉じる</button>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modalHtml);
        $('#confirmation-modal').show();
    }

    // モーダルの閉じるボタン
    $(document).on('click', '.modal-close', function() {
        $('#confirmation-modal').remove();
    });
</script>