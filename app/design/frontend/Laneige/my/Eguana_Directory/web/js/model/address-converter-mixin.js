define([
    'jquery',
    'mage/utils/wrapper',
    'mage/utils/objects',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/new-customer-address',
], function ($, wrapper, mageUtils, customerData, address) {
    'use strict';

    var countryData = customerData.get('directory-data');

    return function (addressConverter) {
        addressConverter.formAddressDataToQuoteAddress = wrapper.wrapSuper(
            addressConverter.formAddressDataToQuoteAddress, function (formData) {
                // clone address form data to new object
                var addressData = $.extend(true, {}, formData),
                    region,
                    regionName = addressData.region,
                    customAttributes;

                if (mageUtils.isObject(addressData.street)) {
                    addressData.street = this.objectToArray(addressData.street);
                }

                addressData.region = {
                    'region_id': addressData['region_id'],
                    'region_code': addressData['region_code'],
                    region: regionName
                };

                if (addressData['region_id'] &&
                    countryData()[addressData['country_id']] &&
                    countryData()[addressData['country_id']].regions
                ) {
                    region = countryData()[addressData['country_id']].regions[addressData['region_id']];

                    if (region) {
                        addressData.region['region_id'] = addressData['region_id'];
                        addressData.region['region_code'] = region.code;
                        addressData.region.region = region.name;
                    }
                } else if (
                    !addressData['region_id'] &&
                    countryData()[addressData['country_id']] &&
                    countryData()[addressData['country_id']].regions
                ) {
                    addressData.region['region_code'] = '';
                    addressData.region.region = '';
                }
                delete addressData['region_id'];

                var cityId = addressData.city_id,
                    wardId = addressData.ward_id,
                    ward = addressData.ward;
                if (cityId && wardId && ward) {
                    addressData['custom_attributes'] = {
                        'city_id': addressData['city_id'],
                        'ward_id': addressData['ward_id'],
                        'ward': addressData['ward'],
                    }
                }

                if (addressData['custom_attributes']) {
                    addressData['custom_attributes'] = _.map(
                        addressData['custom_attributes'],
                        function (value, key) {
                            customAttributes = {
                                'attribute_code': key,
                                'value': value
                            };

                            if (typeof value === 'boolean') {
                                customAttributes = {
                                    'attribute_code': key,
                                    'value': value,
                                    'label': value === true ? 'Yes' : 'No'
                                };
                            }

                            return customAttributes;
                        }
                    );
                }

                return address(addressData);
        });

        return addressConverter;
    };
});
