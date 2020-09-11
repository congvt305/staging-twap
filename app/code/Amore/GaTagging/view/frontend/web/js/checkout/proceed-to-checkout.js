/**
 * 2.4 트랜잭션 구매하기 버튼 (장바구니)
 */

define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data'
], function ($, authenticationPopup, customerData) {
    'use strict';

    return function (config, element) {

        $(element).click(function (event) {
            var cart = customerData.get('cart'),
                customer = customerData.get('customer');

            event.preventDefault();

            if (!customer().firstname && cart().isGuestCheckoutAllowed === false) {
                location.href = window.checkout.customerLoginUrl;
                return false;
            }
            /**
             * added to core code
             */
            window.dataLayer.push({'event': 'purchasecartbtn'});

            $(element).attr('disabled', true);
            location.href = config.checkoutUrl;
        });

    };
});
