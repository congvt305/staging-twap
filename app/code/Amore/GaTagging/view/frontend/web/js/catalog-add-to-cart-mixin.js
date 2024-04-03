define([
    'underscore',
    'jquery',
    'Amore_GaTagging/js/product/view/bundle-product-info-resolver'
], function (_, $, productInfoResolver) {
    'use strict';

    return function (widget) {

        $.widget('mage.catalogAddToCart', widget, {
            /**
             * @param {jQuery} form
             */
            ajaxSubmit: function (form) {
                var isBundle = !!_.find(form.serializeArray(), function (item) {
                    return item.name.indexOf('bundle_option') !== -1;
                });

                if (isBundle) {
                    this.options.productInfoResolver = productInfoResolver;
                }

                return this._super(form);
            }
        });

        return $.mage.catalogAddToCart;
    };
});
