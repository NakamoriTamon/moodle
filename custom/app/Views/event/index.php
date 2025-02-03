<?php
  include('../layouts/header.php');
?>
<link rel="stylesheet" type="text/css" href="/custom/public/css/event.css" />
    <main id="subpage">
      <section id="heading" class="inner_l">
        <h2 class="head_ttl" data-en="EVENT LIST">イベント一覧</h2>
      </section>

      <div class="inner_l">
        <section id="search">
          <h3 class="ttl_event">絞り込み検索</h3>
          <form method="" action="" id="search_cont" class="whitebox">
            <div class="inner_s">
              <ul class="search_list">
                <li>
                  <p class="term">開催ステータス</p>
                  <div class="field f_check">
                    <label><input type="checkbox" id="" />開催前</label>
                    <label><input type="checkbox" id="" />開催中</label>
                    <label><input type="checkbox" id="" />開催終了</label>
                  </div>
                </li>
                <li>
                  <p class="term">
                    申し込み<br />
                    ステータス
                  </p>
                  <div class="field f_check">
                    <label><input type="checkbox" id="" />受付前</label>
                    <label><input type="checkbox" id="" />受付中</label>
                    <label><input type="checkbox" id="" />受付終了</label>
                    <label><input type="checkbox" id="" />申し込み不要</label>
                  </div>
                </li>

                <li>
                  <p class="term">キーワード</p>
                  <div class="field f_txt">
                    <input type="text" placeholder="検索するキーワードを入力" />
                  </div>
                </li>
                <li>
                  <p class="term">開催日時</p>
                  <div class="field f_date">
                    <p class="date_wrap">
                      <input type="text" placeholder="年/月/日" />
                    </p>
                    <span>～</span>
                    <p class="date_wrap">
                      <input type="text" placeholder="年/月/日" />
                    </p>
                  </div>
                </li>
                <li>
                  <p class="term">カテゴリー</p>
                  <div class="field" id="category">
                    <div class="cat_item category01">
                      <input type="checkbox" id="cat01" name="" />
                      <label for="cat01" class="cat_btn">
                        <object
                          type="image/svg+xml"
                          data="/custom/public/svg/icon_cat01.svg"
                          class="obj"
                        ></object>
                        <p class="txt">医療・健康</p>
                      </label>
                    </div>
                    <div class="cat_item category02">
                      <input type="checkbox" id="cat02" name="" />
                      <label for="cat02" class="cat_btn">
                        <object
                          type="image/svg+xml"
                          data="/custom/public/svg/icon_cat02.svg"
                          class="obj"
                        ></object>
                        <p class="txt">科学・技術</p>
                      </label>
                    </div>
                    <div class="cat_item category03">
                      <input type="checkbox" id="cat03" name="" />
                      <label for="cat03" class="cat_btn">
                        <object
                          type="image/svg+xml"
                          data="/custom/public/svg/icon_cat03.svg"
                          class="obj"
                        ></object>
                        <p class="txt">生活・福祉</p>
                      </label>
                    </div>
                    <div class="cat_item category04">
                      <input type="checkbox" id="cat04" name="" />
                      <label for="cat04" class="cat_btn">
                        <object
                          type="image/svg+xml"
                          data="/custom/public/svg/icon_cat04.svg"
                          class="obj"
                        ></object>
                        <p class="txt">文化・芸術</p>
                      </label>
                    </div>
                    <div class="cat_item category05">
                      <input type="checkbox" id="cat05" name="" />
                      <label for="cat05" class="cat_btn">
                        <object
                          type="image/svg+xml"
                          data="/custom/public/svg/icon_cat05.svg"
                          class="obj"
                        ></object>
                        <p class="txt">社会・経済</p>
                      </label>
                    </div>
                    <div class="cat_item category06">
                      <input type="checkbox" id="cat06" name="" />
                      <label for="cat06" class="cat_btn">
                        <object
                          type="image/svg+xml"
                          data="/custom/public/svg/icon_cat06.svg"
                          class="obj"
                        ></object>
                        <p class="txt">自然・環境</p>
                      </label>
                    </div>
                    <div class="cat_item category07">
                      <input type="checkbox" id="cat07" name="" />
                      <label for="cat07" class="cat_btn">
                        <object
                          type="image/svg+xml"
                          data="/custom/public/svg/icon_cat07.svg"
                          class="obj"
                        ></object>
                        <p class="txt">子ども・教育</p>
                      </label>
                    </div>
                    <div class="cat_item category08">
                      <input type="checkbox" id="cat08" name="" />
                      <label for="cat08" class="cat_btn">
                        <object
                          type="image/svg+xml"
                          data="/custom/public/svg/icon_cat08.svg"
                          class="obj"
                        ></object>
                        <p class="txt">国際・言語</p>
                      </label>
                    </div>
                    <div class="cat_item category09">
                      <input type="checkbox" id="cat09" name="" />
                      <label for="cat09" class="cat_btn">
                        <p class="txt">その他</p>
                      </label>
                    </div>
                  </div>
                </li>
              </ul>
              <div class="search_btn">
                <button type="button" class="btn btn_clear">クリア</button>
                <button type="submit" class="btn btn_red">検索する</button>
              </div>
            </div>
          </form>
        </section>

        <section id="result">
          <h3 class="ttl_event">検索結果 ○○○○</h3>
          <ul class="result_list" id="event">
            <li class="event_item">
              <a href="/custom/app/Views/event/detail.php">
                <figure class="img"><img src="/custom/public/images/event01.jpg" alt="" /></figure>
                <div class="event_info">
                  <ul class="event_status">
                    <li class="no">開催前</li>
                    <li class="no">申し込み不要</li>
                  </ul>
                  <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                  <div class="event_sched">
                    <p class="term">開催日</p>
                    <div class="date">
                      <p class="dt01">1回目：2024年12月3日～12月11日</p>
                      <p class="dt02">2回目：2025年1月15日～1月28日</p>
                    </div>
                  </div>
                  <ul class="event_category">
                    <li>文化・芸術</li>
                  </ul>
                </div>
              </a>
            </li>
            <li class="event_item">
              <a href="/custom/app/Views/event/detail.php">
                <figure class="img"><img src="/custom/public/images/event02.jpg" alt="" /></figure>
                <div class="event_info">
                  <ul class="event_status">
                    <li class="end">開催終了</li>
                    <li class="end">受付終了</li>
                  </ul>
                  <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                  <div class="event_sched">
                    <p class="term">開催日</p>
                    <div class="date">
                      <p class="dt01">1回目：2024年12月3日～12月11日</p>
                      <p class="dt02">2回目：2025年1月15日～1月28日</p>
                    </div>
                  </div>
                  <ul class="event_category">
                    <li>医療・健康</li>
                    <li>生活・福祉</li>
                  </ul>
                </div>
              </a>
            </li>
            <li class="event_item">
              <a href="/custom/app/Views/event/detail.php">
                <figure class="img"><img src="/custom/public/images/event03.jpg" alt="" /></figure>
                <div class="event_info">
                  <ul class="event_status">
                    <li class="no">開催前</li>
                    <li class="active">受付中</li>
                  </ul>
                  <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                  <div class="event_sched">
                    <p class="term">開催日</p>
                    <div class="date">
                      <p class="dt01">1回目：2024年12月3日～12月11日</p>
                      <p class="dt02">2回目：2025年1月15日～1月28日</p>
                    </div>
                  </div>
                  <ul class="event_category">
                    <li>その他</li>
                  </ul>
                </div>
              </a>
            </li>
            <li class="event_item">
              <a href="/custom/app/Views/event/detail.php">
                <figure class="img"><img src="/custom/public/images/event04.jpg" alt="" /></figure>
                <div class="event_info">
                  <ul class="event_status">
                    <li class="active">開催中</li>
                    <li class="active">受付中</li>
                  </ul>
                  <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                  <div class="event_sched">
                    <p class="term">開催日</p>
                    <div class="date">
                      <p class="dt01">1回目：2024年12月3日～12月11日</p>
                      <p class="dt02">2回目：2025年1月15日～1月28日</p>
                    </div>
                  </div>
                  <ul class="event_category">
                    <li>自然・環境</li>
                    <li>社会・経済</li>
                  </ul>
                </div>
              </a>
            </li>
            <li class="event_item">
              <a href="/custom/app/Views/event/detail.php">
                <figure class="img"><img src="/custom/public/images/event05.jpg" alt="" /></figure>
                <div class="event_info">
                  <ul class="event_status">
                    <li class="active">受付中</li>
                    <li class="end">受付終了</li>
                  </ul>
                  <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                  <div class="event_sched">
                    <p class="term">開催日</p>
                    <div class="date">
                      <p class="dt01">1回目：2024年12月3日～12月11日</p>
                      <p class="dt02">2回目：2025年1月15日～1月28日</p>
                    </div>
                  </div>
                  <ul class="event_category">
                    <li>社会・経済</li>
                  </ul>
                </div>
              </a>
            </li>
            <li class="event_item">
              <a href="/custom/app/Views/event/detail.php">
                <figure class="img"><img src="/custom/public/images/event01.jpg" alt="" /></figure>
                <div class="event_info">
                  <ul class="event_status">
                    <li class="no">開催前</li>
                    <li class="no">申し込み不要</li>
                  </ul>
                  <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                  <div class="event_sched">
                    <p class="term">開催日</p>
                    <div class="date">
                      <p class="dt01">1回目：2024年12月3日～12月11日</p>
                      <p class="dt02">2回目：2025年1月15日～1月28日</p>
                    </div>
                  </div>
                  <ul class="event_category">
                    <li>文化・芸術</li>
                  </ul>
                </div>
              </a>
            </li>
            <li class="event_item">
              <a href="/custom/app/Views/event/detail.php">
                <figure class="img"><img src="/custom/public/images/event02.jpg" alt="" /></figure>
                <div class="event_info">
                  <ul class="event_status">
                    <li class="end">開催終了</li>
                    <li class="end">受付終了</li>
                  </ul>
                  <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                  <div class="event_sched">
                    <p class="term">開催日</p>
                    <div class="date">
                      <p class="dt01">1回目：2024年12月3日～12月11日</p>
                      <p class="dt02">2回目：2025年1月15日～1月28日</p>
                    </div>
                  </div>
                  <ul class="event_category">
                    <li>医療・健康</li>
                    <li>生活・福祉</li>
                  </ul>
                </div>
              </a>
            </li>
            <li class="event_item">
              <a href="/custom/app/Views/event/detail.php">
                <figure class="img"><img src="/custom/public/images/event03.jpg" alt="" /></figure>
                <div class="event_info">
                  <ul class="event_status">
                    <li class="no">開催前</li>
                    <li class="active">受付中</li>
                  </ul>
                  <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                  <div class="event_sched">
                    <p class="term">開催日</p>
                    <div class="date">
                      <p class="dt01">1回目：2024年12月3日～12月11日</p>
                      <p class="dt02">2回目：2025年1月15日～1月28日</p>
                    </div>
                  </div>
                  <ul class="event_category">
                    <li>その他</li>
                  </ul>
                </div>
              </a>
            </li>
            <li class="event_item">
              <a href="/custom/app/Views/event/detail.php">
                <figure class="img"><img src="/custom/public/images/event04.jpg" alt="" /></figure>
                <div class="event_info">
                  <ul class="event_status">
                    <li class="active">開催中</li>
                    <li class="active">受付中</li>
                  </ul>
                  <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                  <div class="event_sched">
                    <p class="term">開催日</p>
                    <div class="date">
                      <p class="dt01">1回目：2024年12月3日～12月11日</p>
                      <p class="dt02">2回目：2025年1月15日～1月28日</p>
                    </div>
                  </div>
                  <ul class="event_category">
                    <li>自然・環境</li>
                    <li>社会・経済</li>
                  </ul>
                </div>
              </a>
            </li>
            <li class="event_item">
              <a href="/custom/app/Views/event/detail.php">
                <figure class="img"><img src="/custom/public/images/event05.jpg" alt="" /></figure>
                <div class="event_info">
                  <ul class="event_status">
                    <li class="active">受付中</li>
                    <li class="end">受付終了</li>
                  </ul>
                  <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                  <div class="event_sched">
                    <p class="term">開催日</p>
                    <div class="date">
                      <p class="dt01">1回目：2024年12月3日～12月11日</p>
                      <p class="dt02">2回目：2025年1月15日～1月28日</p>
                    </div>
                  </div>
                  <ul class="event_category">
                    <li>社会・経済</li>
                  </ul>
                </div>
              </a>
            </li>
            <li class="event_item">
              <a href="/custom/app/Views/event/detail.php">
                <figure class="img"><img src="/custom/public/images/event01.jpg" alt="" /></figure>
                <div class="event_info">
                  <ul class="event_status">
                    <li class="no">開催前</li>
                    <li class="no">申し込み不要</li>
                  </ul>
                  <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                  <div class="event_sched">
                    <p class="term">開催日</p>
                    <div class="date">
                      <p class="dt01">1回目：2024年12月3日～12月11日</p>
                      <p class="dt02">2回目：2025年1月15日～1月28日</p>
                    </div>
                  </div>
                  <ul class="event_category">
                    <li>文化・芸術</li>
                  </ul>
                </div>
              </a>
            </li>
            <li class="event_item">
              <a href="/custom/app/Views/event/detail.php">
                <figure class="img"><img src="/custom/public/images/event02.jpg" alt="" /></figure>
                <div class="event_info">
                  <ul class="event_status">
                    <li class="end">開催終了</li>
                    <li class="end">受付終了</li>
                  </ul>
                  <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                  <div class="event_sched">
                    <p class="term">開催日</p>
                    <div class="date">
                      <p class="dt01">1回目：2024年12月3日～12月11日</p>
                      <p class="dt02">2回目：2025年1月15日～1月28日</p>
                    </div>
                  </div>
                  <ul class="event_category">
                    <li>医療・健康</li>
                    <li>生活・福祉</li>
                  </ul>
                </div>
              </a>
            </li>
          </ul>
          <ul class="result_pg">
            <li><a href="" class="prev"></a></li>
            <li><a href="" class="num active">1</a></li>
            <li><a href="" class="num">2</a></li>
            <li><a href="" class="num">3</a></li>
            <li><a href="" class="num">4</a></li>
            <li><a href="" class="num">5</a></li>
            <li><a href="" class="next"></a></li>
          </ul>
        </section>
      </div>
    </main>

    <ul id="pankuzu" class="inner_l">
      <li><a href="/custom/app/Views/index.php">トップページ</a></li>
      <li>イベント一覧</li>
    </ul>

<?php
  include('../layouts/footer.php');
?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script src="../assets/common/js/common.js"></script>
</html>
