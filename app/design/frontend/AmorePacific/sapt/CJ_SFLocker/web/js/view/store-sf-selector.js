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
    var sfLockers = window.checkoutConfig.sf_lockers;
    var sfAreas = window.checkoutConfig.sf_areas;
    return Component.extend({
        defaults: {
            template: 'CJ_SFLocker/store-sf-selector',
            selectedLocation: pickupLocationsService.selectedLocation
        },
        sfLockers: ko.observableArray([]),
        sfCities: ko.observableArray(window.checkoutConfig.sf_cities),
        sfAreas: ko.observableArray([{ label: '區域', value: '' }]),
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
            _.each(sfLockers, function(items, city) {
                _.each(items, function(store) {
                    stores.push(store);
                })
            });
            this.sfLockers(stores);
            return this;
        },

        selectCity: function() {
            var selectedValue = $('[name="pickup_sf_city"]').find(":selected").val();
            var areas = [];
            var dValue = { label: '地區', value: '' };
            areas.push(dValue);
            if (!selectedValue) {
                _.each(sfAreas, function(items, city) {
                    _.each(items, function(store) {
                        areas.push(store);
                    })
                });
                this.sfAreas(areas);
                return;
            }
            _.each(sfAreas, function(items, city) {
                if (city === selectedValue) {
                    _.each(items, function(store) {
                        areas.push(store);
                    })
                }
            });
            this.sfAreas(areas);
        },

        filterStore: function() {
            var selectedArea = $('[name="pickup_sf_area"]').find(":selected").val() || '';
            var selectedType = $('[name="pickup_sf_type"]:checked').val() || '';
            var stores = [];
            var dValue = { label: '店舖', value: '' };
            stores.push(dValue);
            if (!selectedArea && !selectedType) {
                _.each(sfLockers, function(items, area) {
                    _.each(items, function(store) {
                        stores.push(store);
                    })
                });
                this.sfLockers(stores);
            } else {
                _.each(sfLockers, function(items, area) {
                    if (selectedArea === '' || area === selectedArea) {
                        _.each(items, function(store) {
                            if (selectedType === '' || store.type === selectedType) {
                                stores.push(store);
                            }
                        })
                    }
                });
                this.sfLockers(stores);
            }
            return true
        },

        selectSfStore: function() {
            var selectedValue = $('[name="pickup_sf"]').find(":selected").val();
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