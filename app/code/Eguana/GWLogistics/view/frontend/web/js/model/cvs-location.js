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
            // console.log(sectionData());
            return sectionData()['cvs-location'] || [];
            // return sectionData()['cvs-location'] || [];
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
            // var cvsLocation = this.getCvsLocation();
            // var searchRequest = this.getSearchCvsRequest();
            // customerData.set(cacheKey, {
            //     'cvs-location': cvsLocation,
            //      'search-request': searchRequest
            // });

        },

        selectPickupLocation: function (pickupLocationId) {
            // console.log(pickupLocationId);
            var pickupLocations = this.getPickupLocations();
            // var searchRequest = this.getSearchRequest(); //cvs store name, 711, familymart... later??

            customerData.set(cacheKey, {
                'cvs-location': pickupLocationId,
                // 'search-request': searchRequest
            });
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
