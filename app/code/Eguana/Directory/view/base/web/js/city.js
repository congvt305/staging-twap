define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Checkout/js/model/default-post-code-resolver',
], function (_, registry, Select, defaultPostCodeResolver) {
    'use strict';

    return Select.extend({
        defaults: {
            visible: false,
            cityValue: null,
            skipValidation: true,
            // zipcode: '${ $.parentName }.postcode:value',
            imports: {
                update: '${ $.parentName }.region_id:value'
            },
            exports: {
                cityValue: '${ $.parentName }.city:value'
            },
            tracks: {
                cityValue: true
            }

        },

        initialize: function () {
            this._super();
            this.hideCityInput();
            return this;
        },

        onUpdate: function (value) { //value: city id , when city id is changed this get called
            var cityOptions = registry.get(this.provider)['dictionaries']['city_id'];
            var newCityName;
            // var cityData = registry.get(this.parent + '.city'); try test later

            //update city name input
            if (cityOptions[value]) {
                newCityName = cityOptions[value]['title']; //value : city_id
                // console.log('new code: ', this.value);
                // let selector = 'input#' + postcodeInputId + '.input-text';
                // $(selector).val(postcode);
                // this.value(newPostcode);
                this.cityValue = newCityName; //try later : registry this.parent.city value(newCity)
            }
            return this._super();
        },
        /**
         * @param {String} value
         */
        update: function (value) {
            var region = registry.get(this.parentName + '.' + 'region_id'), // checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id
                options = region.indexedOptions,
                isCityRequired,
                option;

            //try to hide city
            this.hideCityInput();

            if (!value) {
                return;
            }
            option = options[value]; //value: region_id

            if (typeof option === 'undefined') {
                return;
            }

            if (this.skipValidation) {
                this.validation['required-entry'] = false;
                this.required(false);
            } else {
                if (option && !option['is_city_required']) { //executed here
                    this.error(false);
                    this.validation = _.omit(this.validation, 'required-entry');
                    registry.get(this.customName, function (input) { //customName == custom Entry??
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

        hideCityInput: function () {
            registry.get((this.parentName + '.' + 'city'), function (city) { //customName == custom Entry??
                city.validation['required-entry'] = false;
                city.visible(false);
            });
        },

    });

});
