define([
    'jquery',
    'ko',
    'underscore',
    'uiComponent',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/shipping-save-processor',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/select-billing-address',
    'CJ_SFLocker/js/model/custom-shipping-validator',
    'mage/translate'
], function (
    $,
    ko,
    _,
    Component,
    customer,
    addressConverter,
    customerData,
    shippingProcessor,
    setShippingInformation,
    getTotalsAction,
    quote,
    selectShippingAddress,
    checkoutData,
    selectBillingAddress,
    customShippingValidator,
    $t
) {
    'use strict';

    var validators = [],
        firstLoad = true,
        enableMacau = parseInt(window.checkoutConfig.enable_macau);
    validators.push(customShippingValidator);

    $(document).on('change', "[name='home_delivery_city_id'], .home-delivery-street-input, [name='firstname'], [name='lastname'], [name='country_pos_code'], [name='telephone']", function () {
        triggerChange();
    });

    $(document).on('change', "[name='home_delivery_region_id']", function () {
        triggerChange();
        if (firstLoad === false) {
            setShippingInformation().done(function () {
                var deferred = $.Deferred();
                getTotalsAction([], deferred);
            });
        }
        firstLoad = false;
    });

    function triggerChange() {

        let street = [];
        $('.home-delivery-street-input').each(function (key, element) {
            street.push($(element).val())
        })

        let firstname = $('[name="firstname"]').val(),
            lastname = $('[name="lastname"]').val(),
            cityElement = $('#home_delivery_city_id'),
            city = cityElement.find(":selected").text(),
            telephone = $('[name="telephone"]').val(),
            regionId = $('#home_delivery_region_id').val(),
            countryId = $('[name="country_id"]').val();

        let address = {
                firstname: firstname,
                lastname: lastname,
                street: street,
                city: city,
                telephone: telephone,
                region_id: regionId,
                country_id: countryId
            },
            shippingMethod = quote.shippingMethod();
        if (shippingMethod) {

            if (shippingMethod.carrier_code === 'vlogic' && validateAddressData(address)) {
                let shippingAddress = addressConverter.formAddressDataToQuoteAddress(address);
                selectShippingAddress(shippingAddress)
                checkoutData.setSelectedShippingAddress(shippingAddress.getKey());
            } else if (shippingMethod.carrier_code !== 'vlogic') {
                let quoteAddress = quote.shippingAddress();
                quoteAddress.firstname = firstname;
                quoteAddress.lastname = lastname;
                quoteAddress.telephone = telephone;
                let addressForm = addressConverter.quoteAddressToFormAddressData(quoteAddress);

                if (validateAddressData(addressForm)) {
                    selectShippingAddress(quoteAddress);
                    checkoutData.setSelectedShippingAddress(quoteAddress.getKey());
                    checkoutData.setSelectedPickupAddress(
                        addressForm
                    );
                    selectBillingAddress(quoteAddress);
                    setShippingInformation().done(function () {
                        var deferred = $.Deferred();
                        getTotalsAction([], deferred);
                    });
                }

            }
        }
    }

    /**
     *
     * @param address
     * @returns {boolean}
     */
    function validateAddressData(address) {
        return validators.some(function (validator) {
            return validator.validate(address);
        });
    }

    return Component.extend({
        defaults: {
            template: 'CJ_SFLocker/home-delivery-selector',
            homeDeliveryCity: ko.observableArray([]),
            homeDeliveryRegion: ko.observableArray([]),
            defaultShipping: ko.observableArray([]),
            isNeedLoad: ko.observable(false),
            firstLoad: ko.observable(true)
        },

        /**
         * Init component
         *
         * @return {exports}
         */
        initialize: function () {
            this._super();
            let self = this;
            _.each(customer.customerData.addresses, function (value, key) {
                if (value.default_shipping == true) {
                    self.defaultShipping(value);
                }
            })
            this.getRegionValue();
            return this;
        },

        /**
         *  get region value
         */
        getRegionValue: function () {
            let regions = [],
                defaultValue = {default_name: $t('Please select a region'), region_id: 0};

            regions.push(defaultValue);
            _.each(customerData.get('directory-data')(), function (value, country) {
                if (value['regions']) {
                    _.each(value['regions'], function (regionData, regionId) {
                        if (regionData['code'] == 'H' || (regionData['code'] == 'M' && enableMacau)) {
                            regions.push({default_name: $t(regionData['name']), region_id: regionId});
                        }
                    })
                }
            });
            this.homeDeliveryRegion(regions);
        },

        /**
         *  select home delivery city function
         */
        regionUpdater: function () {
            let regionElement = $('#home_delivery_region_id'),
                selectedValue = regionElement.find(":selected").val(),
                cities = [],
                defaultValue = {default_name: $t('Please select a city'), city_id: 0};
            var self = this;
            cities.push(defaultValue);
            _.each(customerData.get('directory-data')(), function (value, country) {
                if (value['regions']) {
                    _.each(value['regions'], function (regionData, regionId) {
                        if (regionId === selectedValue) {
                            let citiesUpdate = [];
                            _.each(regionData['city'], function (regionCity, key) {
                                citiesUpdate.push({
                                    default_name: self.translateLabel(regionCity['default_name'].trim()),
                                    city_id: regionCity['city_id']
                                });
                            })
                            cities = citiesUpdate;
                        }
                    })
                }
            });
            this.homeDeliveryCity(cities);
        },

        translateLabel: function (label) {
            switch (label) {
                case 'Kowloon':
                    return '九龍';
                case 'Hong Kong Island':
                    return '香港島';
                case 'New Territories':
                    return '新界';
                case 'Municipality of the Islands':
                    return '海島市';
                case 'Municipality of Macau':
                    return '澳門市';
            }
            return label;
        },

        /**
         * save shipping address
         * @param shippingAddress
         */
        saveAddress: function (shippingAddress) {
            selectShippingAddress(shippingAddress)
            checkoutData.setSelectedShippingAddress(shippingAddress.getKey());
        },

        /**
         * fill data when choose or create new address in list
         * @param address
         */
        fillShippingAddressInfo: function (address) {
            let regionElement = $('#home_delivery_region_id'),
                cityElement = $('#home_delivery_city_id'),
                self = this;
            self.fillCustomerInfo(address);
            if (address['street']) {
                _.each(address['street'], function (street, index) {
                    $('[name="home_delivery_street[' + index + ']"]').val(street);
                })
                if (address['regionId']) {
                    regionElement.val(address['regionId']);
                    self.regionUpdater();
                    if (address['city']) {
                        _.each(this.homeDeliveryCity(), function (value, index) {
                            if (value['default_name'] === address['city']) {
                                cityElement.val(value['city_id'])
                            }
                        })
                    }
                }
            }

            this.saveAddress(address);
            this.isNeedLoad(true);
            setShippingInformation().done(function () {
                var deferred = $.Deferred();
                getTotalsAction([], deferred);
            });
        },

        /**
         *
         * @param address
         */
        fillCustomerInfo: function (address) {
            let shippingAddressForm = $('#checkout-step-shipping #co-shipping-form');
            if (!address.firstname) {
                shippingAddressForm.find('[name="firstname"]').val(this.defaultShipping().firstname)
            } else {
                shippingAddressForm.find('[name="firstname"]').val(address.firstname)
            }

            if (!address.lastname) {
                shippingAddressForm.find('[name="lastname"]').val(this.defaultShipping().lastname)
            } else {
                shippingAddressForm.find('[name="lastname"]').val(address.lastname)
            }

            if (!address.telephone) {
                shippingAddressForm.find('[name="telephone"]').val(this.defaultShipping().telephone)
            } else {
                shippingAddressForm.find('[name="telephone"]').val(address.telephone)
            }
            if (!address.customAttributes && this.defaultShipping().custom_attributes.length > 0) {
                shippingAddressForm.find('[name="country_pos_code"]').val(this.defaultShipping().custom_attributes.country_pos_code.value);
            } else {
                _.each(address.customAttributes, function (value, key) {
                    if (value.attribute_code == 'country_pos_code') {
                        if (typeof value.value == 'string') {
                            shippingAddressForm.find('[name="country_pos_code"]').val(value.value)
                        } else {
                            shippingAddressForm.find('[name="country_pos_code"]').val(value.value.value)
                        }
                    }
                })
            }
        }
    });

});
