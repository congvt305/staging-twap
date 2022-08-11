define([
], function () {
    'use strict';
    return function(Component) {
        return Component.extend({
            goBack: function () {
                window.location.href = window.checkoutConfig.cartUrl;
            }
        });
    }
});
