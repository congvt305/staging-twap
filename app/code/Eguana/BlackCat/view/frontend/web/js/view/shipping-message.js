define([
    'uiComponent',
], function (
    Component
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Eguana_BlackCat/shipping-message',
        },
        getShippingMessage: function () {
            return typeof window.checkoutConfig.blackcat.shipping_message !== 'undefined'
                ? window.checkoutConfig.blackcat.shipping_message : '';
        }
    });

});