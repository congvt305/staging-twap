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
        'mage/url',
        'mage/translate',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/select-payment-method',
        'ko'
    ],
    function (Component, redirectOnSuccessAction, url, $t, $, quote, checkoutData, selectPaymentMethodAction, ko) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Payoo_PayNow/payment/form',
            },
            currentMethod: ko.observable(null),

            getCode: function() {
                return 'paynow';
            },

            getTitle: function() {
                return $t('Pay with bank');
            },

            getCodePayooWallet: function() {
                return 'paynow-wallet';
            },

            getTitlePayooWallet:function() {
                return $t('Pay with wallet');
            },

            getCodePayooVisa: function() {
                return 'paynow-visa';
            },
            getTitlePayooVisa:function() {
                return $t('Pay with visa');
            },

            isChecked: ko.computed(function () {
                if (quote.paymentMethod()) {
                    return quote.paymentMethod().method;
                } else if (checkoutData.getSelectedPaymentMethod()) {
                    return checkoutData.getSelectedPaymentMethod();
                } else {
                    return null;
                }

            }),
            /**
             * Get payment method data
             */
            getData: function () {
                return {
                    'method': $("input:radio.payoo-payment:checked").val(),
                    'po_number': null,
                    'additional_data': null,
                    'extension_attributes': {'current_method': this.currentMethod}
                };
            },
            /**
             * @return {Boolean}
             */
            selectPaymentMethod: function () {
                var method = this.item.method;
                if (this.item.method === 'paynow') {
                    method = $("input:radio.payoo-payment:checked").val();
                }
                this.currentMethod = method;
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(method);

                return true;
            },

            beforePlaceOrder : function(data, event) {
                selectPaymentMethodAction(
                    {
                        'method': 'paynow',
                        'po_number': null,
                        'additional_data': null
                    }
                );
                checkoutData.setSelectedPaymentMethod('paynow');
                this.placeOrder(data, event);
            },

            afterPlaceOrder: function () {
                redirectOnSuccessAction.redirectUrl = url.build('payoo/payment/');
                this.redirectAfterPlaceOrder = true;
            },
        });
    }
);
