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
            },

            /**
             * @private
             */
            _redirect: function (url) {
                var result = this._super.bind(this, url);
                if (_.contains(url, '/checkout/cart')) {
                    $(document.body).on('buyNowGaEvent', result.bind(this));

                    setTimeout(result, 5000);
                } else {
                    result();
                }
            }
        });

        return $.mage.catalogAddToCart;
    };
});
