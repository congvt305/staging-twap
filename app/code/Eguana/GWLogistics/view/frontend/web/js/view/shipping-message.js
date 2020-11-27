define([
    'uiComponent',
], function (
    Component
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Eguana_GWLogistics/shipping-message',
        },
        getShippingMessage: function () {
            return typeof window.checkoutConfig.gwlogistics.shipping_message !== 'undefined'
                ? window.checkoutConfig.gwlogistics.shipping_message : '';
        }
    });

});