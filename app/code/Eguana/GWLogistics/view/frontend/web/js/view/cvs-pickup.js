/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'underscore',
    'jquery',
    'knockout',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/shipping-rate-service',
    'Eguana_GWLogistics/js/model/shipping-rate-processor/cvs-pickup-address',
    // 'Eguana_GWLogistics/js/model/cvs-location',
    'Eguana_GWLogistics/js/model/cvs-location-service',
    'Magento_Customer/js/model/customer'
], function (
    Component,
    _,
    $,
    ko,
    registry,
    quote,
    selectShippingMethodAction,
    checkoutData,
    shippingService,
    stepNavigator,
    shippingRateService,
    shippingRateProcessor,
    // cvsLocation,
    pickupLocationsService,
    customer
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Eguana_GWLogistics/cvs-pickup',
            deliveryMethodSelectorTemplate: 'Eguana_GWLogistics/delivery-method-selector',
            deliveryMethodMessageTemplate: 'Eguana_GWLogistics/delivery-method-message',
            isVisible: false,
            isAvailable: false,
            isCvsPickupSelected: false,
            rate: {
                'carrier_code': 'gwlogistics',
                'method_code': 'CVS'
            },
            defaultCountry: window.checkoutConfig.defaultCountryId,
            rates: shippingService.getShippingRates(),
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();

            shippingRateService.registerProcessor('cvs-pickup-address', shippingRateProcessor);

            quote.shippingAddress.subscribe(function (shippingAddress) {
                this.convertAddressType(shippingAddress);
            }, this);
            this.convertAddressType(quote.shippingAddress());

            this.syncWithShipping();
        },

        /**
         * Init component observable variables
         *
         * @return {exports}
         */
        initObservable: function () {
            this._super().observe(['isVisible']);

            this.isCvsPickupSelected = ko.pureComputed(function () {
                return _.isMatch(quote.shippingMethod(), this.rate);
            }, this);

            this.isAvailable = ko.pureComputed(function () {
                return _.findWhere(this.rates(), {
                    'carrier_code': this.rate['carrier_code'],
                    'method_code': this.rate['method_code']
                });
            }, this);

            return this;
        },

        /**
         * Synchronize store pickup visibility with shipping step.
         *
         * @returns void
         */
        syncWithShipping: function () {
            var shippingStep = _.findWhere(stepNavigator.steps(), {
                code: 'shipping'
            });

            shippingStep.isVisible.subscribe(function (isShippingVisible) {
                this.isVisible(this.isAvailable && isShippingVisible);
            }, this);
            this.isVisible(this.isAvailable && shippingStep.isVisible());
            this.isCvsPickupSelected.subscribe(function () {
                if (shippingStep.isVisible()) {
                    //set select shipping null for cvs to avoid miss data when change back home delivery tab
                    pickupLocationsService.selectForShipping({});
                }
            }, this);
        },

        /**
         * @returns void
         */
        selectShipping: function () {
            var nonPickupShippingMethod = _.find(
                this.rates(),
                function (rate) {
                    return (
                        rate['carrier_code'] !== this.rate['carrier_code'] &&
                        rate['method_code'] !== this.rate['method_code']
                    );
                },
                this
            );

            this.selectShippingMethod(nonPickupShippingMethod);

        },

        /**
         * @returns void
         */
        selectCvsPickup: function () {
            var pickupShippingMethod = _.findWhere(
                this.rates(),
                {
                    'carrier_code': this.rate['carrier_code'],
                    'method_code': this.rate['method_code']
                },
                this
            );
            $('#delivery_message').val('');
            pickupLocationsService.selectedLocation(ko.observable({}));
            this.selectShippingMethod(pickupShippingMethod);
        },

        /**
         * @param {Object} shippingMethod
         */
        selectShippingMethod: function (shippingMethod) {
            if (!stepNavigator.isProcessed('shipping')) {
                selectShippingMethodAction(shippingMethod);
                checkoutData.setSelectedShippingAddress(
                    quote.shippingAddress().getKey()
                );
            }
        },

        /**
         * @param {Object} shippingAddress
         * @returns void
         */
        convertAddressType: function (shippingAddress) {
            if (
                !this.isCvsPickupAddress(shippingAddress) &&
                this.isCvsPickupSelected()
            ) {
                quote.shippingAddress(
                    $.extend({}, shippingAddress, {
                        /**
                         * Is address can be used for billing
                         *
                         * @return {Boolean}
                         */
                        canUseForBilling: function () {
                            return true;
                        },

                        /**
                         * Returns address type
                         *
                         * @return {String}
                         */
                        getType: function () {
                            return 'cvs-pickup-address';
                        }
                    })
                );
            }
        },

        /**
         * @param {Object} address
         * @returns {Boolean}
         */
        isCvsPickupAddress: function (address) {
            return address.getType() === 'cvs-pickup-address';
        },

        isLoggedIn: function () {
            return customer.isLoggedIn();
        },

        isVnWebsite: function () {
            let websiteId = window.checkout.websiteId;
            if (websiteId == 8) {
                return 0;
            } else {
                return 1;
            }
        },
    });
});
