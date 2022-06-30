define([
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ], function (Component, rendererList) {
        'use strict';
        rendererList.push({
            type: 'atome_payment_gateway',
            component: 'Atome_MagentoPayment/js/view/payment/method-renderer/atome-payment-gateway'
        });
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
