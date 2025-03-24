// about-swiper
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
      slidesPerView: 4,
      spaceBetween: 60,
    },
  },
});

// 新着イベントswiper
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
      slidesPerView: 2.5,
      spaceBetween: 50,
    },
    1024: {
      slidesPerView: 3.5,
      spaceBetween: 50,
    },
  },
});

// mascot
$(document).ready(function () {
  var mascot = $("#mascot");
  var footer = $("#footer");

  $(window).on("scroll", function () {
    var scrollTop = $(window).scrollTop();
    var windowHeight = $(window).height();
    var footerTop = footer.offset().top;

    if (scrollTop + windowHeight > footerTop) {
      mascot.fadeOut(200);
    } else {
      mascot.fadeIn(200);
    }
  });
});
