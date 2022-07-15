define([
    'jquery'
],function ($) {
   "use strict"

    var isSupportMatchMedia = window.isSupportMatchMedia || (window.matchMedia !== undefined) ? true : false;
    function matchMobile() {
        if (isSupportMatchMedia) {
            return window.matchMedia("(max-width: 599px)").matches
        } else {
            return false
        }
    }

    function matchTablet() {
        if (isSupportMatchMedia) {
            return window.matchMedia("(min-width: 600px) and (max-width: 1024px)").matches && Modernizr.touch
        } else {
            return false
        }
    }
    (function(b) {
        var a = {};
        var c = {
            videoWidth: "100%",
            videoHeight: "auto",
            videoIdBase: "ytplayer",
            color: "white",
            autoplay: 0,
            theme: "dark",
            controls: 1,
            targetId: "",
            onReady: undefined
        };
        b.fn.ytiframe = function(e) {
            if (this.length == 0) {
                return this
            }
            if (this.length > 1) {
                this.each(function() {
                    b(this).ytiframe(e)
                });
                return this
            }
            var d = /^.*(youtu.be\/|v\/|e\/|u\/\w+\/|embed\/|v=)([^#\&\?]*).*/;
            var g = {},
                h = this,
                j = {};
            a.el = this;
            var i = function() {
                b.extend(j, c, e);
                g.url = h.attr("href");
                g.videoId = "";
                g.ytId = "";
                g.ytId = g.url.match(d)[2];
                g.videoId = j.videoIdBase + g.ytId;
                if (j.targetId == "") {
                    if (h.parents(".flexible-obj").length > 0) {
                        g.container = h.wrap('<div class="video-player" />').parent()
                    } else {
                        g.container = h.wrap('<div class="video-player flexible-obj" />').parent()
                    }
                    g.embed = b('<iframe src="https://www.youtube.com/embed/' + g.ytId + "?autoplay=1&loop=1&rel=0&wmode=transparent&showinfo=0&color=" + j.color + "&theme=" + j.theme + "&enablejsapi=1&rel=0&controls=" + j.controls + '" frameborder="0" title="유튜브 영상" allow="autoplay; encrypted-media" allowfullscreen></iframe>').attr("id", g.videoId).addClass("video-iframe").appendTo(g.container)
                } else {
                    b("#" + j.targetId).html("");
                    g.embed = b('<iframe src="https://www.youtube.com/embed/' + g.ytId + "?autoplay=1&loop=1&rel=0&wmode=transparent&showinfo=0&color=" + j.color + "&theme=" + j.theme + "&enablejsapi=1&rel=0&controls=" + j.controls + '" frameborder="0" title="유튜브 영상" allow="autoplay; encrypted-media" allowfullscreen></iframe>').attr("id", g.videoId).appendTo(b("#" + j.targetId))
                }
                if (typeof j.onReady === "function") {
                    j.onReady(g.embed)
                }
                if (j.targetId == "") {
                    h.hide()
                }
            };
            i();
            h.pause = function() {
                console.log(h + " pause")
            };
            h.destroyPlayer = function() {
                if (g.embed) {
                    g.embed.remove();
                    h.css("display", "");
                    h.unwrap()
                }
            };
            return this
        }
    })(jQuery);
    function ytVideoPlay(selector) {
        if (selector.length < 1) {
            return
        }
        if (isSupportMatchMedia) {
            selector.each(function(v, x) {
                var u = $(x), t = u.find("img"), w;
                if (t.length > 0 && !(matchMobile() || matchTablet())) {
                    u.on("click", function(y) {
                        $(this).parent().addClass("playing")
                        w = u.ytiframe({
                            autoplay: 1
                        });
                        $(".video-player").attr("tabindex", 0).focus();
                        y.preventDefault();
                        y.stopPropagation()
                    })
                } else {
                    w = u.ytiframe({
                        autoplay: 0,
                        controls: 1
                    })
                }
            })
        } else {
            selector.each(function(u, w) {
                var t = $(w), v;
                v = t.ytiframe({
                    autoplay: 0,
                    controls: 1
                });
                $(".video-iframe").css("overflow", "visible")
            })
        }
    }
    // youtube 영상
    if ($(".yt-player").length) {
        ytVideoPlay($(".yt-player"));
    }
    function selectbox() {
        var c = $("div.selectbox"),
            a = c.find(".selectbox-option"),
            d = $(document);
        c.not(".is-disabled").each(function() {
            var v = $(this),
                e = v.find(">.selector"),
                y = v.find(".selector-text"),
                g = v.find(">.selectbox-option"),
                l = g.find(">li"),
                h, r = "",
                i = (l.find("a").length > 0) ? true : false,
                m, s = false,
                u = l.length;
            t();

            function t() {
                var w;
                if (v.data("is-setup")) {
                    return
                }
                w = 0;
                h = (i) ? l.find(">a") : l;
                x();
                j();
                if (i) {
                    g.on("click", "a", function(A) {
                        q(this)
                    });
                    g.on("keydown", "a", function(C) {
                        if (C.keyCode == 38) {
                            C.preventDefault();
                        } else if (C.keyCode == 40) {
                            C.preventDefault();
                        }
                    });
                    g.on("keyup", "a", function(C) {
                        var B = $(this),
                            A = B.data("index");
                        if (C.keyCode == 27) {
                            k();
                            n(C)
                        } else {
                            if (C.keyCode == 38) {
                                if (A - 1 >= 0) {
                                    h.eq(A - 1).focus()
                                }
                            } else {
                                if (C.keyCode == 40) {
                                    if (A + 1 <= u - 1) {
                                        h.eq(A + 1).focus()
                                    }
                                }else if(C.keyCode == 9){
                                    if (!(A + 1 <= u - 1)) {
                                        k();
                                        n(C)
                                    }
                                }
                            }
                        }
                    });
                    g.on("focus", "a", function(A) {
                        $(this).parent().addClass("is-active")
                    });
                    g.on("blur", "a", function(A) {
                        $(this).parent().removeClass("is-active")
                    })
                } else {
                    g.on("click", "li", function(A) {
                        q(this);
                        A.stopPropagation()
                    });
                    v.delegate("li", "keydown", function(C) {
                        if (C.keyCode == 38) {
                            C.preventDefault();
                        } else if (C.keyCode == 40) {
                            C.preventDefault();
                        }
                    });
                    v.delegate("li", "keyup", function(C) {
                        var B = $(this),
                            A = B.data("index");
                        if (C.keyCode == 13) {
                            $(this).trigger("click")
                        } else {
                            var prevLength = B.prevAll("li:not([style*='display: none'])").length;
                            var nextLength = B.nextAll("li:not([style*='display: none'])").length;

                            if (C.keyCode == 27) {
                                k();
                                n(C)
                            } else {
                                if (C.keyCode == 38) {
                                    var prevIdx = B.prevAll("li:not([style*='display: none'])").eq(0).data("index");
                                    h.eq(prevIdx).focus();
                                    /*
                              if (A - 1 >= 0) {
                                  h.eq(A - 1).focus()
                              }
                              */
                                } else {
                                    if (C.keyCode == 40) {

                                        if(nextLength > 0){
                                            var nextIdx = B.nextAll("li:not([style*='display: none'])").eq(0).data("index");
                                            h.eq(nextIdx).focus();
                                        }

                                        /*
                                  if (A + 1 <= u - 1) {
                                      console.log("i false focus");
                                      h.eq(A + 1).focus()
                                  }
                                  */
                                    }else if(C.keyCode == 9){
                                        if (!(A + 1 <= u - 1)) {
                                            k();
                                            n(C)
                                        }
                                    }
                                }
                            }
                        }
                    });
                    g.on("focus", "li", function(A) {
                        $(this).addClass("is-active")
                    });
                    g.on("blur", "li", function(A) {
                        $(this).removeClass("is-active")
                    })
                }
                e.on("click keyup", function(A) {
                    if ($(this).next("ul").find("li").attr("tabindex") == undefined) {
                        if (i) {
                            h.each(function(B, C) {
                                $(C).data("index", B)
                            })
                        } else {
                            h.each(function(B, C) {
                                $(C).attr("tabindex", 0).data("index", B);
                                $(C).find("input[type=radio]").attr("tabindex", -1)
                            })
                        }
                        u = h.length
                    }
                    if (A.type == "click" || A.keyCode == 13) {
                        if (v.data("is-open")) {
                            p()
                        } else {
                            o();
                            //if (A.keyCode == 13) {
                            h.not('[style*="display: none"]').eq(0).focus()
                            //}
                        }
                        A.preventDefault()
                    }
                });
                e.on("focus", z);
                v.on("click", function(A) {
                    A.stopPropagation()
                });

                function z() {
                    var A = new Array(38, 40);
                    d.on("keyup.blockArrow", "li", function(C) {
                        var B = C.which;
                        if ($.inArray(B, A) > -1) {
                            C.preventDefault();
                            return false
                        }
                    })
                }
                v.on("click", function(A) {
                    A.stopPropagation()
                });
                if (!i && g.has(".is-current")) {
                    w = $(window).scrollTop();
                    g.find(".is-current").click();
                    $("html, body").scrollTop(w);
                    e.blur()
                }
                if (i && v.has(".is-current").length > 0) {
                    g.find(".is-current").addClass("is-active");
                    y.text(g.find(".is-current").text())
                }
                v.data("is-setup", true)
            }

            function q(A) {
                var z = $(A),
                    w = (z.find(".select-item").length > 0) ? true : false;
                if (w) {
                    r = z.find(".select-item").html();
                    e.find(">span").html(r)
                } else {
                    r = z.text();
                    e.find(">span").text(r)
                }
                if (!i) {
                    g.find("input").prop("checked", false);
                    z.find("input").prop("checked", true).trigger("change")
                }
                k()
            }

            function x() {
                v.data("is-open", false);
                e.attr("tabindex", 0);
                if (!i) {
                    h.attr("tabindex", 0).find("input[type=radio]").attr("tabindex", "-1").each(function(z, B) {
                        var A = $(B),
                            w, C;
                        if (A.attr("id") && A.attr("id") !== "") {
                            return
                        }
                        w = A.parent("label") || A.siblings("label");
                        C = A.attr("name") + (z + 1);
                        A.attr("id", C);
                        if (w.length) {
                            w.attr("for", C)
                        }
                    })
                }
                h.each(function(w, z) {
                    $(z).data("index", w)
                })
            }

            function j() {
                if (!v.attr("style") || v.attr("style").indexOf("width") == -1 || v.width() !== "100%") {
                    var z = l.children().width() + 0,
                        w = e.outerWidth() + 0;
                    if (w < z) {
                        v.width(z)
                    }
                }
                g.css("visibility", "visible").hide()
            }

            function o() {
                if (v.data("is-open")) {
                    return
                }
                b();
                v.addClass("is-active").css("zIndex", 1000).data("is-open", true);
                g.show()
            }

            function p() {
                g.hide();
                d.off("keyup.blockArrow");
                v.removeClass("is-active").css("zIndex", 1).data("is-open", false)
            }

            function k() {
                p();
                e.focus()
            }

            function n(w) {
                if (w.preventDefault) {
                    w.preventDefault();
                    w.stopPropagation()
                } else {
                    w.returnValue = false
                }
            }
        });
        d.on("click", b);

        function b() {
            c.removeClass("is-active").css("zIndex", "auto").data("is-open", false);
            a.hide()
        }
    }

    $(document).ready(function () {
        selectbox();
    });

    /*--------------------*/

    (function(a) {
        a.fn.accordion = function(b) {
            var b = a.extend({
                easing: "easeOutQuint",
                speed: 400,
                titleSelector: ".q",
                contSelector: ".a",
                collapsible: false,
                callback: function() {},
                onOpen: function(c) {}
            }, b);
            return this.each(function() {
                var g = a(this),
                    d = g.find(b.titleSelector),
                    e = g.find(b.contSelector),
                    j = d.parent(),
                    i = g.filter(".on");
                var h = function() {
                    var k = d.outerHeight();
                    d.css("cursor", "pointer").attr("tabindex", 0);
                    d.on("click keypress", function(l) {
                        if (l.type == "click" || l.which == 13) {
                            c(a(this).parent())
                        }
                    });
                    if (!b.collapsible) {
                        if (i.length > 0) {
                            c(i.eq(0))
                        }
                    } else {
                        i.removeClass("on")
                    }
                    j.not(".on").find(b.contSelector).hide()
                };
                var c = function(k) {
                    var l = k.hasClass("on");
                    j.removeClass("on");
                    e.stop(true, true).slideUp({
                        duration: b.speed,
                        easing: b.easing,
                        complete: function() {
                            b.callback();
                            e.css("zoom", 1)
                        }
                    });
                    if (l) {
                        return
                    }
                    k.addClass("on").find(b.contSelector).stop(true, true).slideDown({
                        duration: b.speed,
                        easing: b.easing,
                        complete: function() {
                            b.callback();
                            b.onOpen(k)
                        }
                    })
                };
                h()
            })
        };
        a.fn.accordion1 = function(b) {
            var b = a.extend({
                easing: "ease",
                speed: 400,
                titleSelector: ".q",
                contSelector: ".a",
                collapsible: false,
                callback: function() {},
                onOpen: function(c) {}
            }, b);
            return this.each(function() {
                var g = a(this),
                    d = g.find(b.titleSelector),
                    e = g.find(b.contSelector),
                    j = d.closest("section"),
                    i = g.filter(".on");
                var h = function() {
                    var k = d.outerHeight();
                    d.on("click", function(l) {
                        if (l.type == "click" || l.which == 13) {
                            c(a(this).closest("section"))
                        }
                    });
                    if (!b.collapsible) {
                        if (i.length > 0) {
                            c(i.eq(0))
                        }
                    } else {
                        i.removeClass("on")
                    }
                    j.not(".on").find(b.contSelector).hide()
                };
                var c = function(k) {
                    var l = k.hasClass("on");
                    j.removeClass("on");
                    e.stop(true, true).slideUp({
                        duration: b.speed,
                        complete: function() {
                            b.callback();
                            e.css("zoom", 1)
                        }
                    });
                    if (l) {
                        return
                    }
                    k.addClass("on").find(b.contSelector).stop(true, true).slideDown({
                        duration: b.speed,
                        complete: function() {
                            b.callback();
                            b.onOpen(k)
                        }
                    })
                };
                h()
            })
        }
    })(jQuery);
    (function(a) {
        var b = function() {
            var h = a(window),
                c = h.height(),
                g = h.scrollTop(),
                e = a(document).height(),
                d = [];
            a.each(a.cache, function() {
                if (this.events && this.events.inview) {
                    var j = this.handle.elem,
                        k = 0;
                    try {
                        k = this.events.inview[0].data.offset
                    } catch (i) {}
                    a(j).data("offset", k);
                    d.push(j)
                }
            });
            if (!d.length) {
                return
            }
            a(d).each(function(k, m) {
                var l = a(m),
                    j = l.offset().top,
                    i = l.height(),
                    o = c * (1 - l.data("offset")),
                    n = l.data("inview") || false;
                if ((g + c) < j || g > (j + i)) {
                    if (n) {
                        l.data("inview", false);
                        l.trigger("inview", [false])
                    }
                } else {
                    if ((g + c - o) >= (j) || (j >= (e - o - 150) && g + c >= (e - 150))) {
                        if (!n) {
                            l.data("inview", true);
                            l.trigger("inview", [true])
                        }
                    }
                }
            })
        };
        a(window).scroll(b);
        a(window).resize(b);
        setTimeout(b, 500)
    })(jQuery);
    (function(a) {
        a.belowthefold = function(c, d) {
            var b = a(window).height() + a(window).scrollTop();
            return b <= a(c).offset().top - d.threshold
        };
        a.abovethetop = function(b, c) {
            var d = a(window).scrollTop();
            return d >= a(b).offset().top + a(b).height() - c.threshold
        };
        a.rightofscreen = function(c, d) {
            var b = a(window).width() + a(window).scrollLeft();
            return b <= a(c).offset().left - d.threshold
        };
        a.leftofscreen = function(b, c) {
            var d = a(window).scrollLeft();
            return d >= a(b).offset().left + a(b).width() - c.threshold
        };
        a.inviewport = function(b, c) {
            return !a.rightofscreen(b, c) && !a.leftofscreen(b, c) && !a.belowthefold(b, c) && !a.abovethetop(b, c)
        };
        a.extend(a.expr[":"], {
            "below-the-fold": function(c, d, b) {
                return a.belowthefold(c, {
                    threshold: 0
                })
            },
            "above-the-top": function(c, d, b) {
                return a.abovethetop(c, {
                    threshold: 0
                })
            },
            "left-of-screen": function(c, d, b) {
                return a.leftofscreen(c, {
                    threshold: 0
                })
            },
            "right-of-screen": function(c, d, b) {
                return a.rightofscreen(c, {
                    threshold: 0
                })
            },
            "in-viewport": function(c, d, b) {
                return a.inviewport(c, {
                    threshold: 0
                })
            }
        })
    })(jQuery);

});
