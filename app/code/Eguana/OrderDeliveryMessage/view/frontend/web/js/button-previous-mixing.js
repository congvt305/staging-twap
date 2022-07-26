define([], function () {
    'use strict';

    return function (Component) {
        return Component.extend({

            getShoppingCartUrl: function () {
                return window.checkoutConfig.cartUrl;
            }
        });
    };
});
