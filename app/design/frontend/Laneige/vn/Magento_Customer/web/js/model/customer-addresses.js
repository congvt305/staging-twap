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
                    var maxId = 0;
                    $.each(customerData.addresses, function (key, item) {
                        if (item.default_shipping) {
                            items.push(new Address(item));
                        } else {
                            if (maxId <= key) {
                                maxId = key;
                            }
                        }
                    });
                    if (addressLength > 1) {
                        items.push(new Address(customerData.addresses[maxId]));
                    }
                }
            }

            return items;
        }
    };
});
