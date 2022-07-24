define(['jquery', 'mage/utils/wrapper', 'Magento_Customer/js/model/customer',], function ($, wrapper, customer) {
    'use strict';

    return function (payloadExtender) {
        function handleAddress(payload) {
            let shippingAddress = payload.addressInformation.shipping_address,
                billingAddress = payload.addressInformation.billing_address,
                shippingAddressForm = $('#checkout-step-shipping #co-shipping-form');
            shippingAddress.firstname = shippingAddressForm.find('[name="firstname"]').val();
            shippingAddress.lastname = shippingAddressForm.find('[name="lastname"]').val();
            shippingAddress.telephone = shippingAddressForm.find('[name="telephone"]').val();
            if (payload.addressInformation.shipping_carrier_code == 'vlogic') {
                let street = [];
                $('.home-delivery-street-input').each(function (key, element) {
                    street.push($(element).val())
                })
                shippingAddress.street = street;
                shippingAddress.city = $('#home_delivery_city_id').find(":selected").text();
                if (!customer.isLoggedIn()) {
                    if (billingAddress.region == '') {
                        billingAddress.region = shippingAddress.region;
                        billingAddress.regionId = shippingAddress.regionId;
                        billingAddress.regionCode = shippingAddress.regionCode;
                        billingAddress.city = shippingAddress.city;
                        billingAddress.street = shippingAddress.street;
                    }
                }
            }
        }

        return wrapper.wrap(payloadExtender, function (proceed, payload) {
            handleAddress(payload)
            return payload;
        });
    };
});
