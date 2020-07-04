define([
    'underscore',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Eguana_GWLogistics/js/model/cvs-location'
], function (
    _,
    Component,
    customerData,
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
            var cvsLocation = this.cvsLocation.getCvsLocation(); // {LogisticsSubType: "UNIMART", CVSStoreID: "991182", CVSStoreName: "馥樺門市", CVSAddress: "台北市南港區三重路23號1樓", CVSTelephone: null}
            if (cvsLocation) {
                return 'Eguana_GWLogistics/checkout/shipping-information/address-renderer/cvs-address';
            }
            return this.template;
        },
        getSelectedCvsLocation: function () {
            return this.cvsLocation.getCvsLocation()
        }
    });

});
