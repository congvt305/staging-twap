define([
    'jquery',
    'ko',
    'underscore',
    'uiComponent',
    'Magento_Checkout/js/action/set-shipping-information',
    'CJ_SFLocker/js/model/pickup-locations-service'
], function(
    $,
    ko,
    _,
    Component,
    setShippingInformationAction,
    pickupLocationsService
) {
    'use strict';
    var apStores = window.checkoutConfig.ap_stores;
    var apAreas = window.checkoutConfig.ap_areas;
    return Component.extend({
        defaults: {
            template: 'CJ_SFLocker/store-selector',
            selectedLocation: pickupLocationsService.selectedLocation
        },
        apStores: ko.observableArray([]),
        apCities: ko.observableArray(window.checkoutConfig.ap_cities),
        apAreas: ko.observableArray([{ label: '區域', value: '' }]),
        selectedSourceCode: ko.observable(''),
        /**
         * Init component
         *
         * @return {exports}
         */
        initialize: function() {
            this._super();
            var stores = [];
            var dValue = { label: '店舖', value: '' };
            stores.push(dValue);
            _.each(apStores, function(items, city) {
                _.each(items, function(store) {
                    stores.push(store);
                })
            });
            this.apStores(stores);
            return this;
        },

        selectCity: function() {
            var selectedValue = $('[name="pickup_ap_city"]').find(":selected").val();
            var areas = [];
            var dValue = { label: '地區', value: '' };
            areas.push(dValue);
            if (!selectedValue) {
                _.each(apAreas, function(items, city) {
                    _.each(items, function(store) {
                        areas.push(store);
                    })
                });
                this.apAreas(areas);
                return;
            }
            _.each(apAreas, function(items, city) {
                if (city === selectedValue) {
                    _.each(items, function(store) {
                        areas.push(store);
                    })
                }
            });
            this.apAreas(areas);
        },

        selectArea: function() {
            var selectedValue = $('[name="pickup_ap_area"]').find(":selected").val();
            var stores = [];
            var dValue = { label: '店舖', value: '' };
            stores.push(dValue);
            if (!selectedValue) {
                _.each(apStores, function(items, area) {
                    _.each(items, function(store) {
                        stores.push(store);
                    })
                });
                this.apStores(stores);
                return;
            }
            _.each(apStores, function(items, area) {
                if (area === selectedValue) {
                    _.each(items, function(store) {
                        stores.push(store);
                    })
                }
            });
            this.apStores(stores);
        },

        selectApStore: function() {
            var selectedValue = $('[name="pickup_ap_store"]').find(":selected").val();
            if (!selectedValue) {
                return;
            }
            pickupLocationsService
                .getLocation(selectedValue)
                .then(function(location) {
                    pickupLocationsService.selectForShipping(location);
                    setShippingInformationAction();
                });
        },

    });
});