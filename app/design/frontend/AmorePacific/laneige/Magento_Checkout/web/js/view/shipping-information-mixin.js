define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
], function (ko, $, quote, stepNavigator) {
    'use strict';

    var mixin = {
        isVisibleInShipping: function () {
            return !quote.isVirtual() && !stepNavigator.isProcessed('shipping');
        },
    };

    return function (shippingInformation) {
        return shippingInformation.extend(mixin);
    };
});
