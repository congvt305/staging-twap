// 2.4 트랜잭션 주문결제 결제하기 버튼
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
