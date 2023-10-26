define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/single-checkbox',
], function ($,uiRegistry, checkbox) {
    'use strict';

    return checkbox.extend({
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
            if (value == '1'){
                $('.floating-button-preview').css('display','table-footer-group');
            } else {
                $('.floating-button-preview').css('display','none');
            }
            return this._super();
        },
    });
});