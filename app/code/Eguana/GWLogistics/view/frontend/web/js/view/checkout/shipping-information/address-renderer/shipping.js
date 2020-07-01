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
        getRegionNameByCode: function (countryId, regionCode) {
            var result = regionCode;
            var countryRegions = countryData()[countryId].regions || {};

            if (_.size(countryRegions) > 0) {
                var region = _.filter(countryRegions, (function (element) {
                        return element.code === regionCode;
                    })
                );

                if (region.length > 0) {
                    result = region[0].name;
                }
            }

            return result;
        },
        getTemplate: function () {
            var cvsLocation = cvsLocation.getSelectedCvsLocation();
            console.log('finally...', cvsLocation);
            if (cvsLocation) {
                return 'Eguana_GWLogistics/checkout/shipping/address-renderer/pickup-location';
            }
            return this.template;
        }
    });

});
