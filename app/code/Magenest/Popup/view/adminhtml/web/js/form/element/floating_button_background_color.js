define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/color-picker',
], function ($,uiRegistry, ColorPicker) {
    'use strict';

    return ColorPicker.extend({
        initialize: function () {
            this._super();
            this.onUpdate(this.value());
            return this;
        },
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            $('.magenest-popup-action').css('background-color',value);
            $('.magenest-popup-action').on('mouseout', function() {
                $(this).css('background-color', value);
            });
            return this._super();
        },
    });
});