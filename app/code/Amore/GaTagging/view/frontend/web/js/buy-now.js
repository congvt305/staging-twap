define([
    'jquery'
], function ($) {
    'use strict';

    function notify(eventName) {
        if (window.dataLayer) {
            //console.log('dataLayer.push ', eventName);
            window.dataLayer.push({'event': eventName});
        }
    }

    return function (config) {
        //console.log(config);
        $(config.buttonSelect)
            .on(
                'click',
                function (e) {
                    notify('purchaseprdbtn');
                }
            );
    };
});