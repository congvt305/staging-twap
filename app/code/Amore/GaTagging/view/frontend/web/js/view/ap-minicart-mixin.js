define([
    'jquery',
], function ($) {
    'use strict';

    var mixin = {
        initialize: function () {
            setTimeout(() => {
                $('#top-cart-btn-checkout').on( 'click', function() {
                    window.dataLayer.push({'event': 'purchasecartbtn'});
                });
            }, 2000);
            return this._super();
        },
    };

    return function (target) {
        return target.extend(mixin);
    };
});
