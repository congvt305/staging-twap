define(['jquery', 'uiComponent'], function ($, Component) {
    'use strict';

    return Component.extend({
        defaults: {},
        initObservable: function () {
            this._super();
            // this.observe('openingHoursObject value');
            // $('browser.tabs').on('revmove', $.proxy(this.onWindowChange, this));

            window.addEventListener("beforeunload", function (e) {
                var confirmationMessage = "\o/";

                (e || window.event).returnValue = confirmationMessage; //Gecko + IE
                return confirmationMessage;                            //Webkit, Safari, Chrome
            });
            return this;
        },
        onWindowChange: function () {
            console.log('browser chaned.')
        }
    });
});
