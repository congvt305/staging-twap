/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'knockout',
    'Magento_Checkout/js/model/quote',
    'Eguana_GWLogistics/js/model/cvs-address-finder',
    'mage/storage',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/action/select-shipping-address',
    'underscore',
    'mage/translate',
    'Magento_Checkout/js/model/url-builder',
], function (
    $,
    ko,
    quote,
    cvsAddressFinder,
    storage,
    customer,
    customerData,
    checkoutData,
    addressConverter,
    selectShippingAddressAction,
    _,
    $t,
    urlBuilder
) {
    'use strict';

    var defaultCountry = window.checkoutConfig.defaultCountryId,
        countryData = customerData.get('directory-data');

    return {
        isLoading: ko.observable(false),
        selectedLocation: ko.observable({}),
        locationsCache: [],

        getLocation: function () {
            var serviceUrl;
            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/gwlogistics/guest/:cartId/checkout-cvs-location', {
                    cartId: quote.getQuoteId()
                });
            } else {
                serviceUrl = urlBuilder.createUrl('/gwlogistics/mine/checkout-cvs-location', {});
            }

            this.isLoading(true);

            return storage
                .get(serviceUrl, {}, false)
                .then(function (location) {
                    return this.formatAddress(location);
                }.bind(this))
                .fail(function (response) {
                    this.processError(response);
                    return [];
                }.bind(this))
                .always(function () {
                    this.isLoading(false);
                }.bind(this));
        },

        formatAddress: function (location) {
            // console.log('formatAddress 2: ', JSON.parse(location).CVSStoreName);
            return JSON.parse(location);
        },

        selectForShipping: function (location) {
            // console.log('selectForShipping2 : ', location);

            var cvsAddress = cvsAddressFinder.getAddressDataFromString(location.CVSAddress);
            // console.log('selectForShipping 5 : ', cvsAddress);
            var address = $.extend(
                {},
                addressConverter.formAddressDataToQuoteAddress({
                    firstname: location.firstname,
                    lastname: location.lastname,
                    street: [cvsAddress.street],
                    city: cvsAddress.city,
                    city_id: cvsAddress.city_id,
                    postcode: cvsAddress.postcode,
                    'country_id': defaultCountry,
                    telephone: location.mobileNumber,
                    'region_id': cvsAddress.regionId,
                    'save_in_address_book': 0
                }),
                {
                    /**
                     * Is address can be used for billing
                     *
                     * @return {Boolean}
                     */
                    canUseForBilling: function () {
                        // return false;
                        return true;
                    },

                    /**
                     * Returns address type
                     *
                     * @return {String}
                     */
                    getType: function () {
                        return 'cvs-pickup-address';
                    },
                    'extension_attributes': {
                        // 'pickup_location_code': location['pickup_location_code']
                    }
                }
            );

            this.selectedLocation(location);
            selectShippingAddressAction(address);
            checkoutData.setSelectedShippingAddress(address.getKey());
        },

        selectForShippingMobile: function (location) {
            var address = $.extend(
                {},
                addressConverter.formAddressDataToQuoteAddress({
                    firstname: location.firstname,
                    lastname: location.lastname,
                    street: [location.street],
                    city: location.city,
                    postcode: location.postcode,
                    'country_id': location.countryId,
                    telephone: location.mobileNumber,
                    'region_id': location.regionId,
                    'save_in_address_book': 0
                }),
                {
                    /**
                     * Is address can be used for billing
                     *
                     * @return {Boolean}
                     */
                    canUseForBilling: function () {
                        return false;
                        // return true;
                    },

                    /**
                     * Returns address type
                     *
                     * @return {String}
                     */
                    getType: function () {
                        return 'cvs-pickup-address';
                    },
                    'extension_attributes': {
                        // 'pickup_location_code': location['pickup_location_code']
                    }
                }
            );

            this.selectedLocation(location);
            selectShippingAddressAction(address);
            checkoutData.setSelectedShippingAddress(address.getKey());
        },





        /**
         * Get country name by id.
         *
         * @param {*} countryId
         * @return {String}
         */
        getCountryName: function (countryId) {
            return countryData()[countryId] !== undefined ?
                countryData()[countryId].name
                : ''; //eslint-disable-line
        },

        /**
         * Returns region name based on given country and region identifiers.
         *
         * @param {String} countryId - Country identifier.
         * @param {String} regionId - Region identifier.
         */
        getRegionName: function (countryId, regionId) {
            var regions = countryData()[countryId] ?
                countryData()[countryId].regions
                : null;

            return regions && regions[regionId] ? regions[regionId].name : '';
        },

        /**
         * Process response errors.
         *
         * @param {Object} response
         * @returns void
         */
        processError: function (response) {
            // console.log('process errors: ', response);
            // var expr = /([%])\w+/g,
            //     error;
            //
            // if (response.status === 401) {
            //     //eslint-disable-line eqeqeq
            //     window.location.replace(url.build('customer/account/login/'));
            //
            //     return;
            // }
            //
            // try {
            //     error = JSON.parse(response.responseText);
            // } catch (exception) {
            //     error = $t(
            //         'Something went wrong with your request. Please try again later.'
            //     );
            // }
            //
            // if (error.hasOwnProperty('parameters')) {
            //     error = error.message.replace(expr, function (varName) {
            //         varName = varName.substr(1);
            //
            //         if (error.parameters.hasOwnProperty(varName)) {
            //             return error.parameters[varName];
            //         }
            //
            //         return error.parameters.shift();
            //     });
            // }
        }
    };
});