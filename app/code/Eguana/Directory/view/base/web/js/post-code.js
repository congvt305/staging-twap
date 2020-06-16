define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract'
], function ($, _, registry, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            imports: {
                cityUpdate: '${ $.parentName }.city_id:value'
            },
            tracks: {
                value: true,
            }
        },

        initialize: function () {
            this._super();
            registry.get(this.parentName + '.postcode', function (input) {
                console.log('post parent code : ',input);
                input.disable(true);
            });
            return this;
        },

        cityUpdate: function (value) {
            var cityOptions = registry.get(this.provider)['dictionaries']['city_id'];
            if (cityOptions[value]) {
                var newPostcode = cityOptions[value]['code'];
                this.value(newPostcode);
            }
        },

    });
});
