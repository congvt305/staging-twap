define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract',
], function ($,uiRegistry, Input) {
    'use strict';

    return Input.extend({
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
            $('#floating-button-content').text(value);
            return this._super();
        },
    });
});