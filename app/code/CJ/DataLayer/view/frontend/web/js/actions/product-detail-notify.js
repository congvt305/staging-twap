define([
], function () {
    'use strict';


    function notify(eventName) {
        window.dataLayer.push({
            'event': eventName,
            'ecommerce': {
                'currency': CJ_PRD_CURRENCY,
                'details': {
                    'products': CJ_PRD_DATA
                }
            }
        });
    }

    return function (config) {
        if (window.dataLayer) {
            notify(config.eventName);
        }
    };
});
