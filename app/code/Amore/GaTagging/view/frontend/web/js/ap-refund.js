define([], function () {
    'use strict';

    /**
     * Push event
     *
     * @param eventName
     * @param orderId
     */
    function notify(eventName, orderId) {
        var refundedItems = getRefundedItems();

        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'event': eventName,
            'order_id': orderId
        });
    }

    /**
     * Get refunded items
     *
     * @returns array
     */
    function getRefundedItems() {
        // TODO: Filter order items when partial refund is done
        return window.AP_REFUND_PRDS || [];
    }

    return function (config, element) {
        notify(config.eventName, config.orderId);
    };
});
