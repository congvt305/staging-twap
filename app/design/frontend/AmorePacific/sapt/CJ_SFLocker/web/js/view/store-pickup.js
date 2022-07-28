define([
    'uiComponent',
    'underscore',
    'jquery',
    'knockout',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/shipping-rate-service',
    'Magento_InventoryInStorePickupFrontend/js/model/shipping-rate-processor/store-pickup-address',
    'CJ_SFLocker/js/model/pickup-locations-service',
    'Magento_InventoryInStorePickupFrontend/js/model/pickup-address-converter',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/action/recollect-shipping-rates',
    'Magento_Checkout/js/action/get-payment-information'
], function (
    Component,
    _,
    $,
    ko,
    registry,
    quote,
    selectShippingMethodAction,
    checkoutData,
    selectPaymentMethodAction,
    shippingService,
    stepNavigator,
    shippingRateService,
    shippingRateProcessor,
    pickupLocationsService,
    pickupAddressConverter,
    checkoutDataResolver,
    selectShippingAddress,
    setShippingInformationAction,
    recollectShippingRatesAction,
    getPaymentInformationAction
) {
    'use strict';

    var cities = window.checkoutConfig.cities;
    return Component.extend({
        defaults: {
            template: 'CJ_SFLocker/store-pickup',
            deliveryMethodSelectorTemplate: 'CJ_SFLocker/delivery-method-selector',
            isVisible: false,
            isAvailable: false,
            isStorePickupSelected: false,
            isStorePickupSfSelected: ko.observable(false),
            isHomeDeliverySelected: ko.observable(false),
            pickupRate: {
                'carrier_code': 'instore',
                'method_code': 'pickup'
            },
            sfLockerRate: {
                'carrier_code': 'instoresf',
                'method_code': 'pickup'
            },
            homeDeliveryRate: {
                'carrier_code': 'vlogic',
                'method_code': 'tablerate'
            },
            shippingElm: '#home-delivery-selector',
            shippingMethodElm: '#opc-shipping_method',
            shippingAddressListElm: '.shipping-address-item .action-select-shipping-item',
            codPayment: '#cashondelivery-payment',
            rates: shippingService.getShippingRates(),
            inStoreMethod: null,
            lastSelectedNonPickUpShippingAddress: null
        },
        currentStoreCode: ko.observable(''),
        currentSfCode: ko.observable(''),
        cities: ko.observableArray(cities),

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();

            shippingRateService.registerProcessor('store-pickup-address', shippingRateProcessor);
            this.convertAddressType(quote.shippingAddress());
            this.isStorePickupSelected.subscribe(function () {
                this.preselectLocation();
            }, this);
            this.isStorePickupSfSelected.subscribe(function () {
                this.preselectLocation();
            }, this);
            this.preselectLocation();
        },

        /**
         * Init component observable variables
         *
         * @return {exports}
         */
        initObservable: function () {
            this._super().observe(['isVisible']);
            this.isHomeDeliverySelected = ko.pureComputed(function () {
                return _.isMatch(quote.shippingMethod(), this.homeDeliveryRate);
            }, this);
            this.isStorePickupSelected = ko.pureComputed(function () {
                return _.isMatch(quote.shippingMethod(), this.pickupRate);
            }, this);

            this.isStorePickupSfSelected = ko.pureComputed(function () {
                return _.isMatch(quote.shippingMethod(), this.sfLockerRate);
            }, this);
            return this;
        },

        /**
         * @returns void
         */
        selectShipping: function () {
            var nonPickupShippingMethod = _.find(
                    this.rates(),
                    {
                        'carrier_code': this.homeDeliveryRate['carrier_code'],
                        'method_code': this.homeDeliveryRate['method_code']
                    },
                    this
                ),
                nonPickupShippingAddress;

            if (!nonPickupShippingMethod) {
                recollectShippingRatesAction();
                var deferred = $.Deferred(),
                    self = this;
                getPaymentInformationAction(deferred);
                $.when(deferred).done(function () {
                    nonPickupShippingMethod = _.find(
                        shippingService.getShippingRates()(),
                        {
                            'carrier_code': self.homeDeliveryRate['carrier_code'],
                            'method_code': self.homeDeliveryRate['method_code']
                        },
                        this
                    );
                    self.selectShippingMethod(nonPickupShippingMethod);
                });
            }

            $(this.shippingAddressListElm).prop('disabled', false);
            checkoutData.setSelectedShippingAddress(this.lastSelectedNonPickUpShippingAddress);
            this.selectShippingMethod(nonPickupShippingMethod);
            $('.checkout-index-index .amcheckout-wrapper .shipping-address-item').removeClass('disabled');
            if (this.isStorePickupAddress(quote.shippingAddress())) {
                nonPickupShippingAddress = checkoutDataResolver.getShippingAddressFromCustomerAddressList();

                if (nonPickupShippingAddress) {
                    selectShippingAddress(nonPickupShippingAddress);
                }
            }
            $(this.codPayment).show();
        },

        /**
         * @returns void
         */
        selectStorePickup: function () {
            var pickupShippingMethod = _.findWhere(
                this.rates(),
                {
                    'carrier_code': this.pickupRate['carrier_code'],
                    'method_code': this.pickupRate['method_code']
                },
                this
            );
            $(this.shippingAddressListElm).prop('disabled', true);
            $('.checkout-index-index .amcheckout-wrapper .shipping-address-item').addClass('disabled');
            this.lastSelectedNonPickUpShippingAddress = checkoutData.getSelectedShippingAddress();
            checkoutData.setSelectedShippingAddress(null);
            this.selectShippingMethod(pickupShippingMethod);
            this.preselectLocation();
            $(this.codPayment).hide();
            if(checkoutData.getSelectedPaymentMethod() == 'cashondelivery'){
                this.removePaymentMethod();
            }
        },

        /**
         * @returns void
         */
        selectStorePickupSf: function () {
            var pickupShippingMethod = _.findWhere(
                this.rates(),
                {
                    'carrier_code': this.sfLockerRate['carrier_code'],
                    'method_code': this.sfLockerRate['method_code']
                },
                this
            );
            $(this.shippingAddressListElm).prop('disabled', true);
            $('.checkout-index-index .amcheckout-wrapper .shipping-address-item').addClass('disabled');
            this.lastSelectedNonPickUpShippingAddress = checkoutData.getSelectedShippingAddress();
            checkoutData.setSelectedShippingAddress(null);
            this.selectShippingMethod(pickupShippingMethod);
            this.preselectLocation();
            $(this.codPayment).hide();
            if(checkoutData.getSelectedPaymentMethod() == 'cashondelivery'){
                this.removePaymentMethod();
            }
        },

        /**
         * @param {Object} shippingMethod
         */
        selectShippingMethod: function (shippingMethod) {
            if (shippingMethod) {
                selectShippingMethodAction(shippingMethod);
                checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);
            }

        },

        /**
         * @param {Object} shippingAddress
         * @returns void
         */
        convertAddressType: function (shippingAddress) {
            var pickUpAddress;

            if (
                !this.isStorePickupAddress(shippingAddress) &&
                (this.isStorePickupSelected() || this.isStorePickupSfSelected())
            ) {
                pickUpAddress = pickupAddressConverter.formatAddressToPickupAddress(shippingAddress);

                if (quote.shippingAddress() !== pickUpAddress) {
                    quote.shippingAddress(pickUpAddress);
                }
            }
        },

        /**
         * @returns void
         */
        preselectLocation: function () {
            var selectedLocation,
                shippingAddress,
                selectedPickupAddress,
                customAttributes,
                selectedSource,
                selectedSourceCode;

            if (!this.isStorePickupSelected() && !this.isStorePickupSfSelected()) {
                var self = this;
                setTimeout(function (){
                    if (self.isStorePickupSfSelected() || self.isStorePickupSelected()) {
                        $(self.codPayment).hide();
                        if(checkoutData.getSelectedPaymentMethod() == 'cashondelivery'){
                            self.removePaymentMethod();
                        }
                    } else {
                        $(self.codPayment).show();
                    }
                },2000);
                return;
            }
            selectedLocation = pickupLocationsService.selectedLocation();

            if (selectedLocation) {
                if (this.currentStoreCode() && this.isStorePickupSelected()) {
                    selectedSourceCode = this.currentStoreCode();
                }
                if (this.currentSfCode() && this.isStorePickupSfSelected()) {
                    selectedSourceCode = this.currentSfCode();
                }
            } else {
                shippingAddress = quote.shippingAddress();
                customAttributes = shippingAddress.customAttributes || [];
                selectedSource = _.findWhere(customAttributes, {
                    'attribute_code': 'sourceCode'
                });

                if (selectedSource) {
                    selectedSourceCode = selectedSource.value;
                }

                if (!selectedSourceCode) {
                    selectedSourceCode = this.getPickupLocationCodeFromAddress(shippingAddress);
                }

                if (!selectedSourceCode) {
                    selectedPickupAddress = pickupLocationsService.getSelectedPickupAddress();
                    selectedSourceCode = this.getPickupLocationCodeFromAddress(selectedPickupAddress);
                }
            }

            var self = this;
            if (selectedSourceCode && this.validateStore(selectedSourceCode)) {
                pickupLocationsService
                    .getLocation(selectedSourceCode)
                    .then(function (location) {
                        if (location.fax == '1') {
                            self.currentStoreCode(location.pickup_location_code);
                        }
                        if (location.fax == '2' || location.fax == '3') {
                            self.currentSfCode(location.pickup_location_code);
                        }
                        pickupLocationsService.selectForShipping(location);
                        setShippingInformationAction();
                    });
            }
            setTimeout(function (){
                if (self.isStorePickupSfSelected() || self.isStorePickupSelected()) {
                    $(self.codPayment).hide();
                    if(checkoutData.getSelectedPaymentMethod() == 'cashondelivery'){
                        self.removePaymentMethod();
                    }
                } else {
                    $(self.codPayment).show();
                }
            },2000);
        },

        validateStore: function (selectedSourceCode) {
            var stores;
            if (this.isStorePickupSelected()) {
                stores = window.checkoutConfig.ap_stores;
            } else if (this.isStorePickupSfSelected()) {
                stores = window.checkoutConfig.sf_locker;
            }
            var selectedStore = ko.utils.arrayFilter(stores, function (store) {
                return store.value === selectedSourceCode;
            });
            if (selectedStore.length) {
                return true;
            }
            return false;
        },

        /**
         * @param {Object} address
         * @returns {Boolean}
         */
        isStorePickupAddress: function (address) {
            return address.getType() === 'store-pickup-address';
        },

        /**
         * @param {Object} address
         * @returns {String|null}
         */
        getPickupLocationCodeFromAddress: function (address) {
            if (address &&
                !_.isEmpty(address.extensionAttributes) &&
                address.extensionAttributes['pickup_location_code']
            ) {
                return address.extensionAttributes['pickup_location_code'];
            }

            return null;
        },

        /**
         * choose home delivery method if shipping method is null
         */
        isShippingMethodNull: function () {
            if (!quote.shippingMethod()) {
                this.selectShipping();
            }
        },

        removePaymentMethod: function (){
            selectPaymentMethodAction(null);
            checkoutData.setSelectedPaymentMethod(null);
        }

    });
});
