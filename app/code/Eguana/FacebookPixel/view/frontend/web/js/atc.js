/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 9/2/21
 * Time: 6:00 PM
 */

define([
    'ko',
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (ko, Component, customerData) {
    'use strict';
    return Component.extend({
        initialize: function () {
            var self = this;
            self._super();
            customerData.get('bss-fbpixel-atc').subscribe(function (loadedData) {
                if (loadedData && "undefined" !== typeof loadedData.events) {
                    for (var eventCounter = 0; eventCounter < loadedData.events.length; eventCounter++) {
                        var eventData = loadedData.events[eventCounter];
                        if ("undefined" !== typeof eventData.eventAdditional && eventData.eventAdditional) {
                            eventData.eventAdditional.unique = Date.now();
                            fbq('track', eventData.eventName, eventData.eventAdditional || {});
                        }
                    }
                    customerData.set('bss-fbpixel-atc', {});
                }
            });
        }
    });
});
