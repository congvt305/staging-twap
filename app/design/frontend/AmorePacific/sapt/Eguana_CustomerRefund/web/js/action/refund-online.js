define([
    'jquery',
    'mage/url',
    'mage/storage',
    'mage/translate'
], function ($,url,storage, $t) {
    'use strict';

    return function (orderId) {
        var refundUrl, serviceUrl, payload, orderViewUrl;
        refundUrl = 'rest/V1/eguana_customerrefund/mine/refund/online';
        orderViewUrl = url.build('sales/order/view/order_id/') + orderId
            + '?refund=';
        console.log('orderViewUrl : ', orderViewUrl);
        payload = {'orderId': orderId};

        serviceUrl = url.build(refundUrl);
        $('body').trigger('processStart');

        return storage.post(
            serviceUrl,
            JSON.stringify(payload)
        ).done(
            function (response) {
                if (window.dataLayer) {
                    var AP_ECOMM_CURRENCY = window.AP_ECOMM_CURRENCY || '';

                    window.dataLayer.push({
                        'event': 'refund',
                        'order_id': orderId
                    });
                }
               window.location.href = orderViewUrl + response;
            }
        );
    };
});
