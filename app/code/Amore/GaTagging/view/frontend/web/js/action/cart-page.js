define([], function () {
    'use strict';

    function notify(eventName) {
        console.log('window.dataLayer.push');
        window.dataLayer.push({'event': eventName});
    }
    return function (config, element) {

        console.log(config.eventName);
        if (window.dataLayer) {
            notify(config.eventName);
        }
    };
});
