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
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/action/select-shipping-address'
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
    customer,
    checkoutDataResolver,
    selectShippingAddress
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Eguana_GWLogistics/cvs-pickup',
            deliveryMethodSelectorTemplate: 'Eguana_GWLogistics/delivery-method-selector',
            deliveryMethodMessageTemplate: 'Eguana_GWLogistics/delivery-method-message',
            isVisible: false,
            isAvailable: false,
            isCvsPickupSelected: ko.observable(false),
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
            }),
                isUpdateCvs = $.cookieStorage.get('updatecvs'); //check if is select cvs address data so apply cvs address data

            shippingStep.isVisible.subscribe(function (isShippingVisible) {
                this.isVisible(this.isAvailable && isShippingVisible);
            }, this);
            this.isVisible(this.isAvailable && shippingStep.isVisible());
            if (isUpdateCvs) {
                this.preselectLocation();
            }
            this.isCvsPickupSelected.subscribe(function () {
                var isUpdateCvs = $.cookieStorage.get('updatecvs');
                if (isUpdateCvs) {
                    this.preselectLocation();
                    $.cookieStorage.set('updatecvs', null);
                } else {
                    if (shippingStep.isVisible()) {
                        //set select shipping null for cvs to avoid miss data when change back home delivery tab
                        pickupLocationsService.selectForShipping({});
                    }
                }
            }, this);
        },

        preselectLocation: function () {
            var selectedLocation = pickupLocationsService.selectedLocation();
            if (this.isCvsPickupSelected()) {
                if (selectedLocation) {
                    pickupLocationsService.selectForShipping(selectedLocation);
                }
                pickupLocationsService.getLocation()
                    .then(function (location) {
                        if (!location.CVSAddress) {
                            return;
                        }
                        pickupLocationsService.selectForShipping(location);
                    });
            }

    },

        /**
         * Get url param to make sure it redirect by mobile to decide should reset cvs or not
         *
         * @returns void
         */
        getUrlParameter : function getUrlParameter(sParam) {
            var sPageURL = window.location.pathname,
                sURLVariables = sPageURL.split('/'),
                i;

            for (i = 0; i < sURLVariables.length; i++) {
                if (sURLVariables[i] === sParam) {
                    return true;
                }
            }
            return false;
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
            ), nonPickupShippingAddress;

            checkoutData.setSelectedShippingAddress(this.lastSelectedNonPickUpShippingAddress);
            this.selectShippingMethod(nonPickupShippingMethod);
            //force set shipping address when click back to home delivery tab
            if (this.isCvsPickupAddress(quote.shippingAddress())) {
                nonPickupShippingAddress = checkoutDataResolver.getShippingAddressFromCustomerAddressList();

                if (nonPickupShippingAddress) {
                    selectShippingAddress(nonPickupShippingAddress);
                    checkoutData.setSelectedShippingAddress(
                        quote.shippingAddress().getKey()
                    );
                }
            }
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
            this.lastSelectedNonPickUpShippingAddress = checkoutData.getSelectedShippingAddress();
            this.selectShippingMethod(pickupShippingMethod);
        },

        /**
         * @param {Object} shippingMethod
         */
        selectShippingMethod: function (shippingMethod) {
            if (!stepNavigator.isProcessed('shipping')) {
                selectShippingMethodAction(shippingMethod);
                checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);
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

        shouldVisible: function() {
            if (customer.isLoggedIn()) {
                return true;
            }

            if (typeof window.checkoutConfig.gwlogistics.guest_cvs_shipping_method_enabled !== 'undefined') {
                return window.checkoutConfig.gwlogistics.guest_cvs_shipping_method_enabled;
            } else {
                return false;
            }
        },

        isVnWebsite: function () {
            let websiteId = window.checkout.websiteId;
            if (websiteId == 8) {
                return 0;
            } else {
                return 1;
            }
        },
        showFormPopUp: function (){
            $(".action.action-show-popup.amcheckout-button.-new-address").trigger("click");
        },
    });
});
