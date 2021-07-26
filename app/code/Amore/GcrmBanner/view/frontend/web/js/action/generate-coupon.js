define([
    'jquery',
    'mage/url',
    'mage/storage',
    'mage/translate'
], function ($, url, storage, $t) {
    'use strict';

    return function (deferred, salesruleId) {
        var serviceUrl, generateUrl, payload;

        deferred = deferred || $.Deferred();

        generateUrl = 'rest/V1/gcrmBanner/mine/generate-coupon';
        serviceUrl = url.build(generateUrl);
        payload = {'salesruleId': salesruleId};

        $('body').trigger('processStart');

        return storage.post(
            serviceUrl,
            JSON.stringify(payload),
            false
        ).done(function (result) {
            deferred.resolve(result);
        }).fail(function (response) {
            deferred.reject(response);
        }).always(function () {
            $('body').trigger('processStop');
        });
    }
});
