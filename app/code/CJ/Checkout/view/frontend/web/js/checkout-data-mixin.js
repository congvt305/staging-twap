/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Customer/js/customer-data',
], function (
    $,
    wrapper,
    storage
) {
    'use strict';
    var cacheKey = 'checkout-data',

        /**
         * @param {Object} data
         */
        saveData = function (data) {
            storage.set(cacheKey, data);
        },

        /**
         * @return {*}
         */
        initData = function () {
            return {
                'selectedShippingAddress': null, //Selected shipping address pulled from persistence storage
                'shippingAddressFromData': null, //Shipping address pulled from persistence storage
                'newCustomerShippingAddress': null, //Shipping address pulled from persistence storage for customer
                'selectedShippingRate': null, //Shipping rate pulled from persistence storage
                'selectedPaymentMethod': null, //Payment method pulled from persistence storage
                'selectedBillingAddress': null, //Selected billing address pulled from persistence storage
                'billingAddressFromData': null, //Billing address pulled from persistence storage
                'newCustomerBillingAddress': null, //Billing address pulled from persistence storage for new customer
                'storeName': null,
                'cvsAddress': null
            };
        },

        /**
         * @return {*}
         */
        getData = function () {
            var data = storage.get(cacheKey)();

            if ($.isEmptyObject(data)) {
                data = $.initNamespaceStorage('mage-cache-storage').localStorage.get(cacheKey);

                if ($.isEmptyObject(data)) {
                    data = initData();
                    saveData(data);
                }
            }

            return data;
        };

    return function (checkoutData) {
        checkoutData.setCVSAddress = wrapper.wrapSuper(
            checkoutData.setCVSAddress,
            function (data) {
                var obj = getData();

                obj.cvsAddress = data;
                saveData(obj);
            }
        );
        checkoutData.getCVSAddress = wrapper.wrapSuper(
            checkoutData.getCVSAddress,
            function () {
                return getData().cvsAddress;
            }
        );
        checkoutData.setStoreName = wrapper.wrapSuper(
            checkoutData.setStoreName,
            function (data) {
                var obj = getData();

                obj.storeName = data;
                saveData(obj);
            }
        );
        checkoutData.getStoreName = wrapper.wrapSuper(
            checkoutData.getStoreName,
            function () {
                return getData().storeName;
            }
        );

        return checkoutData;
    };
});
