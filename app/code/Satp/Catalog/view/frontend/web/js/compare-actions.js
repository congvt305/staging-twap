define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'uiComponent',
    'ko'
], function ($, customerData, Component, ko) {
    'use strict';
    return Component.extend({
        compareItem: ko.observable(),
        initialize: function () {
            this._super();
            this.compareItem = customerData.get('compare-products');
        },

        isAddedToCompare: function(productId) {
            var items = this.compareItem().items,
                isCompared = false;
            $.each(items, function (index, item) {
                if (item.id == productId) {
                    isCompared = true;
                }
            });
            return isCompared;
        }
    });
});
