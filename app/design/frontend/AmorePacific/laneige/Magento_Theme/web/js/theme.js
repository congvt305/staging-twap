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

    $('.cart-summary').mage('sticky', {
        container: '#maincontent'
    });

    $('.footer.content').click(function () {
        mediaCheck({
            media: '(max-width: 768px)',
            entry: function () {
                $(this).toggleClass('active');
                $('.page-footer .widget ,.page-footer .top').slideToggle();
            }
        });
    });

    $('.content.footer  .links.socials').clone().appendTo('#store\\.menu');
    $('.content.header > .header.links').clone().appendTo('#store\\.links');

    var stickyNav = function () {
        $('.product.data.items').removeClass('sticky');
        $('.product.data.items >.data.item.title').removeClass('active');

        var scrollTop = $(window).scrollTop();
        var stickyNavTop = $('.product.data.items').offset().top;

        if (scrollTop > stickyNavTop) {
            $('.product.data.items').addClass('sticky');
        }

        $('.product.data.items >.data.item.title:first-child').addClass('active');

        $('.tab-contents >.data.item.content').each(function(index,element){
            var offset = $(element).offset();
            var offsetTop = offset.top;
            var stickyHeight = $('.product.data.items').height();
            var top = offsetTop - stickyHeight - 10;

            if(scrollTop > top && scrollTop > 0) {
                var id = $(element).attr('id');
                $('.product.data.items >.data.item.title').removeClass('active');
                $("a[href='#"+id+"']").parent().addClass('active');
            }
        });
    };
    stickyNav();

    $(window).scroll(function () {
        stickyNav();
    });

    keyboardHandler.apply();
});
