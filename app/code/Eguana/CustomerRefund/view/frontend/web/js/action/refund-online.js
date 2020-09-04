define([
    'jquery',
    'mage/url',
    'mage/storage',
    'mage/translate'
], function ($,url,storage, $t) {
    'use strict';

    return function (orderId) {
        var refundUrl, serviceUrl, payload;
        refundUrl = 'rest/V1/eguana_customerrefund/mine/refund/online';
        payload = {'orderId': orderId};

        serviceUrl = url.build(refundUrl);
        $('body').trigger('processStart');

        return storage.post(
            serviceUrl,
            JSON.stringify(payload)
        ).done(
            function (response) {
                window.location.href = url.build('sales/order/view/order_id/') + orderId
                    + '?refund=' + 'true';
                // $('body').trigger('processStop');

            }
        );
    };
});
