define([
    'jquery',
    'mage/url',
    'mage/storage',
    'mage/translate'
], function ($,url,storage, $t) {
    'use strict';

    return function (bankInfoData) {
        var refundUrl, serviceUrl, payload;
        refundUrl = 'rest/V1/eguana_customerrefund/mine/refund/offline';
        payload = {'bankInfoData': bankInfoData};

        serviceUrl = url.build(refundUrl);

        $('body').trigger('processStart');

        return storage.post(
            serviceUrl,
            JSON.stringify(payload),
            false
        ).done(
            function (response) {
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
