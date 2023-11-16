(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery'
        ], factory);
    } else {
        factory(window.jQuery);
    }
}(function ($) {
    'use strict';

    $.fn.satpMegamenu = function() {
        $(".navigation.satp-megamenu li.classic .submenu, .navigation.satp-megamenu li.staticwidth .submenu, .navigation.satp-megamenu li.classic .subchildmenu .subchildmenu").each(function(){
            $(this).css("left","-9999px");
            $(this).css("right","auto");
        });
        $(this).find("li.classic .subchildmenu > li.parent").mouseover(function(){
            var popup = $(this).children("ul.subchildmenu");
            var w_width = $(window).innerWidth();

            if(popup) {
                var pos = $(this).offset();
                var c_width = $(popup).outerWidth();
                if(w_width <= pos.left + $(this).outerWidth() + c_width) {
                    $(popup).css("left","auto");
                    $(popup).css("right","100%");
                    $(popup).css("border-radius","6px 0 6px 6px");
                } else {
                    $(popup).css("left","100%");
                    $(popup).css("right","auto");
                    $(popup).css("border-radius","0 6px 6px 6px");
                }
            }
        });
        $(this).find("li.staticwidth.parent,li.classic.parent").mouseover(function(){
            var popup = $(this).children(".submenu");
            var w_width = $(window).innerWidth();

            if(popup) {
                var pos = $(this).offset();
                var c_width = $(popup).outerWidth();
                if(w_width <= pos.left + $(this).outerWidth() + c_width) {
                    $(popup).css("left","auto");
                    $(popup).css("right","0");
                    $(popup).css("border-radius","6px 0 6px 6px");
                } else {
                    $(popup).css("left","0");
                    $(popup).css("right","auto");
                    $(popup).css("border-radius","0 6px 6px 6px");
                }
            }
        });
        $(this).find("li.parent.level0").hover(
            function () {
                $(this).addClass("active");
                $('.menu-overlay').addClass("active");
            },
            function () {
                $(this).removeClass("active");
                $('.menu-overlay').removeClass("active");
            }
        );
        $(".btn-close").off('click').on('click',function(e){
            $(".satp-megamenu.navigation li.level0").removeClass("active");
        });
        $(this).find("li.parent li.level1").mouseover(function(){
            $('.satp-megamenu.navigation .subchildmenu.mega-columns li.level1').removeClass('active');
            $(this).addClass('active');
        });
        $(window).resize(function(){
            $(".navigation.satp-megamenu li.classic .submenu, .navigation.satp-megamenu li.staticwidth .submenu, .navigation.satp-megamenu li.classic .subchildmenu .subchildmenu").each(function(){
                $(this).css("left","-9999px");
                $(this).css("right","auto");
            });
        });
        $(".nav-toggle").off('click').on('click',function(e){
            if(!$("html").hasClass("nav-open")) {
                $("html").addClass("nav-before-open");
                setTimeout(function(){
                    $("html").addClass("nav-open");
                }, 300);
            }
            else {
                $("html").removeClass("nav-open");
                setTimeout(function(){
                    $("html").removeClass("nav-before-open");
                }, 300);
            }
        });
        $("li.ui-menu-item > .open-children-toggle").off("click").on("click", function(){
            if(!$(this).parent().children(".submenu-toggle").hasClass("opened")) {
                $(this).parent().children(".submenu-toggle").addClass("opened");
                $(this).parent().children("a").addClass("ui-state-active");
            }
            else {
                $(this).parent().children(".submenu-toggle").removeClass("opened");
                $(this).parent().children("a").removeClass("ui-state-active");
            }
        });
    };
}));
