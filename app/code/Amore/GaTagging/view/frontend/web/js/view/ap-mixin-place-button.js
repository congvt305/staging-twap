define([
    'jquery',
    'Magento_Checkout/js/model/quote'
], function ($, quote) {
    'use strict';

    var mixin = {
        placeOrder: function () {
            if (quote.paymentMethod() && quote.shippingMethod() || quote.paymentMethod() && quote.isVirtual()) {
                console.log("DataLayer push event: 'orderbtn'");
                window.dataLayer.push({'event': 'orderbtn'});
            }

            return this._super();
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
