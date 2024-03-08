define([
    'jquery'
], function ($) {
    'use strict';

    function notify(eventName) {
        switch (eventName) {
            case 'login_complete':
                window.dataLayer.push({
                    'event': eventName,
                    'event_category':'login',
                    'event_action':'login - complete',
                    'event_label':'General Login'
                });
                break;
            default:
                var url = window.location.href,
                    eventLabel = 'General Login';
                if (url.includes('type/line')) {
                    eventLabel = 'line';
                }else if (url.includes('type/facebook')) {
                    eventLabel = 'facebook';
                }else if (url.includes('type/google')) {
                    eventLabel = 'google';
                }
                window.dataLayer.push({
                    'event': eventName,
                    'event_category':'sign_up',
                    'event_action':'sign_up - complete',
                    'event_label': eventLabel
                });
                break;
        }
    }

    return function (config, element) {
        var form = $(element), isValidate = false;
        form.on('submit', function (event) {
            if (form.validation() && form.validation('isValid')) {
                notify(config.eventName);
            }
            return true;
        });
    };
});
