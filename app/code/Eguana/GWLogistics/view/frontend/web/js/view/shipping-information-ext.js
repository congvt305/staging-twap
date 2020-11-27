define([
    'Magento_Checkout/js/model/quote'
], function (quote) {
    'use strict';

    var cvsPickupShippingInformation = {
        defaults: {
            template: 'Eguana_GWLogistics/shipping-information'
        },

        /**
         * Get shipping method title based on delivery method.
         *
         * @return {String}
         */
        getShippingMethodTitle: function () {
            var shippingMethod = quote.shippingMethod(),
                locationName = '',
                title;

            if (!this.isCvsPickup()) {

                return this._super();
            }

            title = shippingMethod['carrier_title'] + ' - ' + shippingMethod['method_title'];

            if (quote.shippingAddress().firstname !== undefined) {
                locationName = quote.shippingAddress().firstname + ' ' + quote.shippingAddress().lastname;
                title += ' "' + locationName + '"';
            }

            return title;
        },

        /**
         * Get is store pickup delivery method selected.
         *
         * @returns {Boolean}
         */
        isCvsPickup: function () {
            var shippingMethod = quote.shippingMethod(),
                isCvsPickup = false;

            if (shippingMethod !== null) {
                isCvsPickup = shippingMethod['carrier_code'] === 'gwlogistics' &&
                    shippingMethod['method_code'] === 'CVS';
            }

            return isCvsPickup;
        }
    };

    return function (shippingInformation) {
        return shippingInformation.extend(cvsPickupShippingInformation);
    };
});