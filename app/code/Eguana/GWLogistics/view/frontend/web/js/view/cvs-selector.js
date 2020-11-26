/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'uiComponent',
    'Eguana_GWLogistics/js/action/open-cvs-map',
    // 'Eguana_GWLogistics/js/model/cvs-location',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/action/set-shipping-information',
    'Eguana_GWLogistics/js/model/cvs-location-service',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/validation'
], function (
    $,
    _,
    Component,
    openCvsMapAction,
    // cvsLocation,
    quote,
    customer,
    stepNavigator,
    addressConverter,
    setShippingInformationAction,
    pickupLocationService,
    checkoutData,
    fullScreenLoader
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Eguana_GWLogistics/cvs-selector',
            cvsAddressFormTemplate: 'Eguana_GWLogistics/form/cvs-address',
            selectedLocation: pickupLocationService.selectedLocation,
            quoteIsVirtual: quote.isVirtual,
            isLoading: pickupLocationService.isLoading,
            lastname: null,
            firstname: null,
            mobileNumber: null,
            selectedCvs: null,
            cvsFormSelector: '#checkout-step-cvs-selector form[data-role=cvs-map-load-form]',
            loginFormSelector: '#cvs-selector form[data-role=email-with-possible-login]',
        },

        /**
         * Init component
         *
         * @return {exports}
         */
        initialize: function () {
            this._super();
            // if (window.location.pathname === '/checkout/index/index/') {

            // cvsLocation.selectCvsLocation();
            // this.selectedLocation = cvsLocation.getCvsLocation();
            // this.selectCvsAddressForShipping();

            return this;

            // }

            // cvsLocation.clear();
        },

        /**
         * Set shipping information handler
         */
        setPickupInformation: function () {
            var shippingAddress;

            this.selectCvsAddressForShipping();
            shippingAddress = quote.shippingAddress();
            console.log('setPickupInformation 7', shippingAddress);
            if (this.validatePickupInformation()) {
                shippingAddress = addressConverter.quoteAddressToFormAddressData(shippingAddress);
                checkoutData.setShippingAddressFromData(shippingAddress);
                setShippingInformationAction().done(function () {
                    stepNavigator.next();
                });
            }
        },
        selectCvsAddressForShipping: function () {
            var address = this.selectedLocation();
            // console.log('selectCvsAddressForShipping1 : ', address);

            if (!address) {
                return;
            }

            _.extend(address, {'firstname': this.firstname, 'lastname': this.lastname, 'mobileNumber': this.mobileNumber});
            pickupLocationService.selectForShipping(address);
        },

        selectCvsAddressForShippingMobile: function () {
            var address = quote.shippingAddress();
            if (!address) {
                return;
            }

            _.extend(address, {'firstname': this.firstname, 'lastname': this.lastname, 'mobileNumber': this.mobileNumber});
            pickupLocationService.selectForShippingMobile(address);
        },

        openFamiMap: function () {
            this.selectedCvs = 'FAMI';
            this.openMapWindow();
        },

        openUnimartMap: function () {
            this.selectedCvs = 'UNIMART';
            this.openMapWindow();
        },


        openMapWindow: function () {
            // this.selectCvsAddressForShipping();
            // this.selectCvsAddressForShippingMobile();

            var deferred = $.Deferred();
            openCvsMapAction(deferred, this.selectedCvs);
            $.when(
                deferred
            ).done(
                this.done.bind(this)
            ).fail(
                this.fail.bind(this)
            ).always(
                this.always.bind(this)
            );
        },
        done: function () {
            pickupLocationService.getLocation()
                .then(function (location) {
                    // console.log('preselectLocation api: ', location);
                    pickupLocationService.selectForShipping(location);
                });
        },

        fail: function () {
        },

        always: function () {
            fullScreenLoader.stopLoader();
        },

        getCvsLocation: function () {
            return cvsLocation.getCvsLocation();
        },

        /**
         * @returns {Boolean}
         */
        validatePickupInformation: function () {
            var emailValidationResult,
                cvsStoreNameValidationResult,
                cvsStoreAddressValidationResult,
                mobileValidationResult,
                firstnameValidationResult,
                lastnameValidationResult,
                loginFormSelector = this.loginFormSelector,
                cvsFormSelector = this.cvsFormSelector;


            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = $(loginFormSelector + ' input[name=username]').valid() ? true : false;

                if (!emailValidationResult) {
                    $(this.loginFormSelector + ' input[name=username]').focus();

                    return false;
                }
            }

            $(cvsFormSelector).validation();
            cvsStoreNameValidationResult = $(cvsFormSelector + ' input[name=CVSStoreName]').valid() ? true : false;
            cvsStoreAddressValidationResult = $(cvsFormSelector + ' input[name=CVSAddress]').valid() ? true : false;
            firstnameValidationResult = $(cvsFormSelector + ' input[name=firstname]').valid() ? true : false;
            lastnameValidationResult = $(cvsFormSelector + ' input[name=lastname]').valid() ? true : false;
            mobileValidationResult = $(cvsFormSelector + ' input[name=mobile_number]').valid() ? true : false;

            return cvsStoreNameValidationResult && cvsStoreAddressValidationResult && firstnameValidationResult && lastnameValidationResult && mobileValidationResult;
        },

        isMobile: function () {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        },

        isLineAppBrowser: function () {
            return /(iPhone|iPod|iPad|Android).*AppleWebKit.*Line/i.test(navigator.userAgent);
        },

    });
});
