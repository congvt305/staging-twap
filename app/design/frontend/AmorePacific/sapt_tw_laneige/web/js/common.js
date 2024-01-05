/**
 * Created by Eguana
 */
require([
    'jquery',
    'swiper',
    'matchMedia',
    'mage/translate',
    'underscore',
    'domReady!'
],function ($,Swiper, mediaCheck, $t, _) {
    /**
     * Mobile Navigation slideToggle
     * @author Hyuna Ju
     * @version 1.0
     */
    if ($('.navigation').offset()) {
        if (isApplication()) {
            $('.navigation .level0 > a').click(function () {
                $(this).next().slideToggle(300);
                $(this).toggleClass('ui-state-active');
            });
        }
    }

    $('.bundle-button-toggle').click(function () {
        $(this).next().slideToggle(300);
        $(this).toggleClass('active');
    });

    $('.qty_control .increaseQty, .qty_control .decreaseQty').on("click",function(){
        var $this = $(this);
        var ctrl = ($(this).attr('id').replace('-upt','')).replace('-dec','');
        var currentQty = $("#cart-"+ctrl+"-qty").val();
        if($this.hasClass('increaseQty')){
            var newAdd = parseInt(currentQty)+parseInt(1);
            $("#cart-"+ctrl+"-qty").val(newAdd);
            $('.form.form-cart .action.update').click();
        }else{
            if(currentQty>1){
                var newAdd = parseInt(currentQty)-parseInt(1);
                $("#cart-"+ctrl+"-qty").val(newAdd);
                $('.form.form-cart .action.update').click();
            }
        }
    });


    /**
     * Top Button
     * @author Hyuna Ju
     * @version 1.0
     */
        // Initial value setting
    var position = $('.footer.content').offset().left - 90;
    $('.box_totop').css('right',position);
    if ($(window).scrollTop() > 100) {
        $('.back-top').slideDown();
    }

    // scroll body to 0px on click
    $('.back-top').click(function () {
        $('body,html').animate({
            scrollTop: 0
        }, 800);
        return false;
    });

    $(window).scroll(function () {
        ($(this).scrollTop() > 100) ? $('.back-top').slideDown() : $('.back-top').slideUp();
    });

    $(window).resize(function () {
        if (!isApplication()) {
            position = $('.footer.content').offset().left - 90;
            $('.box_totop').css('right',position);
        }
    });


    /**
     *  Add Placeholder
     * @author Christine Song
     * @version 1.0
     */
    if ($('input').offset()) {
        $('input').each(function (index,element) {
            var title = $(element).closest('div.field').children('label').find('span').text();
            var placeholder = $(element).attr('placeholder');

            if(placeholder === undefined) {
                if(title === '') {
                    title = $(element).attr('title');
                }

                $(element).attr('placeholder',title);
            }
        });
    }

    if ($('select').offset()) {
        $('select').each(function (index,element) {
            var title = $(element).closest('div.field').children('label').find('span').text();
            var text = $(element).find('option:selected').text();

            if (text === ' ') {
                $(element).find('option:selected').text(title);
            }
        });
    }

    /**
     * Left Navigation
     * @author Christine Song
     * @version 1.0
     */
    var accountNav = function () {
        var currentNav = $('.account-nav .nav.items .current').html();
        var navTitle = $('.nav.items').parents().closest('div.block').find('.title');
        navTitle.append(currentNav);

        $('.account-nav > .title').click(function () {
            $(this).next().slideToggle('slow');
        });
    };

    if ($('.account-nav').offset()) {
        accountNav();
    }

    /**
     * My Quote List Filter Add Calendar Icon
     * @author Hyuna Ju
     * @version 1.0
     */
    $(document).on('click touchend', '.negotiable_quote-quote-index .quote-grid-filters-wrap .admin__form-field-control', function (event) {
        $(event.target).find('input').trigger('focus');
    });

    /**
     * Add trigger input qty after qty increment & decrement button click
     * @author Soojin An
     * @version 1.0
     */

    $(document).on('click', '.qty-selector', function (event) {
        $(event.target).closest('div.control').find('input').trigger('change');
        return false;
    });


    /**
     * Product detail page tab
     * @author Soojin An
     * @version 1.0
     */

    if ($('.catalog-product-view .product-info-wrapper').offset()) {
        var detailPosition = $('.product-info-scroll').offset().top;

        $('.product-info-main .attribute.ingredients .title').click(function () {
            $(this).toggleClass('active');
            $(this).parent().find('.value').slideToggle();
        });

        $('.product-info-content > span').click(function () {
            $('html,body').animate({scrollTop: detailPosition + 20}, 400);
        });
    }


    if ($('.product-info-scroll').offset()) {
        var Titles = $('.product-info-scroll .titles');
        var Contents = $('.product-info-scroll .contents');
        var titleLength = Titles.find('.title').length;

        Titles.find('.title:first-child').addClass('active');

        Titles.find('.title').click(function (event) {
            event.preventDefault();

            Titles.toggleClass('open');
            Titles.find('.title').removeClass('active');
            var titleAnchor = $($(this).find('a').attr('href'));
            var contentPosition = titleAnchor.offset().top;
            var titlePosition = $(this).height();

            contentPosition = contentPosition - titlePosition;
            $('html,body').animate({scrollTop: contentPosition}, 400);

            $(this).addClass('active');
        });

        var stickyDetailTab = function (e) {
            var scrollTop = $(window).scrollTop();
            var stickyDetailed = $('.product-info-scroll').offset().top;
            var DetailedEnd = stickyDetailed + $('.product-info-scroll').outerHeight();

            if($(window).width() > 767 && $('.product-info-main').hasClass('stickyshow')) { //Add height of product info main if it is also sticky
                stickyDetailed = stickyDetailed - 96;
            }

            setTimeout(function() {
                //console.log('sleep');
            }, 3000);

            if (scrollTop > stickyDetailed && scrollTop < DetailedEnd) {
                Titles.addClass('sticky');
                Titles.removeClass('non-sticky');
                Contents.addClass('sticky');
                Titles.parent().addClass('active');
            } else {
                Titles.removeClass('sticky');
                Titles.addClass('non-sticky');
                Contents.removeClass('sticky');
                Titles.parent().removeClass('active');
            }

        };

        // scroll selectors
        var lastId,
            tabContents = Titles.find('.data.item.title'),
            lastTabContents = Titles.find('.title:last-child'),
            lastContents = Contents.find('.content:last-child'),
            firstContents = Contents.find('.content:first-child'),
            scrollItems = tabContents.map(function () {
                var item = $($(this).find('.switch').attr('href'));
                if (item.length) {
                    return item;
                }
            });

        var devent = _.debounce(function () {
            if (titleLength > 1) {
                stickyDetailTab();
            }

            var currentScroll = $(this).scrollTop();
            var scrollPosition = $(window).height() + $(window).scrollTop();
            var scrollHeight = $(document).height();
            var tabTitlesPosition = Titles.outerHeight();
            var detailedPosition = $('.product-info-scroll').offset().top;

            if (currentScroll > detailedPosition) {
                var cur = scrollItems.map(function () {
                    if ($(this).offset().top < currentScroll + tabTitlesPosition + 1) {
                        return this;
                    }
                });

                cur = cur[cur.length-1];
                var titleId = cur && cur.length ? cur[0].id : firstContents.attr('id');

                if ((scrollHeight - scrollPosition) / scrollHeight === 0) {
                    tabContents.removeClass('active');
                    lastTabContents.addClass('active');
                    lastId = lastContents.attr('id');
                } else if (lastId !== titleId) {
                    lastId = titleId;
                    tabContents.removeClass('active');
                    tabContents.find('a').filter("[href='#"+titleId+"']").parent().addClass('active');
                }
            }
        }, 100);

        $(window).scroll(function (e) {
            devent();
        });
    }

    if ($('.products-crosssell .product-items').offset()) {
        mediaCheck({
            media: '(min-width: 768px)',
            entry: function () {
                if ($('.products-crosssell .product-items .item').length < 5) {
                    $('.block.crosssell .content, .products-crosssell .product-items').addClass('align-items');
                } else {
                    var crosssellSlider = new Swiper('.products-crosssell', {
                        slidesPerView: 5,
                        freeMode: false,
                        autoHeight: true,
                        direction: 'horizontal',
                        loop: true,
                        spaceBetween: 20,
                        autoplay: {
                            delay: 1000,
                        },
                        breakpoints: {
                            768: {
                                slidesPerView: 3
                            }
                        },
                        navigation: {
                            nextEl: '.block.crosssell .swiper-button-next',
                            prevEl: '.block.crosssell .swiper-button-prev'
                        }
                    });
                }
            },
            exit: function () {
                if ($('.products-crosssell .product-items .item').length < 2) {
                    $('.products-crosssell .product-items').addClass('align-items');
                } else {
                    var crosssellSlider = new Swiper('.products-crosssell', {
                        slidesPerView: 1.7,
                        spaceBetween: 16,
                        freeMode: false,
                        autoHeight: true,
                        direction: 'horizontal',
                        loop: false,
                        navigation: {
                            nextEl: '.block.crosssell .swiper-button-next',
                            prevEl: '.block.crosssell .swiper-button-prev'
                        }
                    });
                }
            }
        });
    }

    if ($('.product-config-items').offset()) {
        // 20230620 modified - start
        if ($('.products-related .product-items').offset()) {
            var relatedSlider,
                relatedSliderLen = $('.products-related .product-items .item').length;

            mediaCheck({
                media: '(min-width: 768px)',
                entry: function () {
                    if (typeof relatedSlider !== 'undefined') relatedSlider.destroy();
                    relatedSlider = new Swiper('.products-related', {
                        slidesPerView: 5,
                        freeMode: false,
                        autoHeight: true,
                        direction: 'horizontal',
                        loop: relatedSliderLen > 5,
                        spaceBetween: 20,
                        autoplay: {
                            delay: 1000,
                        },
                        breakpoints: {
                            1024: {
                                slidesPerView: 4
                            }
                        },
                        navigation: {
                            nextEl: '.block.related .swiper-button-next',
                            prevEl: '.block.related .swiper-button-prev'
                        },
                        watchOverflow: true,
                        centerInsufficientSlides: true,
                        on: {
                            init: function() {
                                if (relatedSliderLen <= 5) {
                                    $('.block.related .content').addClass('align-items');
                                }
                            }
                        }
                    });
                },
                exit: function () {
                    if (typeof relatedSlider !== 'undefined') relatedSlider.destroy();
                    relatedSlider = new Swiper('.products-related', {
                        slidesPerView: 1.7,
                        spaceBetween: 16,
                        freeMode: false,
                        autoHeight: true,
                        direction: 'horizontal',
                        loop: false,
                        navigation: {
                            nextEl: '.block.related .swiper-button-next',
                            prevEl: '.block.related .swiper-button-prev'
                        },
                        watchOverflow: true,
                        centerInsufficientSlides: true
                    });
                }
            });
        }
        // 20230620 modified - end


        if ($('.products-upsell .product-items').offset()) {
            mediaCheck({
                media: '(min-width: 768px)',
                entry: function () {
                    if ($('.products-upsell .product-items .item').length < 4) {
                        $('.block.upsell .content, .products-upsell .product-items').addClass('align-items');
                    } else {
                        var upsellSlider = new Swiper('.products-upsell', {
                            slidesPerView: 4,
                            freeMode: false,
                            autoHeight: true,
                            direction: 'horizontal',
                            loop: false,
                            spaceBetween: 20,
                            autoplay: {
                                delay: 1000,
                            },
                            breakpoints: {
                                768: {
                                    slidesPerView: 3
                                }
                            },
                            navigation: {
                                nextEl: '.block.upsell .swiper-button-next',
                                prevEl: '.block.upsell .swiper-button-prev'
                            }
                        });
                    }
                },
                exit: function () {
                    if ($('.products-upsell .product-items .item').length < 2) {
                        $('.block.upsell .content, .products-upsell .product-items').addClass('align-items');
                    } else {
                        var upsellSlider = new Swiper('.products-upsell', {
                            slidesPerView: 1.7,
                            freeMode: false,
                            autoHeight: true,
                            direction: 'horizontal',
                            loop: false,
                            spaceBetween: 16,
                            navigation: {
                                nextEl: '.block.upsell .swiper-button-next',
                                prevEl: '.block.upsell .swiper-button-prev'
                            }
                        });
                    }
                }
            });
        }
    }

    if ($('.page.messages > .messages').offset()) {
        $('.page.messages > .messages').append('<div class="remove"></div>');
        $('.page.messages > .messages .remove').show();

        $('.page.messages > .messages .remove').click(function (e) {
            $(this).closest('div.messages').slideUp();
        });
    }

    /****************************
     * Mobile Y/N
     * @returns {boolean}
     ****************************/
    function isApplication()
    {
        var isMobile = false;
        if (navigator.userAgent.match(/Android|Mobile|iP(hone|od|ad)|BlackBerry|IEMobile|Kindle|NetFront|Silk-Accelerated|(hpw|web)OS|Fennec|Minimo|Opera M(obi|ini)|Blazer|Dolfin|Dolphin|Skyfire|Zune/)) {
            if ($(window).width() < 768) {
                isMobile = true;
            }
        }
        return isMobile;
    }

    /**
     * Footer Arcodian
     * @author Kenn
     * @version 1.0
     */
    $('.footer_top_nav .footer_nav .nav_item > h5').click(function(){
        $(this).parent().toggleClass('_active');
        $(this).parent().find('> ul').slideToggle();
    });

    /**
     * My account - my page
     * @author Kenn
     * @version 2.0
     */
    $(document).ready(function(){
        if($('.sidebar #account-nav').length){
            var myPageHtml = $('.sidebar .account-nav-content .items').html();
            var myPageContainer = '<div class="mobile-my-page"><div class="mobile-my-page-title">' + $t('My page') + '</div><div class="mobile-my-page-content"></div></div>';
            $('.page-wrapper .sections.nav-sections').after(myPageContainer);
            $('.mobile-my-page-content').append(myPageHtml);

            $('.mobile-my-page .mobile-my-page-title').click(function(){
                $('.mobile-my-page .mobile-my-page-content').slideToggle();
                $('.mobile-my-page').toggleClass('_active');
            });
        }
    });

    /**
     * Mobile Privacy Policy, T&C, Membership T&C, FAQ, Shipping Info Nav Accordion
     * @author Luke
     * @version 2.0
     */
    $('.cms-2columns-sidebar > h2').click(function(){
        if($(window).width() < 768){
            $(this).parent().toggleClass('_active');
            $(this).parent().find('ul').slideToggle();
        }
    });

    /**
     * Align height of product tags of product items
     * @author Steven
     * @version 1.0
     */
    function alignHeight(selector) {
        $(selector).css('height', '');
        if ($(window).width() > 767) {
            let minHeight = 0;
            $(selector).each(function() {
                if ($(this).height() > minHeight) {
                    minHeight = $(this).height();
                }
            });
            if (minHeight > 0) {
                $(selector).css('height', minHeight);
            }
        }
    }
    $(document).ready(function() {
        alignHeight('.new_arrivals_section .product-items .product-item .product_tags');
        alignHeight('.bestseller-contents-slider:first-child .product-items .product-item .product_tags');
        alignHeight('.bestseller-contents-slider:nth-child(2) .product-items .product-item .product_tags');
        alignHeight('.bestseller-contents-slider:nth-child(3) .product-items .product-item .product_tags');
    });

    $(window).resize(function() {
        setTimeout(function() {
            alignHeight('.new_arrivals_section .product-items .product-item .product_tags');
            alignHeight('.bestseller-contents-slider:first-child .product-items .product-item .product_tags');
            alignHeight('.bestseller-contents-slider:nth-child(2) .product-items .product-item .product_tags');
            alignHeight('.bestseller-contents-slider:nth-child(3) .product-items .product-item .product_tags');
        }, 500)
    });
    $(window).resize(function(){
        $('.product-image-wrapper').each(function() {
            $(this).children('img').height($(this).innerHeight());
            $(this).children('img').width($(this).innerWidth());
        });

    });

    $(document).ready(function() {
        $('.product-image-wrapper').each(function() {
                  $(this).children('img').height($(this).innerHeight());
                  $(this).children('img').width($(this).innerWidth());
              })
       
      });


});