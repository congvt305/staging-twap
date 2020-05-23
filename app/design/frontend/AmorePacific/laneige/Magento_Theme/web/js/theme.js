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

    keyboardHandler.apply();
});
