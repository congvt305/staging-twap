define([
    'underscore',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/quote',
    'Eguana_GWLogistics/js/model/cvs-location'
], function (
    _,
    Component,
    customerData,
    checkoutData,
    quote,
    cvsLocation
) {
    'use strict';

    var countryData = customerData.get('directory-data');

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-information/address-renderer/default'
        },
        cvsLocation: cvsLocation,
        /**
         * @param {*} countryId
         * @return {String}
         */
        getCountryName: function (countryId) {
            return countryData()[countryId] != undefined ? countryData()[countryId].name : ''; //eslint-disable-line
        },
        getTemplate: function () {
            console.log(quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code']);
            console.log(this.template);
            var addressTemplate = 'Eguana_GWLogistics/checkout/shipping-information/address-renderer/cvs-address';
           return quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] === 'gwlogistics_CVS' ? addressTemplate : this.template
        },
        getSelectedCvsLocation: function () {
            return this.cvsLocation.getCvsLocation()
        }
    });

});
