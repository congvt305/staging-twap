define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Eguana_CustomerRefund/js/action/refund-online',
    'mage/translate'
], function ($, confirmation, refundAction, $t) {
    'use strict'
    return function (config, element) {
        $(element).click(function (event) {
            confirmation({
                title: $t('Refund Order Confirmation'),
                content: $t('Are you sure to refund this order?.'),
                actions: {
                    cancel: function () {
                        return false;
                    },
                    confirm: function () {
                        refundAction(config.orderId);
                    }
                }
            });
        });
    };
});
