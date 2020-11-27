define([
    'underscore',
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function (_, $, customerData) {
    'use strict';

    var cacheKey = 'cvs-location-result';
    var sectionData = customerData.get(cacheKey);

    return {
        getCvsLocation: function () {
            return sectionData()['cvs-location'] || [];
        },

        selectCvsLocation: function () {
            this.reloadCheckoutData();
        },

        getSelectedCvsLocation: function () {
            var locations = this.getCvsLocation();
            if (locations.length === 0) {
                return false;
            } else {
                return locations;
            }

        },

        reloadCheckoutData: function () {
            return customerData.reload([cacheKey], true);
        },

        clear: function() {
            customerData.set(cacheKey, {
                'cvs-location': [],
            });
        }
    };

});
