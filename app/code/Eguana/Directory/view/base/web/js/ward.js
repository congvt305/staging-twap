define([
    'jquery',
    'mage/url',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/form/element/region'
], function ($, urlBuilder, _, registry, Select) {
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
                this.wardValue = this.indexedOptions[value]['labeltitle'];
            }
        },

        update:function(value) {
            this.resetFields();
            var city = registry.get(this.parentName + '.' + 'city_id'),
                options = city.indexedOptions,
                option;
            if (value == '0000') {
                return;
            }

            this.loadWards(value);//value: city_id

        },

        hideOrigWard: function () {
            registry.get((this.parentName + '.' + 'ward'), function (ward) {
                ward.validation['required-entry'] = false;
                ward.visible(false);
            });
        },

        loadWards: function (cityId) {
            var self = this;
            $.ajax({
                url: urlBuilder.build('custom_directory/account/getWard'),
                method: 'GET',
                data: {city_id: cityId},
                dataType: 'json',
                showLoader: true,
                success: function (response) {
                    if (response) {
                        // Populate the city select field with the returned ward IDs
                        self.setOptions(response);
                    }
                }
            });
        },

        resetFields: function () {
            this.hideOrigWard();
        }
    });
});
