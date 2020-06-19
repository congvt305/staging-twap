define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Checkout/js/model/default-post-code-resolver',
], function (_, registry, Select, defaultPostCodeResolver) {
    'use strict';

    return Select.extend({
        defaults: {
            visible: true,
            cityValue: null,
            postcodeValue: null,
            is_city_required: true,
            currentRegion: null,
            skipValidation: false, // todo: should be false, but when change to false, 2 city is shown.
            imports: {
                update: '${ $.parentName }.region_id:value'
            },
            exports: { //without this, post code is not updated I cannot proceed to next step, because original city is required field.
                cityValue: '${ $.parentName }.city:value',
                postcodeValue: '${ $.parentName }.postcode:value'
            },
            tracks: {
                cityValue: true,
                postcodeValue: true,
                value: true,
            }
        },

        initialize: function () {
            this._super();
            this.hideOrigCity();
            this.disablePostcode();
            this.initAttr();
            return this;
        },

        onUpdate: function (value) {
            this._super();
            if ( this.indexedOptions[value]) {
                this.cityValue = this.indexedOptions[value]['title'];
                this.postcodeValue = this.indexedOptions[value]['code'];
            }
        },
        /**
         * todo: when clear city and postcode,
         * basically here is to set required attr depend upon configuration value
         * @param {String} value
         */
        update: function (value) {
            var region = registry.get(this.parentName + '.' + 'region_id'),
                options = region.indexedOptions,
                isCityRequired,
                option;
            if (!value) {
                return;
            }
            this.currentRegion = value;
            option = options[value]; //value: region_id

            if (typeof option === 'undefined') {
                return;
            }

            if (this.skipValidation) { //here is excuted and working, but need to be fixed.
                this.validation['required-entry'] = false;
                this.required(false);
            } else {
                if (option && !option['is_city_required']) { //executed here todo: is_city_required, city_display_all need to be configuration.
                    this.error(false);
                    this.validation = _.omit(this.validation, 'required-entry');
                    registry.get(this.customName, function (input) { //customName == custom Entry?? todo: required entiry shoud be fixed
                        input.validation['required-entry'] = false;
                        input.required(false);
                    });
                } else {
                    this.validation['required-entry'] = true;
                }

                if (option && !this.options().length) {
                    registry.get(this.customName, function (input) {
                        isCityRequired = !!option['is_city_required'];
                        input.validation['required-entry'] = isCityRequired;
                        input.validation['validate-not-number-first'] = true;
                        input.required(isCityRequired);
                    });
                }
                this.required(!!option['is_city_required']);
            }
        },

        hideOrigCity: function () {
            registry.get((this.parentName + '.' + 'city'), function (city) { //customName == custom Entry??
                city.validation['required-entry'] = false;
                city.visible(false);
            });
        },

        disablePostcode: function () {
            registry.get((this.parentName + '.' + 'postcode'), function (postcode) { //customName == custom Entry??
                // postcode.validation['required-entry'] = true;
                postcode.disabled(true);
            });
        },

        initAttr: function () {
            // registry.get((this.parentName + '.' + 'city_id_input'), function (cityId) { //customName == custom Entry??
            //     cityId.required(true);
            // });
        }

    });

});
