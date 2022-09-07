/**
 * @author     Abbas
 */

define([
    'jquery',
    'mage/translate',
    'uiRegistry',
    'Magento_Customer/js/model/customer',
    'underscore',
    'Magento_Checkout/js/model/quote'
], function ($, $t, registry, customer, _, quote) {
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
                var messageContainer = registry.get('checkout.errors').messageContainer;

                if (customer.isLoggedIn()
                    && (
                        !quote.shippingAddress().lastname ||
                        !quote.shippingAddress().firstname ||
                        !quote.shippingAddress().street ||
                        !quote.shippingAddress().telephone
                    )
                ) {
                    messageContainer.addErrorMessage({
                        message: $t('Please select delivery method : home delivery / cvs delivery. Thanks')
                    });

                    return false;
                }

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
