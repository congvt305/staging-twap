define([
    'jquery'
], function ($) {
    'use strict';

    function notify(eventName) {
        window.dataLayer.push({'event': eventName});
    }

    return function (config, element) {
        var registerForm = $(element);
        registerForm.on('submit', function () {
            if (registerForm.validation() && registerForm.validation('isValid')) {
                notify(config.eventName);
            }
            return true;
        });

    };
});
