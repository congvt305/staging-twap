define([
    'jquery',
], function ($) {
    'use strict';

    /**
     * Dispatch product detail event to GA
     *
     * @param {Object} data
     *
     * @private
     */
    function notify(data) {
        window.dataLayer.push({
            'event': 'productViewContent',
            'ecommerce': {
                'currency': data.currency,
                'details': {
                    'products': [data.products]
                }
            }
        });
    }

    return function (productData) {
        notify(productData);
    };
});
