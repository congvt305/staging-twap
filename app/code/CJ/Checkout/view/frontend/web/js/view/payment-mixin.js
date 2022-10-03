define([
    'ko',
    'jquery',
    'Eguana_LinePay/js/view/payment/method-renderer/linepaypayment',
    'Ecpay_Ecpaypayment/js/view/payment/method-renderer/ecpaypayment',
    'Magento_OfflinePayments/js/view/payment/method-renderer/checkmo-method',
    'Magento_Checkout/js/checkout-data'
], function (ko, $, linepayPayment, ecpayPayment, checkmoMethod, checkoutData) {
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
