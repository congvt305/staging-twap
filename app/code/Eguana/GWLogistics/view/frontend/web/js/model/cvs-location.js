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

        getMessage: function () {
            var location = this.getCvsLocation();
            var searchCvsRequest = this.getSearchCvsRequest();
            var locationsCount = _.size(location);

            if (_.isEmpty(searchCvsRequest)) {
                return $.mage.__('Please wait.');
            } else if (_.isEmpty(location) && _.size(searchCvsRequest) > 0) {
                return $.mage.__('No cvs location found.');
            } else {
                return $.mage.__('There were %1 results for your search.').replace('%1', locationsCount);
            }
        },

        getSearchCvsRequest: function () {
            if (_.size(sectionData()['search-cvs-request']) > 0) {
                return sectionData()['search-cvs-request'];
            }
            return false;
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
            return customerData.reload([cacheKey]);
        },

        clear: function() {
            customerData.set(cacheKey, {
                'cvs-location': [],
                'search-request': {}
            });
        }
    };

});
