define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
], function ($,uiRegistry, select) {
    'use strict';

    return select.extend({
        initialize: function () {
            this._super();
            this.onUpdate(this.value())
            return this;
        },
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            $.ajax({
                type: 'POST',
                url: this.urlTemplateType,
                data: {popup_type: value},
                showLoader: false,
                dataType: 'json',
                success: function (data) {
                    var template_id = uiRegistry.get('index = popup_template_id').value();
                    uiRegistry.get('index = popup_template_id').options(data);
                    uiRegistry.get('index = popup_template_id').value(template_id);
                }
            }).fail(function(error) {
            });
            return this._super();
        },
    });
});