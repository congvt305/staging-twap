/**
 * @author     Abbas
 */

define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    return function (Component) {
        return Component.extend({
            defaults: {
                template: 'Eguana_OrderDeliveryMessage/shipping'
            },
            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {

                var superResult = this._super();
                var validate_enabled = parseInt(window.checkoutConfig.validate_delivery_message_enabled);
                if (validate_enabled) {
                    if (superResult) {
                        var delivery_message = '[name="delivery_message"]';

                        delivery_message = $(delivery_message).val();
                        $(".delivery-message-error").css("display", "none");
                        if (delivery_message.length > 512) {
                            $(".delivery-message-error").css("display", "block");
                            return false;
                        }
                    }
                }

                return superResult;
            }
        });
    };
});
