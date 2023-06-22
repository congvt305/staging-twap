define([
    'ko',
    'jquery',
    'Eguana_LinePay/js/view/payment/method-renderer/linepaypayment',
    'Ecpay_Ecpaypayment/js/view/payment/method-renderer/ecpaypayment',
    'Magento_OfflinePayments/js/view/payment/method-renderer/checkmo-method',
    'Magento_Checkout/js/checkout-data',
    'mage/url'
], function (ko, $, linepayPayment, ecpayPayment, checkmoMethod, checkoutData, url) {
    'use strict';
    return function(Component) {
        return Component.extend({
            goBack: function () {
                window.location.href = window.checkoutConfig.cartUrl;
            },

            /**
             * Place Order
             */
            checkoutPlaceOrder: function() {
                $.ajax({
                    url: url.build('cj_amrewards/ajax/validaterewardbeforeplace'),
                    type: 'post',
                    dataType: 'json',
                    context: this,
                    cache: false,
                    data: {},
                    beforeSend: function () {
                        $('body').loader('show');
                    },
                    success: function (response) {
                        if (response['success']) {
                            if ($('.payment-method._active').length) {
                                var paymentMethod = $('.payment-method._active').find("[name='payment[method]']").val();
                                if (paymentMethod === "ecpay_ecpaypayment") {
                                    ecpayPayment().checkoutToEcpay();
                                } else if (paymentMethod === "linepay_payment") {
                                    linepayPayment().checkoutToLinePay();
                                } else {
                                    checkmoMethod().placeOrder();
                                }
                            }
                        } else {
                            window.location.reload();
                        }
                    },
                    /** @inheritdoc */
                    complete: function () {
                        $('body').trigger('processStop');
                    }
                });
            },

            /**
             * Is disable button for place order
             *
             * @returns {boolean}
             */
            isDisableButtonCheckoutPlaceOrder: function() {
                if (checkoutData.getSelectedPaymentMethod()) {
                    return false;
                } else {
                    return true;
                }
            }
        });
    }
});
