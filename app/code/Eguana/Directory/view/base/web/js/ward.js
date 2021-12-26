define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/form/element/region'
], function (_, registry, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            imports: {
                update: '${ $.parentName }.city_id:value'
            },
            exports: {
                wardValue: '${ $.parentName }.ward:value',
            },
            visible: true,
            wardValue: null,
            tracks: {
                wardValue: true,
                value: true,
            },
        },

        initialize: function () {
            this._super();
            this.resetFields();
            return this;
        },

        onUpdate: function (value) {
            this._super();

            if ( this.indexedOptions[value] && this.indexedOptions[value] !== '0000') {
                this.wardValue = this.indexedOptions[value]['title'];
            }
        },

        hideOrigWard: function () {
            registry.get((this.parentName + '.' + 'ward'), function (ward) {
                ward.validation['required-entry'] = false;
                ward.visible(false);
            });
            registry.get((this.parentName + '.' + 'ward_id'), function (wardId) {
                wardId.validation['required-entry'] = false;
                wardId.visible(false);
            });
        },

        resetFields: function () {
            this.hideOrigWard();
        }
    });
});
