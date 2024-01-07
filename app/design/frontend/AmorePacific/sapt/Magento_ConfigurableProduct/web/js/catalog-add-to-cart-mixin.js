/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery',
    'Magento_ConfigurableProduct/js/product/view/product-info-resolver'
], function (_, $, productInfoResolver) {
    'use strict';

    return function (widget) {

        $.widget('mage.catalogAddToCart', widget, {
            /**
             * Handler for the form 'submit' event
             *
             * @param {jQuery} form
             */
            submitForm: function (form) {
                var isConfigurable = !!_.find(form.serializeArray(), function (item) {
                    return item.name.indexOf('super_attribute') !== -1;
                });
                if ($(window).width() < 768) {
                    if ($('.product-info-main.sticky').length) {
                        var mWrapBundle = $('.product-info-main.sticky').find('.bundle-options-wrapper , .field-wrapper-qty');
                        if (mWrapBundle.is(":hidden")) {
                            mWrapBundle.show();
                            $('.product-info-main-wrapper').addClass('is_popupcart');
                            return;
                        }
                    }
                }
                return this._super(form);
            },
            /**
             * @param {jQuery} form
             */
            ajaxSubmit: function (form) {
                var isConfigurable = !!_.find(form.serializeArray(), function (item) {
                    return item.name.indexOf('super_attribute') !== -1;
                });

                if (isConfigurable) {
                    this.options.productInfoResolver = productInfoResolver;
                }

                return this._super(form);
            }
        });

        return $.mage.catalogAddToCart;
    };
});
