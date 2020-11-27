define([
    'jquery',
    'mage/url'
], function (
    $,
    url
) {
    'use strict';

    function isMobile() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        // return true;
    }

    function openWindow(openLineWindowUrl, target) {
        return window.open(openLineWindowUrl, target, "width=500,height=600");
    }


    return function (deferred, cvs) {
        let  openLineWindowUrl = url.build('eguana_gwlogistics/selectcvs/index') + '/cvs_type/' + cvs,
            lineWindow, target;

        target = isMobile() ? '_self' : 'popupWind';

        if (isMobile()) {
            openWindow(openLineWindowUrl, target);
            return;
        }

        lineWindow = openWindow(openLineWindowUrl, target);
        deferred = deferred || $.Deferred();

        let i = 0;
        let timer = setInterval(function () {
            i++;
            if (lineWindow.closed) {
                clearInterval(timer);

                return deferred.resolve();
            }
        },1000);

    }

});
