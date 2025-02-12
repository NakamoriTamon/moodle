<html>
<style>
    .area {
        display: flex;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    label {
        width: 100%;
        margin-bottom: 5px;
    }

    select,
    input,
    textarea {
        width: 300px
    }

    #post_button {
        margin-left: 5px;
    }

    .checkbox_input,
    .radio_input {
        width: 30px;
    }

    .checkbox_label {
        width: calc(100% - 50px);
    }

    .radio_label {
        width: 100px;
    }
</style>
<form method="POST" action="confirm.php">
    <div class="area">
        <label>会員種別</label>
        <select name="type_code">
            <option selected value=1>普通会員</option>
            <option value=2>賛助会員</option>
        </select>
    </div>
    <div class="area">
        <label>氏名</label>
        <input name="name">
    </div>
    <div class="area">
        <label>フリガナ</label>
        <input name="kana">
    </div>
    <div class="area">
        <label>性別</label>
        <select name="sex">
            <option selected value=1>男性</option>
            <option value=2>女性</option>
            <option value=3>その他</option>
        </select>
    </div>
    <div class="area">
        <label>郵便番号（ハイフンなし）</label>
        <input type="text" id="zip" name="post_code" maxlength="7" pattern="\d{7}" required>
        <button id="post_button" type="button" onclick="fetchAddress()">住所検索</button>
    </div>
    <div class="area">
        <label for="address">住所</label>
        <input type="text" id="address" name="address">
    </div>
    <div class="area">
        <label>電話番号</label>
        <input type="tel" name="tell_number">
    </div>
    <div class="area">
        <label>メールアドレス</label>
        <input type="email" name="email">
    </div>
    <div class="area">
        <label style="margin-bottom: 20px">支払方法</label>
        <div class="radio-group">
            <input class="radio_input" type="radio" name="payment_method" id="convenience" value=1 checked />
            <label class="radio_label" for="convenience">コンビニ決済</label>

            <input class="radio_input" type="radio" name="payment_method" id="credit" value=2 />
            <label class="radio_label" for="credit">クレジット</label>

            <input class="radio_input" type="radio" name="payment_method" id="bank" value=3 />
            <label class="radio_label" for="bank">銀行振込</label>
        </div>
    </div>
    <div class="area">
        <label>備考</label>
        <textarea name="note" rows=5></textarea>
    </div>

    <div class="area">
        <input class="checkbox_input" type="checkbox" name="is_published">
        <label class="checkbox_label">氏名掲載を許可します</label>
    </div>

    <button type="submit">登録する</button>

</form>

</html>

<script>
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
</script>