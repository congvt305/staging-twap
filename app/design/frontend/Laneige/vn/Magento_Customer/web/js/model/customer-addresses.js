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
                customerData = window.customerData;

            if (isLoggedIn()) {
                if (Object.keys(customerData).length) {
                    $.each(customerData.addresses, function (key, item) {
                        items.push(new Address(item));
                        //customize here
                        if (items.length === 2) {
                            //break the loop to limit 2 line shipping
                            return false;
                        }
                    });
                }
            }

            return items;
        }
    };
});
