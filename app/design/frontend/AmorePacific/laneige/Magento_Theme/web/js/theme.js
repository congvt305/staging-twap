/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/smart-keyboard-handler',
    'matchMedia',
    'mage/mage',
    'mage/ie-class-fixer',
    'domReady!'
], function ($, keyboardHandler, mediaCheck) {
    'use strict';

    if ($('body').hasClass('checkout-cart-index')) {
        if ($('#co-shipping-method-form .fieldset.rates').length > 0 &&
            $('#co-shipping-method-form .fieldset.rates :checked').length === 0
        ) {
            $('#block-shipping').on('collapsiblecreate', function () {
                $('#block-shipping').collapsible('forceActivate');
            });
        }
    }

    if ($('.navigation').offset()) {
        mediaCheck({
            media: '(max-width: 768px)',
            entry: function () {
                $('.navigation .parent > a').click(function () {
                    $(this).next().slideToggle(300);
                    $(this).toggleClass('ui-state-active');
                });
            }
        });
    }

    $('.footer.content').click(function () {
        mediaCheck({
            media: '(max-width: 768px)',
            entry: function () {
                $('.footer.content').toggleClass('active');
                $('.page-footer .widget ,.page-footer .top').slideToggle();
            }
        });
    });

    $('.content.footer  .links.socials').clone().appendTo('#store\\.menu');
    $('.content.header > .header.links').clone().appendTo('#store\\.links');

    var stickyHeader = function () {
        var scrollTop = $(window).scrollTop();
        var stickyVal = 0;

        if ($('.laneige-top-banner').offset()) {
            stickyVal = $('.laneige-top-banner').height();
        }

        if ($(window).scrollTop() <= stickyVal) {
            $('.page-header').removeClass('sticky');
        } else {
            $('.page-header').addClass('sticky');
        }
    };

    var stickyNav = function () {
        if ($('.product.data.items').offset()) {
            $('.product.data.items').removeClass('sticky');
            $('.product.data.items >.data.item.title').removeClass('active');

            var scrollTop = $(window).scrollTop();
            var stickyNavTop = $('.product.data.items').offset().top;

            if (scrollTop > stickyNavTop) {
                $('.product.data.items').addClass('sticky');
            }

            $('.product.data.items >.data.item.title:first-child').addClass('active');

            $('.tab-contents >.data.item.content').each(function (index,element) {
                var offset = $(element).offset();
                var offsetTop = offset.top;
                var stickyHeight = $('.product.data.items').height();
                var top = offsetTop - stickyHeight - 10;
                var id = $(element).attr('id');

                if (id != 'community_gallery') {
                    if (scrollTop > top && scrollTop > 0) {
                        var id = $(element).attr('id');
                        $('.product.data.items >.data.item.title').removeClass('active');
                        $("a[href='#"+id+"']").parent().addClass('active');
                    }
                }
            });
        }

        if ($('.catalog-product-view').offset()) {
            mediaCheck({
                media: '(max-width: 768px)',
                entry: function () {
                    $('.box-tocart .actions').removeClass('sticky');
                    if($('.box-tocart .actions').length > 0) {
                        var scrollTop = $(window).scrollTop();
                        var stickyNavTop = $('.box-tocart .actions').offset().top;
                        if (scrollTop > stickyNavTop) {
                            $('.box-tocart .actions').addClass('sticky');
                        }
                    }
                }
            });
        }
    };

    stickyHeader();
    stickyNav();

    $(window).scroll(function () {
        stickyHeader();
        stickyNav();
    });

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

    $(document).on('click touchend', '.ui-datepicker-trigger', function (event) {
        $(event.target).find('._has-datepicker').trigger('focus');
    });

    keyboardHandler.apply();
});
