<?php
include('/var/www/html/moodle/custom/app/Views/common/header.php');

unset($_SESSION['errors'], $_SESSION['old_input']); ?>
<?php $prefectures = PREFECTURES; ?>
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
            <form id="user_form" method="POST" action="/custom/app/Controllers/user/user_controller.php" class="whitebox form_cont">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="inner_m">
                    <?php if (!empty($basic_error)) { ?><p class="error"> <?= $basic_error ?></p><?php } ?>
                    <ul class="list">
                        <li class="list_item01 req">
                            <p class="list_label">お名前</p>
                            <div class="list_field f_txt">
                                <input type="text" name="name" value="<?= htmlspecialchars($old_input['name'] ?? '') ?>" />
                                <?php if (!empty($errors['name'])): ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['name']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item02 req">
                            <p class="list_label">フリガナ</p>
                            <div class="list_field f_txt">
                                <input type="text" name="kana" pattern="^[ァ-ヶー]+$" value="<?= htmlspecialchars($old_input['kana'] ?? '') ?>" />
                                <?php if (!empty($errors['kana'])): ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['kana']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item03 req">
                            <p class="list_label">お住いの都道府県</p>
                            <div class="list_field f_select select">
                                <select name="city">
                                    <option value="" disabled selected>都道府県を選択</option>
                                    <?php foreach ($prefectures as $prefecture) { ?>
                                        <option value="<?= htmlspecialchars($prefecture) ?>" <?= isSelected($prefecture, $old_input['city'] ?? null, null) ? 'selected' : '' ?>>
                                            <?= $prefecture ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <?php if (!empty($errors['city'])) { ?>
                                <div class="error-msg mt-2">
                                    <?= htmlspecialchars($errors['city']); ?>
                                </div>
                            <?php } ?>
                        </li>
                        <li class="list_item04 req">
                            <p class="list_label">メールアドレス</p>
                            <div class="list_field f_txt">
                                <input type="email" name="email" value="<?= htmlspecialchars($old_input['email'] ?? '') ?>" />
                                <?php if (!empty($errors['email'])) { ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['email']); ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </li>
                        <li class=" list_item05 req">
                            <p class="list_label">メールアドレス（確認用）</p>
                            <div class="list_field f_txt">
                                <input type="email" name="email_confirm" value="<?= htmlspecialchars($old_input['email_confirm'] ?? '') ?>"
                                    onpaste="return false" autocomplete="off" />
                                <?php if (!empty($errors['email_confirm'])): ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['email_confirm']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item06 req">
                            <p class="list_label">パスワード</p>
                            <div class="list_field f_txt">
                                <input type="password" name="password" />
                                <?php if (!empty($errors['password'])): ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['password']); ?>
                                    </div>
                                <?php endif; ?>
                                <p class="note">
                                    8文字以上20文字以内、数字・アルファベットを組み合わせてご入力ください。
                                </p>
                                <p class="note">使用できる記号!"#$%'()*+,-./:;<=>?@[¥]^_{|}~</p>
                            </div>
                        </li>
                        <li class="list_item07 req">
                            <p class="list_label">パスワード（確認用）</p>
                            <div class="list_field f_txt">
                                <input type="password" name="password_confirm" onpaste="return false" autocomplete="off" />
                                <?php if (!empty($errors['password_confirm'])): ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['password_confirm']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item09 req">
                            <p class="list_label">電話番号（ハイフンなし）</p>
                            <div class="list_field f_txt">
                                <input type="tel" name="phone" value="<?= htmlspecialchars($old_input['phone'] ?? '') ?>" />
                                <?php if (!empty($errors['phone'])): ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['phone']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item10 req">
                            <p class="list_label">生年月日</p>
                            <div class="list_field f_txt">
                                <input type="date" name="birthdate" value="<?= htmlspecialchars($old_input['birthdate'] ?? '') ?>" />
                                <?php if (!empty($errors['birthdate'])): ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['birthdate']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="list_item11">
                            <p class="list_label">お子様の氏名</p>
                            <div class="list_field f_txt">
                                <input type="text" name="child_name" value="<?= htmlspecialchars($old_input['child_name'] ?? '') ?>" />
                                <?php if (!empty($errors['child_name'])): ?>
                                    <div class="error-msg mt-2">
                                        <?= htmlspecialchars($errors['child_name']); ?>
                                    </div>
                                <?php endif; ?>
                                <p class="note">
                                    保護者が代理入力している場合記入してください。
                                </p>
                            </div>
                        </li>
                        <li class="list_item12 long_item">
                            <p class="list_label">備考</p>
                            <div class="list_field f_txtarea">
                                <textarea name="discription"><?= htmlspecialchars($old_input['discription'] ?? '') ?></textarea>
                            </div>
                        </li>
                        <div id="parents_input_area">
                            <li class="list_item11 req">
                                <p class="list_label">保護者の氏名</p>
                                <div class="list_field f_txt">
                                    <input type="text" name="guardian_name" value="<?= htmlspecialchars($old_input['guardian_name'] ?? '') ?>" />
                                    <?php if (!empty($errors['guardian_name'])): ?>
                                        <div class="error-msg mt-2">
                                            <?= htmlspecialchars($errors['guardian_name']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </li>
                            <li class="list_item12 req">
                                <p class="list_label">保護者メールアドレス</p>
                                <div class="list_field f_txt">
                                    <input type="email" name="guardian_email" value="<?= htmlspecialchars($old_input['guardian_email'] ?? '') ?>" />
                                    <?php if (!empty($errors['guardian_email'])): ?>
                                        <div class="error-msg mt-2">
                                            <?= htmlspecialchars($errors['guardian_email']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </li>
                            <li class="list_item13 req">
                                <p class="list_label">保護者電話番号 <br class="responsive_br">（ハイフンなし）</p>
                                <div class="list_field f_txt">
                                    <input type="tel" name="guardian_phone" value="<?= htmlspecialchars($old_input['guardian_phone'] ?? '') ?>" />
                                    <?php if (!empty($errors['guardian_phone'])): ?>
                                        <div class="error-msg mt-2">
                                            <?= htmlspecialchars($errors['guardian_phone']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        </div>
                        <div id="parents_check_area">
                            <div class="agree">
                                <p class="agree_txt">
                                    この会員登録は保護者の同意を得ています 。
                                </p>
                                <label for="parent_agree">
                                    <input type="checkbox" name="parent_agree" id="parent_agree" <?= !empty($old_input['parent_agree']) ? "checked" : ''; ?> />同意する
                                </label>
                            </div>
                        </div>
                    </ul>
                    <div class="agree">
                        <p class="agree_txt">
                            個人情報の提供について、大阪大学の個人情報保護に関する<a href="https://www.osaka-u.ac.jp/ja/misc/privacy.html">プライバシーポリシー</a>を確認し、同意します。
                        </p>
                        <label for="agree">
                            <input type="checkbox" name="agree" id="agree" <?= !empty($old_input['agree']) ? "checked" : ''; ?> />同意する
                        </label>
                    </div>
                    <div class="form_btn">
                        <input id="submit" type="submit" disabled class="btn btn_red" value="この内容で仮登録する" />
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

<script>
    $(document).ready(function() {
        $('input[name="birthdate"]').on('change', function() {
            const birthdate = $(this).val();
            displayRange(birthdate);
        });

        // 年齢計算
        function calculateAge(birthdate) {
            const birthDateObj = new Date(birthdate);
            const today = new Date();

            let age = today.getFullYear() - birthDateObj.getFullYear();
            const monthDiff = today.getMonth() - birthDateObj.getMonth();
            const dayDiff = today.getDate() - birthDateObj.getDate();

            // 誕生日がまだ来ていない場合、年齢を1引く
            if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
                age--;
            }

            return age;
        }

        // 処理中フラグ
        let processing = false;
        displayRange($('input[name="birthdate"]').val());

        function displayRange(birthdate) {
            $('#parents_input_area').css('display', 'none');
            $('#parents_check_area').css('display', 'none');
            if (birthdate) {
                const age = calculateAge(birthdate);
                if (age < 13) {
                    $('#parents_input_area').css('display', 'block');
                } else if (age < 19) {
                    $('#parents_check_area').css('display', 'block');
                }
            }
            // 同意チェック
            checkParentAgree();
        }

        // 初回の確認
        checkAgree();
        $(document).on('change', '#agree', checkAgree);
        $(document).on('change', '#parent_agree', checkParentAgree);

        // 利用規約同意チェック
        function checkAgree() {
            if (processing) return; // 処理中は再実行しない

            // 利用規約の同意がチェックされている場合
            if ($('#agree').prop('checked')) {
                checkParentAgree();
            } else {
                $('#submit').prop('disabled', true);
            }
        }

        // 保護者の同意チェック
        function checkParentAgree() {
            if (processing) return;

            if ($('#parents_check_area').css('display') !== 'none') {
                // 両方がチェックされている場合にボタンを有効化
                if ($('#agree').prop('checked') && $('#parent_agree').prop('checked')) {
                    $('#submit').prop('disabled', false); // 両方チェックされている場合は有効化
                } else {
                    $('#submit').prop('disabled', true);
                }
            } else if ($('#agree').prop('checked')) {
                $('#submit').prop('disabled', false);
            } else {
                $('#submit').prop('disabled', true);
            }
        }

        $('#user_form').on('submit', function() {
            $(this).find('input[type=submit]').prop('disabled', true); // 送信ボタンを無効化
        });
    });
</script>
</body>

</html>