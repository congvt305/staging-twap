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

            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {

                var superResult = this._super();

                if (superResult) {
                    var delivery_message = '[name="delivery_message"]';

                    delivery_message = $(delivery_message).val();

                    if (delivery_message.length > 512) {
                        this.errorValidationMessage(
                            $t('Delivery Message is too long. Please type less than 512 characters.')
                        );

                        return false;
                    }
                }
                return superResult;
            }
        });
    };
});
