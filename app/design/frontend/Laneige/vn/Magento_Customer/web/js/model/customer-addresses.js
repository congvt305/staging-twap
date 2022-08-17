/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'ko',
    './customer/address'
], function ($, ko, Address) {
    'use strict';

    var isLoggedIn = ko.observable(window.isCustomerLoggedIn);

    return {
        /**
         * @return {Array}
         */
        getAddressItems: function () {
            var items = [],
                customerData = window.customerData,
                i = 1,
                addressLength = 0;

            if (isLoggedIn()) {
                if (Object.keys(customerData).length) {
                    addressLength = Object.keys(customerData.addresses).length
                    $.each(customerData.addresses, function (key, item) {
                        if (item.default_shipping || i === addressLength) {
                            items.push(new Address(item));
                        }
                        i++;
                    });
                }
            }

            return items;
        }
    };
});
