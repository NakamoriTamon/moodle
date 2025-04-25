<?php
require_once('/var/www/html/moodle/config.php');
include('/var/www/html/moodle/custom/admin/app/Views/common/header.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once('/var/www/html/moodle/custom/admin/app/Controllers/management/paying_cush_controller.php');

$paying_cush_controller = new PayingCushController();
$results = $paying_cush_controller->index();

// 情報取得
$user_list = $results['user_list'] ?? [];
$tekijuku_commemoration = empty($results['tekijuku']) ? false : $results['tekijuku'];
$keyword = $results['keyword']  ?? '';
$fk_user_id = $results['fk_user_id']  ?? '';
$payment_type_list = $results['payment_type_list'] ?? [];

// old_inputがあれば値を取得
if (isset($_SESSION['old_input'])) {
    $old_input = $_SESSION['old_input'];
}

if ($tekijuku_commemoration !== false) {
    // 現在の日付を取得
    $current_date = new DateTime();
    $current_year = (int)$current_date->format('Y');

    // 決済状態を取得
    $paymentStatus = determinePaymentStatus($tekijuku_commemoration, $current_year);

    // フォーム要素を無効化する属性文字列を生成
    $disabledAttr = ($paymentStatus && !$paymentStatus['can_edit']) ? 'disabled' : '';
}

// エラーメッセージを取得
$errors = $_SESSION['errors'] ?? [];
$message_error = isset($_SESSION['message_error']) ? $_SESSION['message_error'] : null;

// セッション変数をクリア
unset($_SESSION['errors'], $_SESSION['old_input'], $_SESSION['message_error']);

$paid_status_list = [
    [
        'id' => 1,
        'status' => 'unpaid',
        'label' => '未決済',
        'can_edit' => true
    ],
    [
        'id' => 2,
        'status' => 'in-progress',
        'label' => '決済中',
        'can_edit' => false
    ],
    [
        'id' => 3,
        'status' => 'completed',
        'label' => '決済済',
        'can_edit' => true
    ]
];

// 決済状態を判定する関数
function determinePaymentStatus($tekijuku_commemoration, $current_fiscal_year)
{
    if ($tekijuku_commemoration === false) {
        return null; // 適塾記念会会員ではない
    }

    // デポジットフラグ
    $isDeposit = false;
    if ($current_fiscal_year >= 2024 && $current_fiscal_year <= 2030) {
        $deposit_column = "is_deposit_{$current_fiscal_year}";
        if (array_key_exists($deposit_column, $tekijuku_commemoration)) {
            $isDeposit = $tekijuku_commemoration[$deposit_column] == '1';
        }
    }

    // 支払日付の有効性チェック（現在の年度内かどうか）
    $hasPaidDate = false;
    if (!empty($tekijuku_commemoration['paid_date'])) {
        $paid_date = new DateTime($tekijuku_commemoration['paid_date']);

        $paid_deadline = TEKIJUKU_PAID_DEADLINE; // "mm-dd" 形式（例："04-01"）
        $current_date = date('Y-m-d');

        $hasPaidDate = false;
        // 年度の判定（支払期限日より前なら前年）
        if ($current_date < date('Y') . '-' . $paid_deadline) {
            $fiscal_start = new DateTime((date('Y') - 1) . '-' . $paid_deadline);
            $fiscal_end = new DateTime(date('Y') . '-' . date('m-d', strtotime($paid_deadline . ' -1 day')));
        } else {
            $fiscal_start = new DateTime(date('Y') . '-' . $paid_deadline);
            $fiscal_end = new DateTime((date('Y') + 1) . '-' . date('m-d', strtotime($paid_deadline . ' -1 day')));
        }

        if ($paid_date >= $fiscal_start && $paid_date <= $fiscal_end) {
            $hasPaidDate = true;
        }
    }

    // 決済状態の判定
    if (($isDeposit || $hasPaidDate)) {
        return [
            'status' => 'completed',
            'label' => '決済済',
            'can_edit' => true
        ];
    } elseif (!$hasPaidDate && !$isDeposit && $tekijuku_commemoration['paid_status'] == PAID_STATUS['PROCESSING']) {
        return [
            'status' => 'in-progress',
            'label' => '決済中',
            'can_edit' => false
        ];
    } elseif (!$hasPaidDate && !$isDeposit) {
        return [
            'status' => 'unpaid',
            'label' => '未決済',
            'can_edit' => true
        ];
    }

    // 万が一どの条件にも当てはまらない場合のデフォルト
    return [
        'status' => 'in-progress',
        'label' => '決済中(デフォルト)',
        'can_edit' => false
    ];
}
?>

<style>
    /* 決済状態表示用 */
    .payment-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.85em;
        margin-left: 10px;
    }

    .payment-status.unpaid {
        background-color: #f8d7da;
        color: #721c24;
    }

    .payment-status.in-progress {
        background-color: #fff3cd;
        color: #856404;
    }

    .payment-status.completed {
        background-color: #d4edda;
        color: #155724;
    }

    .readonly-select {
        background-color: #e9ecef;
        pointer-events: none;
        touch-action: none;
    }

    #university_member_fields {
        display: none;
    }

    #zip {
        display: inline;
        width: 95%;
    }
</style>

<body id="event" data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default" class="position-relative">
    <div class="wrapper">
        <?php include('/var/www/html/moodle/custom/admin/app/Views/common/sidebar.php'); ?>
        <div class="main">
            <div id="alert-container" class="position-fixed top-0 start-50 translate-middle-x" style="z-index: 1050; margin-top: 20px;"></div>
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <div class="navbar-collapse collapse">
                    <a class="sidebar-toggle js-sidebar-toggle">
                        <i class="hamburger align-self-center"></i>
                    </a>
                    <p class="title header-title ms-4 fs-4 fw-bold mb-0">適塾会費情報管理</p>
                    <ul class="navbar-nav navbar-align">
                        <li class="nav-item dropdown">
                            <a class="nav-icon pe-md-0 dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <div class="fs-5 me-4 text-decoration-underline"><?= htmlspecialchars($USER->name) ?></div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="/custom/admin/app/Views/login/login.php">Log out</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="content">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-body p-055">
                            <form id="form" method="POST" action="/custom/admin/app/Views/management/paying_cush.php" class="w-100">
                                <div class="mb-3">
                                    <label class="form-label" for="notyf-message">会員</label>
                                    <select id="fk_user_id" name="fk_user_id" class="form-control">
                                        <option value="">選択してください</option>
                                        <?php foreach ($user_list as $user) { ?>
                                            <option value=<?= $user['id'] ?> <?= isSelected($user['id'], $fk_user_id, $old_input['fk_user_id'] ?? null) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($user['id'] ? sprintf('%08d', $user['id']) : '') ?><?= htmlspecialchars('：' . $user['name']) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="d-flex w-100">
                                    <button class="btn btn-primary mb-3 me-0 ms-auto">検索</button>
                                </div>
                                <!-- <hr> -->
                            </form>
                        </div>
                    </div>
                    <?php if (!empty($tekijuku_commemoration)): ?>
                        <form id="usert_form" method="POST" action="/custom/admin/app/Controllers/management/paying_cush_upsert_controller.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0 mt-3">適塾記念会 会員情報<?php if (!empty($tekijuku_commemoration['id'])) {
                                                                                    echo " 編集";
                                                                                } else {
                                                                                    echo " 新規登録";
                                                                                } ?></h5>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="fk_user_id" value="<?= htmlspecialchars(isSetValue($fk_user_id, $old_input['fk_user_id'] ?? '')); ?>">
                                    <input type="hidden" name="tekijuku_commemoration_id" value=<?php echo htmlspecialchars($tekijuku_commemoration['id']) ?>>
                                    <input type="hidden" name="old_paid_status" value="<?= htmlspecialchars($tekijuku_commemoration['paid_status'] ?? 0) ?>">
                                    <input type="hidden" name="type_code" value="<?= htmlspecialchars($tekijuku_commemoration['type_code'] ?? 0) ?>">
                                    <input type="hidden" id="price_value" name="price" value="<?= htmlspecialchars($tekijuku_commemoration['price']) ?>" />
                                    <?php if (!empty($tekijuku_commemoration['id'])): ?>
                                        <div class="mb-3">
                                            <label class="form-label">会員番号: <?php echo htmlspecialchars($tekijuku_commemoration['number'] ? sprintf('%08d', $tekijuku_commemoration['number']) : ''); ?></label>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">会員種別: <?php if (!empty($tekijuku_commemoration['type_code'])) echo TYPE_CODE_LIST[$tekijuku_commemoration['type_code']] ?></label>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">支払い方法:
                                                <?php foreach ($payment_type_list as $payment_type): ?>
                                                    <?php if ($payment_type['id'] == $tekijuku_commemoration['payment_method']): ?>
                                                        <?= htmlspecialchars($payment_type['name']) ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </label>
                                        </div>
                                    <?php else: ?>
                                        <div class="mb-3">
                                            <label class="form-label">会員種別</label>
                                            <select name="type_code" id="type_code" class="form-control mb-3" onchange="updatePrice()">
                                                <option value=1 <?= isSelected(1, $old_input['type_code'] ?? null, null) ? 'selected' : '' ?>>普通会員</option>
                                                <option value=2 <?= isSelected(2, $old_input['type_code'] ?? null, null) ? 'selected' : '' ?>>賛助会員</option>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label class="form-label">本年度決済</label>
                                        <?php if ((int)$tekijuku_commemoration['is_delete'] == TEKIJUKU_COMMEMORATION_IS_DELETE['ACTIVE']): ?>
                                            <select id="paid_status" name="paid_status" class="form-control mb-3 <?php if ($paymentStatus['status'] == 'completed') { ?>readonly-select <?php } ?>">
                                                <?php foreach ($paid_status_list as $paid_status): ?>
                                                    <option value="<?= htmlspecialchars($paid_status['id']) ?>"
                                                        <?= isSelected($paid_status['id'], $old_input['paid_status'] ?? null, $tekijuku_commemoration['paid_status'] ?? null) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($paid_status['label']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                        <?php if ((int)$tekijuku_commemoration['is_delete'] === TEKIJUKU_COMMEMORATION_IS_DELETE['INACTIVE']) : ?>
                                            <div class="inactive-text">（退会済み）</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">お名前</label>
                                        <span class="badge bg-danger">必須</span>
                                        <div class="align-items-center">
                                            <input type="text" name="tekijuku_name" class="form-control" value="<?= htmlspecialchars($old_input['tekijuku_name'] ?? $tekijuku_commemoration['name']); ?>">
                                            <?php if (!empty($errors['tekijuku_name'])): ?>
                                                <div class=" text-danger mt-2"><?= htmlspecialchars($errors['tekijuku_name']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">フリガナ</label>
                                        <span class="badge bg-danger">必須</span>
                                        <div class="align-items-center">
                                            <input type="text" name="kana" class="form-control" value="<?= htmlspecialchars($old_input['kana'] ?? $tekijuku_commemoration['kana']) ?>">
                                            <?php if (!empty($errors['kana'])): ?>
                                                <div class=" text-danger mt-2"><?= htmlspecialchars($errors['kana']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">郵便番号（ハイフンなし）</label>
                                        <span class="badge bg-danger">必須</span>
                                        <div class="align-items-center">
                                            <div class="post_code" style="display: flex;">
                                                <input type="text" id="zip" name="post_code" class="form-control" maxlength="7" pattern="\d{7}"
                                                    value="<?= htmlspecialchars($old_input['post_code'] ?? $tekijuku_commemoration['post_code']) ?>"
                                                    pattern="[0-9]*" inputmode="numeric"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                                <button id="post_button" type="button" style="margin-right: 0; width: 80px;" onclick="fetchAddress()">住所検索</button>
                                            </div>
                                            <?php if (!empty($errors['post_code'])): ?>
                                                <div class="text-danger mt-2"><?= htmlspecialchars($errors['post_code']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">住所</label>
                                        <span class="badge bg-danger">必須</span>
                                        <div class="align-items-center">
                                            <input type="text" id="address" name="address" class="form-control" value="<?= htmlspecialchars($old_input['address'] ?? $tekijuku_commemoration['address']) ?>">
                                            <?php if (!empty($errors['address'])): ?>
                                                <div class=" text-danger mt-2"><?= htmlspecialchars($errors['address']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">電話番号（ハイフンなし）</label>
                                        <span class="badge bg-danger">必須</span>
                                        <div class="align-items-center">
                                            <input type="text" name="tell_number" class="form-control" maxlength="15"
                                                value="<?= htmlspecialchars($old_input['tell_number'] ?? $tekijuku_commemoration['tell_number']) ?>"
                                                pattern="[0-9]*" inputmode="numeric"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                            <?php if (!empty($errors['tell_number'])): ?>
                                                <div class=" text-danger mt-2"><?= htmlspecialchars($errors['tell_number']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">メールアドレス</label>
                                        <span class="badge bg-danger">必須</span>
                                        <div class="align-items-center">
                                            <input type="email" name="tekijuku_email" class="form-control" value="<?= htmlspecialchars($old_input['tekijuku_email'] ?? $tekijuku_commemoration['email']) ?>"
                                                inputmode="email"
                                                autocomplete="email"
                                                oninput="this.value = this.value.replace(/[^a-zA-Z0-9@._-]/g, '');">
                                            <?php if (!empty($errors['tekijuku_email'])): ?>
                                                <div class=" text-danger mt-2"><?= htmlspecialchars($errors['tekijuku_email']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (empty($tekijuku_commemoration['id'])): ?>
                                        <div class="mb-3">
                                            <label class="form-label">口数</label>
                                            <span class="badge bg-danger">必須</span>
                                            <div class="list_field f_num">
                                                <button type="button" class="num_min" style="margin-right: 0" onclick="updateUnitCount(-1)">ー</button>
                                                <input type="number" id="unit" name="unit" value="<?= htmlspecialchars($old_input['unit'] ?? 1) ?>" class="num_txt" onchange="updatePrice()" />
                                                <button type="button" class="num_plus" onclick="updateUnitCount(1)">＋</button>
                                                <?php if (!empty($errors['unit'])): ?>
                                                    <div class=" text-danger mt-2"><?= htmlspecialchars($errors['unit']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label class="form-label">金額:
                                            <span id="price"><?php if (!empty($tekijuku_commemoration['price'])): ?><?= htmlspecialchars(number_format($tekijuku_commemoration['price'])) ?><?php else: ?>0<?php endif; ?>円</span></label>
                                        <?php if (!empty($errors['price'])): ?>
                                            <div class=" text-danger mt-2"><?= htmlspecialchars($errors['price']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">備考</label>
                                        <div class="list_field f_txt">
                                            <textarea name="note" class="form-control" rows=5><?= htmlspecialchars($old_input['note'] ?? $tekijuku_commemoration['note']); ?></textarea>
                                            <?php if (!empty($errors['note'])): ?>
                                                <div class=" text-danger mt-2"><?= htmlspecialchars($errors['note']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <input class="checkbox_input" type="checkbox" name="is_university_member" id="is_university_member" value="1" <?php echo ($old_input['is_university_member'] ?? $tekijuku_commemoration['is_university_member']) == '1' ? 'checked' : ''; ?>>
                                        <label class="checkbox_label" id="is_university_member_label" for="is_university_member">大阪大学教職員の方はこちらにチェックしてください。</label>
                                    </div>
                                    <div id="university_member_fields">
                                        <div class="mb-3">
                                            <label class="form-label">所属部局（学部・研究科）</label>
                                            <div class="align-items-center">
                                                <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($old_input['department'] ?? $tekijuku_commemoration['department']); ?>">
                                                <?php if (!empty($errors['department'])): ?>
                                                    <div class="text-danger mt-2"><?= htmlspecialchars($errors['department']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">講座/部課/専攻名</label>
                                            <div class="align-items-center">
                                                <input type="text" name="major" value="<?= htmlspecialchars($old_input['major'] ?? $tekijuku_commemoration['major']); ?>">
                                                <?php if (!empty($errors['major'])): ?>
                                                    <div class="text-danger mt-2"><?= htmlspecialchars($errors['major']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">職名・学年</label>
                                            <div class="align-items-center">
                                                <input type="text" name="official" class="form-control" value="<?= htmlspecialchars($old_input['official'] ?? $tekijuku_commemoration['official']); ?>">
                                                <?php if (!empty($errors['official'])): ?>
                                                    <div class="text-danger mt-2"><?= htmlspecialchars($errors['official']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <input type="hidden" name="is_published" value="0">
                                        <input class="checkbox_input" type="checkbox" id="is_published" name="is_published" value="1" <?php echo ($old_input['is_published'] ?? $tekijuku_commemoration['is_published']) == '1' ? 'checked' : ''; ?>>
                                        <label class="checkbox_label" for="is_published">氏名掲載を許可します</label>
                                    </div>
                                    <div class="mb-3">
                                        <input type="submit" id="submit_btn" class="btn btn-primary" value="変更を確定する">
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    <script src="/custom/admin/public/js/app.js"></script>
</body>

</html>
<script>
    $(document).ready(function() {
        let submitted = false;
        $('#submit_btn').on('click', function(e) {
            e.preventDefault(); // デフォルトのsubmit動作を止める
            if (submitted) return; // すでに送信済みなら何もしない

            const paid_status = $('#paid_status').val();

            if (paid_status === '3') {
                if (!confirm('本年度の決済を決済済に変更します。決済済にすると本年度決済は変更できません。確定してよろしいですか？')) {
                    return;
                }
            }

            submitted = true;
            // submitイベントをトリガーするのではなく、ネイティブで送信
            document.getElementById('usert_form').submit();
        });
    });
    async function fetchAddress() {
        const zip = document.getElementById("zip").value; // スペースを削除
        if (!/^\d{7}$/.test(zip)) {
            alert("7桁の数字を入力してください");
            return;
        }

        try {
            const response = await fetch(`https://zipcloud.ibsnet.co.jp/api/search?zipcode=${zip}`);
            const data = await response.json();
            if (data.status === 200 && data.results) {
                document.getElementById("address").value = `${data.results[0].address1} ${data.results[0].address2} ${data.results[0].address3}`;
            } else {
                alert("住所が見つかりませんでした");
            }
        } catch (error) {
            alert("エラーが発生しました");
        }
    }

    const element = document.getElementById('fk_user_id');
    const choices = new Choices(element, {
        removeItemButton: true,
        searchEnabled: true,
        shouldSort: false
    });
    // 部分一致検索にする：searchChoices をオーバーライド
    choices.searchChoices = function(value) {
        if (!value) return;
        const escapedValue = value.toLowerCase();

        this._currentState.choices = this._store.getChoicesFilteredByActive().map(choice => {
            const haystack = String(choice.label || choice.value).toLowerCase();
            const match = haystack.includes(escapedValue); // ★部分一致に変更
            return {
                ...choice,
                score: match ? 1 : 0, // スコアを仮設定
                match
            };
        }).filter(choice => choice.match);

        this._highlightPosition = 0;
        this._renderChoices(this._currentState.choices, true);
    };

    // 会員種別ごとの単価
    const PRICE_REGULAR_MEMBER = 2000; // 普通会員単価
    const PRICE_SUPPORTING_MEMBER = 10000; // 賛助会員単価

    // 現在選ばれている会員種別の単価を決定
    let currentUnitPrice = PRICE_REGULAR_MEMBER;
    // 枚数が増減したときの処理
    function updateUnitCount(delta) {
        const unitInput = document.getElementById('unit');
        let currentCount = parseInt(unitInput.value) || 0;
        currentCount += delta;

        // 0未満にはならないように、1を最低枚数に設定
        if (currentCount < 1) currentCount = 1;

        unitInput.value = currentCount;

        // 金額の再計算
        updatePrice();
    }

    // 会員種別が変更されたときの処理
    function updatePrice() {
        // 会員種別の選択を取得
        const typeCode = document.getElementById('type_code').value;

        // 会員種別によって単価を決定
        if (typeCode == 1) {
            currentUnitPrice = PRICE_REGULAR_MEMBER;
        } else if (typeCode == 2) {
            currentUnitPrice = PRICE_SUPPORTING_MEMBER;
        }

        // 枚数を取得
        const unitCount = parseInt(document.getElementById('unit').value) || 0;

        // 金額を計算
        const totalPrice = currentUnitPrice * unitCount;

        // 金額欄に表示
        document.getElementById('price').textContent = totalPrice.toLocaleString() + "円";
        document.getElementById('price_value').value = totalPrice;
    }

    // ページ読み込み時に金額を初期化
    window.onload = updatePrice;


    // 大阪大学教職員・学生の方はこちらにチェックしてください。の処理
    document.addEventListener("DOMContentLoaded", function() {
        const checkbox = document.getElementById("is_university_member");
        const universityFields = document.getElementById("university_member_fields");

        // チェックボックスの状態に応じてフォームを表示/非表示
        checkbox.addEventListener("change", function() {
            if (checkbox.checked) {
                universityFields.style.display = "block"; // チェックされていれば表示
            } else {
                universityFields.style.display = "none"; // チェックされていなければ非表示
            }
        });

        // 初期状態の処理（ページがロードされたときにチェックが入っている場合）
        if (checkbox.checked) {
            universityFields.style.display = "block";
        }
    });
</script>