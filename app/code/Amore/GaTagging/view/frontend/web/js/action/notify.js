define([], function () {
    'use strict';

    function notify(eventName) {
        if (eventName === 'product') {
            window.dataLayer.push({
                'event': 'productViewContent'  ,
                'ecommerce' : {
                    'currency': AP_PRD_CURRENCY,
                    'products' : [
                        {
                            'name': AP_PRD_NAME,
                            'id': AP_PRD_CODE,
                            'price': AP_PRD_PRICE,
                            'category': AP_PRD_CATEGORY,
                            'quantity': AP_PRD_QTY
                        }
                    ]
                }
            });
        } else {
            window.dataLayer.push({'event': eventName});
        }

    }

    return function (config, element) {
        if (window.dataLayer) {
            notify(config.eventName);
        }
    };
});
