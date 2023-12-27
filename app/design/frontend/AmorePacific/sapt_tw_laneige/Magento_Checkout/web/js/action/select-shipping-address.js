/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'Magento_Checkout/js/model/quote'
], function ($, quote) {
    'use strict';

    return function (shippingAddress) {
        if (shippingAddress.telephone) {
            $("#shipping-new-address-form input[name='telephone']").val(shippingAddress.telephone);
        }
        if (shippingAddress.firstname) {
            $("#shipping-new-address-form input[name='firstname']").val(shippingAddress.firstname);
        }
        if (shippingAddress.lastname) {
            $("#shipping-new-address-form input[name='lastname']").val(shippingAddress.lastname);
        }
        quote.shippingAddress(shippingAddress);
    };
});
