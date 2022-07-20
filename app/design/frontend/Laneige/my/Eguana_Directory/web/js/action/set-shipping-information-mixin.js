/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();
            if (shippingAddress.customAttributes) {
                if (shippingAddress['extension_attributes'] === undefined) {
                    shippingAddress['extension_attributes'] = {};
                }

                var cityId = shippingAddress.customAttributes.find(
                    function (element) {
                        return element.attribute_code === 'city_id';
                    }
                );
                var wardId = shippingAddress.customAttributes.find(
                    function (element) {
                        return element.attribute_code === 'ward_id';
                    }
                );
                var ward = shippingAddress.customAttributes.find(
                    function (element) {
                        return element.attribute_code === 'ward';
                    }
                );

                if (cityId) {
                    shippingAddress['extension_attributes']['city_id'] = cityId.value;
                }
                if (wardId) {
                    shippingAddress['extension_attributes']['ward_id'] = wardId.value;
                }
                if (ward) {
                    shippingAddress['extension_attributes']['ward'] = ward.value;
                }
            }

            // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            return originalAction();
        });
    };
});
