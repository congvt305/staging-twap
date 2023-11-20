define([
    'jquery',
    'Magento_Checkout/js/action/place-order'
], function (
    $,
    placeOrderAction
) {
    'use strict';
    var mixin = {
        getPlaceOrderDeferredObject: function () {
            if (this.index === 'cashondelivery') {
                return $.when(
                    placeOrderAction(this.getData(), null, this.messageContainer)
                );
            } else {
                this._super();
            }
        }
    };
    return function (target) {
        return target.extend(mixin);
    };
});
