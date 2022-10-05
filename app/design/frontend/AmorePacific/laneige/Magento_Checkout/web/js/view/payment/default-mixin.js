define([
    'ko',
    'jquery'
], function (ko, $) {
    'use strict';

    var mixin = {
        /**
         * @return {Boolean}
         */
        selectPaymentMethod: function () {
            $('#place-order-button').prop('disabled', false);
            this._super();
            return true;
        },
    };

    return function (selectPaymentMethod) {
        return selectPaymentMethod.extend(mixin);
    };
});
