/**
 * Created by Eguana
 */
require([
    'jquery',
    'swiper',
    'matchMedia',
    'domReady!',
],function ($,Swiper, mediaCheck) {
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
    
    /**
     * Top Button
     * @author Hyuna Ju
     * @version 1.0
     */
    // Initial value setting
    var position = $('.footer.content').offset().left + 20;
    $('.back-top').css('right',position);
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
            position = $('.footer.content').offset().left + 20;
            $('.back-top').css('right',position);
        }
    });

    /**
     * Layered Navigation
     * @author Soojin An
     * @version 1.0
     */
    if ($('.block.filter').offset()) {
        $('.filter-option').click(function () {
            if ($(this).hasClass('active')) {
                $(this).toggleClass('active');
            } else {
                $('.filter-option').removeClass('active');
                $(this).toggleClass('active');
            }
        });
        
        $('body').click(function (e) {
            if (!$('.filter-option').has(e.target).length) {
                $('.filter-option').removeClass('active');
            }
        });
    }

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

    $(document).on('click touch touchstart', '.qty-selector', function (event) {
        $(event.target).closest('div.control').find('input').trigger('change');
        return false;
    });


    /**
     * Product detail page tab
     * @author Soojin An
     * @version 1.0
     */

    if ($('.catalog-product-view .product-info-wrapper').offset()) {
        var detailPosition = $('.product.info.detailed').offset().top;

        $('.product-info-main .attribute.ingredients .title').click(function () {
            $(this).toggleClass('active');
            $(this).parent().find('.value').slideToggle();
        });

        $('.product-info-content > span').click(function () {
            $('html,body').animate({scrollTop: detailPosition + 20}, 400);
        });
    }


    if ($('.product.info.detailed').offset()) {
        var Titles = $('.product.info.detailed .titles');
        var Contents = $('.product.info.detailed .contents');
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
            var stickyDetailed = $('.product.info.detailed').offset().top;
            var DetailedEnd = stickyDetailed + $('.product.info.detailed').outerHeight();

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

        $(window).scroll(function (e) {
            if (titleLength > 1) {
                stickyDetailTab();
            }

            var currentScroll = $(this).scrollTop();
            var scrollPosition = $(window).height() + $(window).scrollTop();
            var scrollHeight = $(document).height();
            var tabTitlesPosition = Titles.outerHeight();
            var detailedPosition = $('.product.info.detailed').offset().top;

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
        });
    }

    if ($('.product-config-items').offset()) {
        if ($('.products-related .product-items').offset()) {
            mediaCheck({
                media: '(min-width: 768px)',
                entry: function () {
                    if ($('.products-related .product-items .item').length < 4) {
                        $('.block.related .content, .products-related .product-items').addClass('align-items');
                    } else {
                        var relatedSlider = new Swiper('.products-related', {
                            slidesPerView: 4,
                            freeMode: false,
                            autoHeight: true,
                            direction: 'horizontal',
                            loop: false,
                            threshold: 30,
                            breakpoints: {
                                768: {
                                    slidesPerView: 3
                                }
                            },
                            navigation: {
                                nextEl: '.block.related .swiper-button-next',
                                prevEl: '.block.related .swiper-button-prev'
                            }
                        });
                    }
                },
                exit: function () {
                    if ($('.products-related .product-items .item').length < 2) {
                        $('.products-related .product-items').addClass('align-items');
                    } else {
                        var relatedSlider = new Swiper('.products-related', {
                            slidesPerView: 2,
                            freeMode: false,
                            autoHeight: true,
                            direction: 'horizontal',
                            loop: false,
                            threshold: 30,
                            navigation: {
                                nextEl: '.block.related .swiper-button-next',
                                prevEl: '.block.related .swiper-button-prev'
                            }
                        });
                    }
                }
            });
        }

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
                            threshold: 30,
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
                            slidesPerView: 2,
                            freeMode: false,
                            autoHeight: true,
                            direction: 'horizontal',
                            loop: false,
                            threshold: 30,
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
            if (jQuery(window).width() < 768) {
                isMobile = true;
            }
        }
        return isMobile;
    }
});