define([], function () {
    'use strict';
    return function (Component) {
        return Component.extend({
            getShoppingCartUrl: function () {
                return window.checkoutConfig.cartUrl;
            },
            goBack: function() {
                window.location.href = window.checkoutConfig.cartUrl;
            }
        });
    };
});
