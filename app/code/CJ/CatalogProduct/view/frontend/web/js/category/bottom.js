define([
    'jquery'
], function ($) {

    var wrap = $(".category-bottom");
    $(window).scroll(function () {
        if ($(window).scrollTop() + window.innerHeight >= $('.page-footer').offset().top) {
            wrap.addClass("footer-visible");
        } else {
            wrap.removeClass("footer-visible");
        }
    });
});
