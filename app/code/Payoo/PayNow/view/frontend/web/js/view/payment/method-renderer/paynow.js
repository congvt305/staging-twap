/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/redirect-on-success',
        'mage/url'
    ],
    function (Component, redirectOnSuccessAction, url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Payoo_PayNow/payment/form',
            },

            getCode: function() {
                return 'paynow';
            },

            afterPlaceOrder: function () {
                redirectOnSuccessAction.redirectUrl = url.build('payoo/payment/');
                this.redirectAfterPlaceOrder = true;
          },
        });
    }
);