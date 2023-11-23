define([

    'jquery',
    'Magento_Customer/js/customer-data',
    'uiComponent',
    'ko'
], function ($, customerData, Component, ko) {
    'use strict';
    return Component.extend({

        wishlistitem: ko.observable(),


        initialize: function () {
            this._super();
            this.wishlistitem = customerData.get('wishlist');
        },


    });
});
