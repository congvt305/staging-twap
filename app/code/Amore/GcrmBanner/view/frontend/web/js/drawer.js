define(['jquery'], function ($) {

    // custom promo drawer plug-in

// allow hand scolling
    function scrollPromoSlider() {
        var slider = document.querySelector("#promoDealsSlider");
        var isDown = false;
        var startX;
        var scrollLeft;
        slider.addEventListener("mousedown", function (e) {
            isDown = true;
            slider.classList.add("active");
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });
        slider.addEventListener("mouseleave", function () {
            isDown = false;
            slider.classList.remove("active");
        });
        slider.addEventListener("mouseup", function (e) {
            isDown = false;
            slider.classList.remove("active");
            e.preventDefault();
        });
        slider.addEventListener("mousemove", function (e) {
            if (!isDown) return;
            e.preventDefault();
            var x = e.pageX - slider.offsetLeft;
            var walk = (x - startX) * 1.3; //scroll-fast

            slider.scrollLeft = scrollLeft - walk;
        });
    }

    (function (global, $) {
        $.fn.promoDeal = function (options) {
            // Default options
            var settings = $.extend(
                {
                    titleBkgColor: "black",
                    titleColor: "white",
                    title: "Deals",
                    promos: [],
                    //	css:'promoCSS.css',
                    //	css: 'https://stage.lechateau.com/style/html/promosDeal/promoCSS.css',
                    number: false,
                    singleLink: false,
                    noTrack: false,
                    arrows: false,
                    margin: 12,
                    variableWidth: false,
                    height: false, // number
                    mobHeight: false, // number or units
                    callback: function () {}
                },
                options
            );

            var nbs = settings.promos.length; // numbers of promos

            // if promos
            if (nbs == 0) {
                return;
            }

            // custom styles
            var styles =
                "#promosDrawer .promos-container .promos-header {background-color:" +
                settings.titleBkgColor +
                ";}";
            styles +=
                "#promosDrawer .promos-container .promos-header span,  #promosDrawer .promos-container .promos-header.up span:after {color:" +
                settings.titleColor +
                ";}";

            // Append the default CSS styles
            if (settings.css) {
                $("head").append(
                    '<link rel="stylesheet" type="text/css" href="' + settings.css + '">'
                );
            }

            // Add custom styles
            $("head").append('<style type="text/css">' + styles + "</style>");

            // overide css styles
            if (settings.height != false) {
                if (isNaN(settings.height) == false) {
                    settings.height = settings.height + "px";
                }

                $("head").append(
                    '<style type="text/css">#promosDrawer .promos-container.up{height:' +
                    settings.height +
                    ";}</style>"
                );
                $("head").append(
                    '<style type="text/css">#promosDrawer .promos-wrap #promoDealsSlider .single-promo{line-height:' +
                    settings.height +
                    ";}</style>"
                );
            }

            // mobile height
            if (settings.mobHeight != false) {
                if (isNaN(settings.mobHeight) == false) {
                    settings.mobHeight = settings.mobHeight + "px";
                }
                $("head").append(
                    '<style type="text/css">@media (max-width: 591px){#promosDrawer .promos-container.up{height:' +
                    settings.mobHeight +
                    ";}}</style>"
                );
                $("head").append(
                    '<style type="text/css">@media (max-width: 591px){#promosDrawer .promos-wrap #promoDealsSlider .single-promo{line-height:' +
                    settings.mobHeight +
                    ";}}</style>"
                );
            }

            // append the promo
            this.append("<div id='promosDrawer' style='opacity:0;'></div>");

            // append container + background
            $("#promosDrawer").append("<div class='promos-container'></div>");
            $("#promosDrawer").append("<div class='promos-overlay'></div>");

            // promo container
            $("#promosDrawer .promos-container").append(
                '<div class="promos-header"><span>' + settings.title + "</span></div>"
            );
            $("#promosDrawer .promos-container").append(
                '<div class="promos-wrap"></div>'
            );

            // append the slider div
            $("#promosDrawer .promos-container .promos-wrap").append(
                '<div id="promoDealsSlider"></div>'
            );

            //	$("#promosDrawer .promos-container .promos-wrap").append('<div id="dotsPromoDeal"></div>');

            // hide the tracking
            if (settings.noTrack == true) {
                $("#promosDrawer .promos-container .promos-wrap").append(
                    '<div id="coverUpTrack"></div>'
                );
            }

            // promos loop
            for (var i = 0; i < nbs; i++) {
                var slide = settings.promos[i];
                var txt = '<div class="single-promo">';
                if (slide.link) {
                    txt += '<a href="' + slide.link + '" title="' + slide.alt + '">';
                }
                // mobile img
                if (slide.mob) {
                    txt +=
                        '<img class="only-desktop" src="' +
                        slide.img +
                        '" alt="' +
                        slide.alt +
                        '" />';
                    txt +=
                        '<img class="only-mobile" src="' +
                        slide.mob +
                        '" alt="' +
                        slide.alt +
                        '" />';
                } else {
                    txt += '<img src="' + slide.img + '" alt="' + slide.alt + '" />';
                }
                if (slide.link) {
                    txt += "</a>";
                }
                txt += "</div>";

                $("#promoDealsSlider").append(txt);
            } // @end loop

            // if title longer than 30 caracters add special class
            if (settings.title.length > 20 && settings.title.length <= 55) {
                $("#promosDrawer .promos-container .promos-header span").addClass(
                    "long-title"
                );
            } else if (settings.title.length > 55) {
                $("#promosDrawer .promos-container .promos-header span").addClass(
                    "very-long-title"
                );
            }

            if (settings.singleLink == true) {
                $(".promos-header").click(function () {
                    window.location.href = settings.promos[0].link;
                });
            } else {
                // up and down
                $(".promos-header").bind("click", function () {
                    $(".promos-container, .promos-overlay, .promos-header").toggleClass(
                        "up"
                    );
                });

                $(".promos-overlay").click(function () {
                    $(".promos-container, .promos-overlay, .promos-header").removeClass(
                        "up"
                    );
                });
            }

            // arrows
            if (settings.arrows == true) {
                // add arrows
                if (settings.variableWidth == true) {
                    $("#promosDrawer .promos-container").append(
                        '<div id="arrowsPromoDeal"><div class="promo-arrow prev" onclick="promoPrevIrr(' +
                        settings.margin +
                        ')"></div><div class="promo-arrow next" onclick="promoNextIrr(' +
                        settings.margin +
                        ')"></div></div>'
                    );
                } else {
                    $("#promosDrawer .promos-container").append(
                        '<div id="arrowsPromoDeal"><div class="promo-arrow prev" onclick="promoPrev(' +
                        settings.margin +
                        ')"></div><div class="promo-arrow next" onclick="promoNext(' +
                        settings.margin +
                        ')"></div></div>'
                    );
                }

                function hideShowLogic() {
                    if (
                        $(".single-promo").outerWidth() * $(".single-promo").length <
                        $(".promos-wrap").width()
                    ) {
                        $("#arrowsPromoDeal > div.promo-arrow.prev").addClass("reach-end");
                        $("#arrowsPromoDeal > div.promo-arrow.next").addClass("reach-end");
                        return;
                    }

                    var conditL =
                        $("#promoDealsSlider").scrollLeft() <= 0 ||
                        $("#promoDealsSlider").scrollLeft() <=
                        $(".single-promo").outerWidth() / 3;
                    var conditR =
                        $(".single-promo").last().offset().left +
                        $(".single-promo").last().width() / 3 <
                        $("#promoDealsSlider").width() + $("#promoDealsSlider").offset().left;
                    //	var conditR  = $('#promoDealsSlider').scrollLeft() >= ( ($(".single-promo").outerWidth() * $(".single-promo").length) - $("#promoDealsSlider").width());

                    if (conditL == true) {
                        $("#arrowsPromoDeal > div.promo-arrow.prev").addClass("reach-end");
                    }
                    if (conditL == false) {
                        $("#arrowsPromoDeal > div.promo-arrow.prev").removeClass("reach-end");
                    }

                    if (conditR == true) {
                        $("#arrowsPromoDeal > div.promo-arrow.next").addClass("reach-end");
                    }
                    if (conditR == false) {
                        $("#arrowsPromoDeal > div.promo-arrow.next").removeClass("reach-end");
                    }
                }

                // detect to show or not arrows
                $("#promoDealsSlider").scroll(function () {
                    hideShowLogic();
                });

                $(window).resize(function () {
                    hideShowLogic();
                });
            }

            // reveal
            $("#promosDrawer")
                .delay(1000)
                .animate(
                    {
                        opacity: "1"
                    },
                    600,
                    function () {
                        /*	try{
                          //	if(dragscroll){
                          //	dragscroll.reset(); // reset dragsoll if there's already on page
                          //	}
                          } catch(err){console.log(err)}
                      */

                        $("#promoDealsSlider").scrollLeft(settings.margin);
                        scrollPromoSlider();

                        // fix bug for arrows
                        hideShowLogic();

                        // callback
                        settings.callback.call(this);
                    }
                );
        }; // end
    })(window, jQuery);

// arrows functions
    function promoNext(margin) {
        $("#promoDealsSlider").stop(true, true);
        var x = Number($("#promoDealsSlider").scrollLeft()) + 1;
        var w = Number($(".single-promo").outerWidth()) + margin;
        $("#promoDealsSlider").animate({ scrollLeft: "+=" + (w - (x % w)) }, 300);
    }

    function promoPrev(margin) {
        $("#promoDealsSlider").stop(true, true);
        var x = Number($("#promoDealsSlider").scrollLeft()) + 1;
        var w = Number($(".single-promo").outerWidth()) + margin;
        var y = x % w;
        if (y == 0) {
            y = w;
        }
        $("#promoDealsSlider").animate({ scrollLeft: "-=" + y }, 300);
    }

// iregular width
    function promoPrevIrr(margin) {
        $("#promoDealsSlider").stop(true, true);
        var p = 0;
        var z = $("#promoDealsSlider").offset().left - 1;

        $(".single-promo").each(function (i) {
            if ($(this).offset().left + margin >= z) {
                p = i;
                return false;
            }
        });
        if (p == 0) {
            return false;
        }
        p--;

        var x = $(".single-promo").eq(p).offset().left * -1 + z;
        if ($.easing && $.easing.swing) {
            $("#promoDealsSlider").animate({ scrollLeft: "-=" + x }, 400, "swing");
        } else {
            $("#promoDealsSlider").animate({ scrollLeft: "-=" + x }, 400);
        }
    }

    function promoNextIrr(margin) {
        $("#promoDealsSlider").stop(true, true);
        var p = 0;
        var z = $("#promoDealsSlider").offset().left;
        $(".single-promo").each(function (i) {
            if ($(this).offset().left >= z + margin / 2) {
                p = i - 1;
                return false;
            }
        });
        if (p == -1) {
            p = 0;
        }
        var w = $(".single-promo").eq(p).outerWidth() + margin;
        var o = z + -1 * $(".single-promo").eq(p).offset().left;
        var m = w - o;
        if ($.easing && $.easing.swing) {
            $("#promoDealsSlider").animate({ scrollLeft: "+=" + m }, 400, "swing");
        } else {
            $("#promoDealsSlider").animate({ scrollLeft: "+=" + m }, 400);
        }
    }
});

