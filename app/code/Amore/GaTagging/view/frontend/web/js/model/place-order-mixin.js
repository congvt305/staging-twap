define([
    'jquery',
    'mage/utils/wrapper',
], function ($, wrapper) {
    'use strict';

    function notify(eventName) {
        console.log('window.dataLayer.push notifyOrderButn');
        window.dataLayer.push({'event': eventName});
    }
    return function (placeOrderAction) {

        /** Override default place order action and add agreement_ids to request */
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            console.log('notifyOrderButn');
            notify('orderbtn');

            return originalAction(paymentData, messageContainer);
        });
    };
});
