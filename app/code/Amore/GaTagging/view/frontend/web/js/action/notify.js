define([], function () {
    'use strict';

    function notify(eventName) {
        console.log('dataLayer push ', eventName);
        window.dataLayer.push({'event': eventName});
    }

    return function (config, element) {
        if (window.dataLayer) {
            notify(config.eventName);
        }
    };
});
