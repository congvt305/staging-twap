define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data'
], function ($, authenticationPopup, customerData) {
    'use strict';

    return function (config, element) {
        $(element).click(function (event) {
            let cart = customerData.get('cart'),
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
            let requestUrl = config.checkoutUrl;
            let searchArr = '';
            if (requestUrl.indexOf('amp;') > -1)
            {
                searchArr = requestUrl.split('&amp;');
                requestUrl = searchArr.join('&');
                config.checkoutUrl = requestUrl;
            }

            location.href = config.checkoutUrl;
        });

    };
});
