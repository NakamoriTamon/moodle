<?php
  include('layouts/header.php');
?>
<link rel="stylesheet" type="text/css" href="/custom/public/css/home.css" />
  <main>
    <section id="mv">
      <img
        src="/custom/public/images/mv.png"
        alt="大阪大学 社会と未来、学びをつなぐ"
        class="mv_img nosp" />
      <img
        src="/custom/public/images/mv-sp.png"
        alt="大阪大学 社会と未来、学びをつなぐ"
        class="mv_img nopc" />
      <p class="mv_scroll nosp">SCROLL</p>
    </section>

    <section id="about">
      <div class="about_cont inner_l">
        <h2 class="ttl_home">
          <span class="en">ABOUT</span>
          大阪大学<br />「知の広場」とは？
        </h2>
        <p class="sent">
          緒方洪庵が江戸末期の大坂に開いた蘭学塾「適塾」には、福沢諭吉をはじめ全国から多くの塾生が集い、ともに切磋琢磨しながら学びました。<br />
          大阪大学「知の広場」は、大阪大学の精神的源流である適塾のように、大阪大学が主催する市民向け講座や子ども向けイベントなど、多様な学びに触れることのできる開かれた広場です。<br />
          地域・社会と大学、そして研究者と市民をつなぐことで、社会との共創を目指します。<br />
          あなたも、大阪大学が拓く学びの世界へ。
        </p>
      </div>
      <div class="swiper about_swiper01">
        <ul class="swiper-wrapper">
          <li class="swiper-slide"><img src="/custom/public/images/about_slide01.jpg" alt="" /></li>
          <li class="swiper-slide"><img src="/custom/public/images/about_slide02.jpg" alt="" /></li>
          <li class="swiper-slide"><img src="/custom/public/images/about_slide03.jpg" alt="" /></li>
          <li class="swiper-slide"><img src="/custom/public/images/about_slide04.jpg" alt="" /></li>
          <li class="swiper-slide"><img src="/custom/public/images/about_slide05.jpg" alt="" /></li>
          <li class="swiper-slide"><img src="/custom/public/images/about_slide06.jpg" alt="" /></li>
        </ul>
      </div>
      <div class="swiper about_swiper02">
        <ul class="swiper-wrapper">
          <li class="swiper-slide"><img src="/custom/public/svg/deco_text.svg" alt="UOsaka" /></li>
          <li class="swiper-slide"><img src="/custom/public/svg/deco_text.svg" alt="UOsaka" /></li>
        </ul>
      </div>
    </section>

    <section id="new">
      <div class="new_head inner_l">
        <h2 class="ttl_home">
          <span class="en">NEW ARRIVAL</span>
          新着イベント
        </h2>
        <a href="/custom/app/Views/event/index.php" class="btn btn_blue arrow nosp">全てのイベントを見る</a>
      </div>
      <div class="swiper new_swiper">
        <ul class="swiper-wrapper" id="event">
          <li class="swiper-slide event_item">
            <a href="/custom/app/Views/event/detail.php">
              <figure class="img"><img src="/custom/public/images/event01.jpg" alt="" /></figure>
              <div class="event_info">
                <p class="event_ttl">講義のタイトルが入ります講義のタイトルが入ります</p>
                <div class="event_sched">
                  <p class="term">開催日</p>
                  <div class="date">
                    <p class="dt01">1回目：2024年12月3日～12月11日</p>
                    <p class="dt02">2回目：2025年1月15日～1月28日</p>
                  </div>
                </div>
              </div>
            </a>
          </li>
          <li class="swiper-slide event_item">
            <a href="/custom/app/Views/event/detail.php">
              <figure class="img"><img src="/custom/public/images/event02.jpg" alt="" /></figure>
              <div class="event_info">
                <ul class="event_status">
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
          <li class="swiper-slide event_item">
            <a href="/custom/app/Views/event/detail.php">
              <figure class="img"><img src="/custom/public/images/event03.jpg" alt="" /></figure>
              <div class="event_info">
                <ul class="event_status">
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
          <li class="swiper-slide event_item">
            <a href="/custom/app/Views/event/detail.php">
              <figure class="img"><img src="/custom/public/images/event04.jpg" alt="" /></figure>
              <div class="event_info">
                <ul class="event_status">
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
          <li class="swiper-slide event_item">
            <a href="/custom/app/Views/event/detail.php">
              <figure class="img"><img src="/custom/public/images/event05.jpg" alt="" /></figure>
              <div class="event_info">
                <ul class="event_status">
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
                  <li>社会・経済</li>
                </ul>
              </div>
            </a>
          </li>
        </ul>
        <div class="new_btns">
          <div class="swiper-button-prev"></div>
          <div class="swiper-button-next"></div>
        </div>
      </div>
      <a href="/custom/app/Views/event/index.php" class="btn btn_blue arrow nopc">全てのイベントを見る</a>
    </section>

    <section id="search" class="inner_l">
      <h2 class="ttl_home">
        <span class="en">SEARCH</span>
        イベント検索
      </h2>
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
              <p class="term">開催形式</p>
              <div class="field f_check">
                <label><input type="checkbox" id="" />会場（対面）</label>
                <label><input type="checkbox" id="" />後日オンデマンド配信</label>
                <label><input type="checkbox" id="" />オンライン生配信</label>
              </div>
            </li>
            <li>
              <p class="term">対象</p>
              <div class="field f_check">
                <label><input type="checkbox" id="" />高校生以下</label>
                <label><input type="checkbox" id="" />一般向け</label>
                <label><input type="checkbox" id="" />子ども向け</label>
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
                      class="obj"></object>
                    <p class="txt">医療・健康</p>
                  </label>
                </div>
                <div class="cat_item category02">
                  <input type="checkbox" id="cat02" name="" />
                  <label for="cat02" class="cat_btn">
                    <object
                      type="image/svg+xml"
                      data="/custom/public/svg/icon_cat02.svg"
                      class="obj"></object>
                    <p class="txt">科学・技術</p>
                  </label>
                </div>
                <div class="cat_item category03">
                  <input type="checkbox" id="cat03" name="" />
                  <label for="cat03" class="cat_btn">
                    <object
                      type="image/svg+xml"
                      data="/custom/public/svg/icon_cat03.svg"
                      class="obj"></object>
                    <p class="txt">生活・福祉</p>
                  </label>
                </div>
                <div class="cat_item category04">
                  <input type="checkbox" id="cat04" name="" />
                  <label for="cat04" class="cat_btn">
                    <object
                      type="image/svg+xml"
                      data="/custom/public/svg/icon_cat04.svg"
                      class="obj"></object>
                    <p class="txt">文化・芸術</p>
                  </label>
                </div>
                <div class="cat_item category05">
                  <input type="checkbox" id="cat05" name="" />
                  <label for="cat05" class="cat_btn">
                    <object
                      type="image/svg+xml"
                      data="/custom/public/svg/icon_cat05.svg"
                      class="obj"></object>
                    <p class="txt">社会・経済</p>
                  </label>
                </div>
                <div class="cat_item category06">
                  <input type="checkbox" id="cat06" name="" />
                  <label for="cat06" class="cat_btn">
                    <object
                      type="image/svg+xml"
                      data="/custom/public/svg/icon_cat06.svg"
                      class="obj"></object>
                    <p class="txt">自然・環境</p>
                  </label>
                </div>
                <div class="cat_item category07">
                  <input type="checkbox" id="cat07" name="" />
                  <label for="cat07" class="cat_btn">
                    <object
                      type="image/svg+xml"
                      data="/custom/public/svg/icon_cat07.svg"
                      class="obj"></object>
                    <p class="txt">子ども・教育</p>
                  </label>
                </div>
                <div class="cat_item category08">
                  <input type="checkbox" id="cat08" name="" />
                  <label for="cat08" class="cat_btn">
                    <object
                      type="image/svg+xml"
                      data="/custom/public/svg/icon_cat08.svg"
                      class="obj"></object>
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

    <section id="juku">
      <div class="juku_cont inner_l">
        <div class="img"><img src="/custom/public/images/juku.png" alt="" /></div>
        <div class="desc">
          <h2 class="ttl_home">
            <span class="en">ABOUT TEKIJUKU</span>
            適塾記念会について
          </h2>
          <p class="sent">
            適塾記念センターでは、一般の方もご参加いただけるイベントを開催しております。<br />
            適塾に何度でも参観できたり、会員のみが参加できるイベントに参加できたり等の特典があります。
          </p>
          <a href="/custom/app/Views/juku/index.php" class="btn btn_blue arrow">詳しくはこちら</a>
        </div>
      </div>
    </section>
  </main>

<?php
  include('layouts/footer.php');
?>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
  <script src="/custom/public/js/common.js"></script>
  <script>
    const aboutSwiper01 = new Swiper(".about_swiper01", {
      speed: 10000,
      slidesPerView: 2,
      spaceBetween: 30,
      loop: true,
      centeredSlides: true,
      preventInteractionOnTransition: true,
      autoplay: {
        delay: 0,
      },
      breakpoints: {
        959: {
          slidesPerView: 3.8,
        },
      },
    });

    const aboutSwiper02 = new Swiper(".about_swiper02", {
      speed: 10000,
      slidesPerView: 1.2,
      spaceBetween: 45,
      loop: true,
      centeredSlides: true,
      preventInteractionOnTransition: true,
      autoplay: {
        delay: 0,
        reverseDirection: true,
      },
      breakpoints: {
        959: {
          slidesPerView: 3,
          spaceBetween: 60,
        },
      },
    });

    const newSwiper = new Swiper(".new_swiper", {
      speed: 500,
      slidesPerView: 1.2,
      spaceBetween: 15,
      loop: true,
      centeredSlides: true,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      breakpoints: {
        959: {
          slidesPerView: 3.5,
          spaceBetween: 50,
        },
      },
    });
  </script>