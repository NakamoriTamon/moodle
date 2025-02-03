<?php
  include('../layouts/header.php');
?>
<link rel="stylesheet" type="text/css" href="/custom/public/css/quest.css" />
    <main id="subpage">
        <section id="heading" class="inner_l">
            <h2 class="head_ttl" data-en="QUESTIONNAIRE">アンケート</h2>
        </section>

        <section id="quest" class="inner_l">
            <p class="quest_head">0000年00月00日 / 講座名が入ります講座名が入ります</p>
            <form method="" action="" class="whitebox quest_form">
                <div class="inner_s">
                    <div class="form_block form01">
                    <ul class="list">
                        <li>
                        <h4 class="sub_ttl">本日の講義内容について、ご意見・ご感想をお書きください。</h4>
                        <div class="list_field f_txtarea">
                            <textarea></textarea>
                        </div>
                        </li>
                        <li>
                        <h4 class="sub_ttl">
                            今までに大阪大学公開講座のプログラムに参加されたことはありますか
                        </h4>
                        <div class="list_field f_radio">
                            <label><input type="radio" id="" name="qust01" />ある</label>
                            <label><input type="radio" id="" name="qust01" />ない</label>
                        </div>
                        </li>
                    </ul>
                    </div>
                    <div class="form_block form02">
                    <p class="red">
                        <span>今回が初回受講の方は、以下の質問にすべてご回答ください。</span>
                    </p>
                    <p class="comment">※「その他」の欄にどこでご覧になったか具体的にご記載ください</p>
                    <ul class="list">
                        <li>
                        <h4 class="sub_ttl">
                            本日のプログラムをどのようにしてお知りになりましたか。
                            <span class="comment">※複数回答可</span>
                        </h4>
                        <div class="list_field f_check">
                            <div class="check_item">
                            <label><input type="checkbox" id="" />チラシ</label>
                            <span class="comment"
                                >※「その他」の欄にどこでご覧になったか具体的にご記載ください</span
                            >
                            </div>
                            <div class="check_item">
                            <label><input type="checkbox" id="" />ウェブサイト</label>
                            <span class="comment"
                                >※「その他」の欄にどこでご覧になったか具体的にご記載ください</span
                            >
                            </div>
                            <div class="check_item">
                            <label>
                                <input type="checkbox" id="" />大阪大学公開講座「知の広場」からのメール
                            </label>
                            </div>
                            <div class="check_item">
                            <label>
                                <input type="checkbox" id="" />SNS（X, Instagram, Facebookなど）
                            </label>
                            </div>
                            <div class="check_item">
                            <label>
                                <input type="checkbox" id="" />21世紀懐徳堂からのメールマガジン
                            </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />大阪大学卒業生メールマガジン </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />大阪大学入試課からのメール </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />Peatixからのメール </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />知人からの紹介 </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />講師・スタッフからの紹介 </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />Peatixからのメール </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />自治体の広報・掲示 </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />スマートニュース広告 </label>
                            </div>
                            <div class="other_item"><label> その他 </label><input type="text" id="" /></div>
                        </div>
                        </li>
                        <li>
                        <h4 class="sub_ttl">
                            本日のテーマを受講した理由は何ですか？
                            <span class="comment">※複数回答可</span>
                        </h4>
                        <div class="list_field f_check">
                            <div class="check_item">
                            <label><input type="checkbox" id="" />テーマに関心があったから</label>
                            </div>
                            <div class="check_item">
                            <label>
                                <input type="checkbox" id="" />本日のプログラム内容に関心があったから
                            </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />本日のゲストに関心があったから </label>
                            </div>
                            <div class="check_item">
                            <label>
                                <input type="checkbox" id="" />大阪大学のプログラムに参加したかったから
                            </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />教養を高めたいから </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />仕事に役立つと思われたから </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />日常生活に役立つと思われたから </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />余暇を有効に利用したかったから </label>
                            </div>
                            <div class="oher_item"><label> その他 </label><input type="text" id="" /></div>
                        </div>
                        </li>
                        <li>
                        <h4 class="sub_ttl">
                            本日のプログラムの満足度について、あてはまるもの1つをお選びください。
                        </h4>
                        <div class="list_field f_radio">
                            <label><input type="radio" id="" name="qust05" />非常に満足</label>
                            <label><input type="radio" id="" name="qust05" />満足</label
                            ><label><input type="radio" id="" name="qust05" />ふつう</label
                            ><label><input type="radio" id="" name="qust05" />不満</label
                            ><label><input type="radio" id="" name="qust05" />非常に不満</label>
                        </div>
                        </li>
                        <li>
                        <h4 class="sub_ttl">
                            本日のプログラムの理解度について、あてはまるもの1つをお選びください。
                        </h4>
                        <div class="list_field f_radio">
                            <label><input type="radio" id="" name="qust06" />よく理解できた</label>
                            <label><input type="radio" id="" name="qust06" />理解できた</label
                            ><label><input type="radio" id="" name="qust06" />ふつう</label
                            ><label><input type="radio" id="" name="qust06" />理解できなかった</label
                            ><label><input type="radio" id="" name="qust06" />全く理解できなかった</label>
                        </div>
                        </li>
                        <li>
                        <h4 class="sub_ttl">
                            本日のプログラムで特に良かった点について教えてください。<br />
                            以下にあてはまるものがあれば、一つお選びください。あてはまるものがなければ、「その他」の欄に記述してください。
                        </h4>
                        <div class="list_field f_check">
                            <div class="check_item">
                            <label>
                                <input type="checkbox" id="" />テーマについて考えを深めることができた
                            </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />最先端の研究について学べた </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />大学の研究者と対話ができた </label>
                            </div>
                            <div class="check_item">
                            <label> <input type="checkbox" id="" />大学の講義の雰囲気を味わえた </label>
                            </div>
                            <div class="check_item">
                            <label>
                                <input type="checkbox" id="" />大阪大学について知ることができた
                            </label>
                            </div>
                            <div class="check_item">
                            <label>
                                <input
                                type="checkbox"
                                id=""
                                />身の周りの社会課題に対する解決のヒントが得られた
                            </label>
                            </div>
                            <div class="oher_item"><label> その他 </label><input type="text" id="" /></div>
                        </div>
                        </li>
                        <li>
                        <h4 class="sub_ttl">
                            本日のプログラムの開催時間(○○分)について、あてはまるもの1つをお選びください。
                        </h4>
                        <div class="list_field f_radio">
                            <label><input type="radio" id="" name="qust08" />適当である</label>
                            <label><input type="radio" id="" name="qust08" />長すぎる</label>
                            <label><input type="radio" id="" name="qust08" />短すぎる</label>
                        </div>
                        </li>
                        <li>
                        <h4 class="sub_ttl">
                            本日のプログラムの開催環境について、あてはまるものを１つお選びください。
                            <span class="comment"
                            >※「あまり快適ではなかった」「全く快適ではなかった」と回答された方は次の質問にその理由を教えてください。</span
                            >
                        </h4>
                        <div class="list_field f_radio">
                            <label><input type="radio" id="" name="qust09" />とても快適だった</label>
                            <label><input type="radio" id="" name="qust09" />快適だった</label>
                            <label><input type="radio" id="" name="qust09" />ふつう</label>
                            <label><input type="radio" id="" name="qust09" />あまり快適ではなかった</label>
                            <label><input type="radio" id="" name="qust09" />全く快適ではなかった</label>
                        </div>
                        </li>
                        <li>
                        <h4 class="sub_ttl">
                            【問○】で「あまり快適ではなかった」「全く快適ではなかった」と回答された方はその理由を教えてください。
                        </h4>
                        <div class="list_field f_txtarea">
                            <textarea></textarea>
                        </div>
                        </li>
                        <li>
                        <h4 class="sub_ttl">
                            今後の大阪大学公開講座で希望するジャンルやテーマや話題があればご提案ください。
                        </h4>
                        <div class="list_field f_txtarea">
                            <textarea></textarea>
                        </div>
                        </li>
                        <li>
                        <h4 class="sub_ttl">
                            話を聞いてみたい大阪大学の教員や研究者がいれば、具体的にご提案ください。
                        </h4>
                        <div class="list_field f_txtarea">
                            <textarea></textarea>
                        </div>
                        </li>
                    </ul>
                    </div>
                    <div class="form_block form03">
                    <p class="red">
                        <span>以下、差し支えなければご回答ください。</span>
                    </p>
                    <ul class="list">
                        <li>
                        <h4 class="sub_ttl">ご職業等を教えてください。</h4>
                        <div class="list_field f_radio">
                            <label><input type="radio" id="" name="qust13" />高校生以下</label>
                            <label
                            ><input
                                type="radio"
                                id=""
                                name="qust13"
                            />学生（高校生、大学生、大学院生等）</label
                            >
                            <label><input type="radio" id="" name="qust13" />会社員</label>
                            <label><input type="radio" id="" name="qust13" />自営業・フリーランス</label>
                            <label><input type="radio" id="" name="qust13" />公務員</label>
                            <label><input type="radio" id="" name="qust13" />教職員</label>
                            <label><input type="radio" id="" name="qust13" />パート・アルバイト</label>
                            <label><input type="radio" id="" name="qust13" />主婦・主夫</label>
                            <label><input type="radio" id="" name="qust13" />定年退職</label>
                            <label><input type="radio" id="" name="qust13" />その他</label>
                        </div>
                        </li>
                        <li>
                        <h4 class="sub_ttl">性別をご回答ください。</h4>
                        <div class="list_field f_radio">
                            <label><input type="radio" id="" name="qust14" />女性</label>
                            <label><input type="radio" id="" name="qust14" />男性</label>
                            <label><input type="radio" id="" name="qust14" />その他</label>
                        </div>
                        </li>
                        <li>
                        <h4 class="sub_ttl">
                            お住いの地域を教えてください。
                            <span class="comment">※〇〇県△△市のようにご回答ください</span>
                        </h4>
                        <div class="list_field f_area">
                            <div class="area_item01">
                            <label>都道府県 </label>
                            <div class="select">
                                <select>
                                <option value=""></option>
                                </select>
                            </div>
                            </div>
                            <div class="area_item02">
                            <label>市町村 </label>
                            <input type="text" />
                            </div>
                        </div>
                        </li>
                    </ul>
                    </div>
                </div>
                <div class="box_btns">
                    <input type="submit" class="btn btn_red" value="アンケート内容を送信する" />
                </div>
            </form>
        </section>
    </main>

    <ul id="pankuzu" class="inner_l">
      <li><a href="/custom/app/Views/index.php">トップページ</a></li>
      <li>アンケート</li>
    </ul>

<?php
  include('../layouts/footer.php');
?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script src="../assets/common/js/common.js"></script>
</html>
