//ハンバーガーメニュー
$(function () {
  $(".header_hbg").on("click", function () {
    $(this).toggleClass("open");
    $("nav").toggleClass("open");
    $("html").toggleClass("fix");
  });
});

//カテゴリボタン
$(function () {
  $(".cat_item input[type=checkbox]").on("click", function () {
    var btnObj = $(this).siblings("label").find(".obj")[0];
    if (!btnObj || !btnObj.contentDocument) return;
    var svgDoc = btnObj.contentDocument;
    var svg = $(svgDoc).find("svg");
    var icon = svg.find("#icon_path");
    icon.toggleClass("active");
  });
  $(".cat_item.active").each(function () {
    var btnObj = $(this).find(".obj")[0];
    if (!btnObj || !btnObj.contentDocument) return;
    var svgDoc = btnObj.contentDocument;
    var svg = $(svgDoc).find("svg");
    var icon = svg.find("#icon_path");
    if (icon) {
      icon.addClass("active");
    }
  });
});

//モーダル
$(".big").on("click", function () {
  srlpos = $(window).scrollTop();
  let imgSrc = $(this).closest(".desc_img").find(".img img").attr("src");
  $("#modal .modal_cont .img").attr("src", imgSrc);
  $("#modal").fadeIn();
  $("body").addClass("modal_fix").css({ top: -srlpos });
});
$(".js_close").on("click", function () {
  $("#modal").fadeOut();
  $("body").removeClass("modal_fix").css({ top: 0 });
  $(window).scrollTop(srlpos);
});
