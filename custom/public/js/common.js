$(function () {
  $(".header_hbg").on("click", function () {
    $(this).toggleClass("open");
    $("nav").toggleClass("open");
    $("html").toggleClass("fix");
  });
});

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
