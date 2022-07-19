/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/place-order',
    'jquery'
], function (quote, urlBuilder, customer, placeOrderService, $) {
    'use strict';

    return function (paymentData, messageContainer) {
        var serviceUrl, payload;
        var is_apply_identifier = "input[name='is_apply']:checked";
        var company_name_identifier = "input[name='company_name']";
        var tax_code_identifier = "input[name='tax_code']";
        var state_identifier = "select[name='state']";
        var city_identifier = "select[name='city']";
        var road_name_identifier = "input[name='road_name']";
        var email_identifier = "input[name='email']";
        var ward_identifier = "select[name='ward']";
        var delivery_message_identifier = '[name="delivery_message"]';

        var is_apply_value = $(is_apply_identifier).val();
        var company_name_value = $(company_name_identifier).val();
        var tax_code_value = $(tax_code_identifier).val();
        var state_value = $(state_identifier).val();
        var city_value = $(city_identifier).val();
        var road_name_value = $(road_name_identifier).val();
        var email_value = $(email_identifier).val();
        var ward_value = $(ward_identifier).val();
        var delivery_message_value = $(delivery_message_identifier).val();

        payload = {
            cartId: quote.getQuoteId(),
            billingAddress: quote.billingAddress(),
            paymentMethod: paymentData
        };
        if (payload.billingAddress['extension_attributes'] === undefined) {
            payload.billingAddress['extension_attributes'] = {};
        }
        payload.billingAddress['extension_attributes']['is_apply'] = is_apply_value;
        payload.billingAddress['extension_attributes']['company_name'] = company_name_value;
        payload.billingAddress['extension_attributes']['tax_code'] = tax_code_value;
        payload.billingAddress['extension_attributes']['state'] = state_value;
        payload.billingAddress['extension_attributes']['city'] = city_value;
        payload.billingAddress['extension_attributes']['road_name'] = road_name_value;
        payload.billingAddress['extension_attributes']['email'] = email_value;
        payload.billingAddress['extension_attributes']['ward'] = ward_value;
        payload.billingAddress['extension_attributes']['delivery_message'] = delivery_message_value;

        if (customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/carts/mine/payment-information', {});
        } else {
            serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/payment-information', {
                quoteId: quote.getQuoteId()
            });
            payload.email = quote.guestEmail;
        }

        return placeOrderService(serviceUrl, payload, messageContainer);
    };
});
