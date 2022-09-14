/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

/**
 * Customer store credit(balance) application
 */
/*global define,alert*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/error-processor',
        'mage/storage',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/action/get-payment-information'
    ],
    function (ko, $, quote, urlManager, errorProcessor,  storage, getTotalsAction, paymentInformation) {
        'use strict';
        return function (coupon, isApplied) {
            var quoteId = quote.getQuoteId();
            var url = urlManager.getApplyCouponUrl(coupon, quoteId);
            return storage.put(
                url,
                {},
                false
            ).done(
                function (response) {
                    if (response) {
                        var deferred = $.Deferred();
                        isApplied(true);
                        getTotalsAction([], deferred);
                        paymentInformation(deferred);
                    }
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            );
        };
    }
);

