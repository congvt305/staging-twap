define([
    'jquery',
    'mage/url',
    'mage/storage',
    'mage/translate'
], function ($,url,storage, $t) {
    'use strict';

    return function (orderId) {
        console.log(orderId);
        console.log(typeof orderId);

        var refundUrl, serviceUrl, payload;
        refundUrl = 'rest/V1/eguana_customerrefund/mine/refund/online';
            console.log(refundUrl);
        payload = {'orderId': orderId};

        serviceUrl = url.build(refundUrl);

        $('body').trigger('processStart');

        return storage.post(
            serviceUrl,
            JSON.stringify(payload)
        ).done(
            function (response) {
                console.log(response);
                $('body').trigger('processStop');
                location.reload();
            }
        ).fail(
            function () {
                $('body').trigger('processStop');
            }
        );
    };
});
