define([
    'jquery',
    'Magento_Ui/js/form/element/abstract'
], function ($, Element) {
    'use strict';

    return Element.extend({
        initialize: function () {
            this._super();
        },

        onKeypress: function (el, e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
                // Allow: Ctrl+A, Command+A
                (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                // let it happen, don't do anything
                return true;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                return false;
            }

            var number = ['1','2','3','4','5','6','7','8','9','0'];
            if($.inArray(e.key,number) === -1){
                return false;
            }

            return true;
        }
    });
});
