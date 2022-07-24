define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Checkout/js/action/set-shipping-information'
], function ($, Component, ko, setShippingInformationAction) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_CheckoutCore/package-option',
            economyPackage: ko.observable(true),
            giftPackage: ko.observable(false),
        },
        initialize: function () {
            this._super();
        },

        /**
         * select economy package
         */
        selectEconomyPackage: function() {
            this.economyPackage(true);
            this.giftPackage(false);
            $('#economy-package').attr('checked', true);
            setShippingInformationAction();
        },

        /**
         * select gift package
         */
        selectGiftPackage: function() {
            this.giftPackage(true);
            this.economyPackage(false);
            $('#gift-package').attr('checked', true);
            setShippingInformationAction();
        }
    });
});
