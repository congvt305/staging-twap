define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function (payloadExtender) {
        return wrapper.wrap(payloadExtender, function (proceed, payload) {
            payload = proceed(payload);
            var delivery_message_identifier = '[name="delivery_message"]';
            var delivery_message_value = $(delivery_message_identifier).val();
            payload.addressInformation.extension_attributes.delivery_message = delivery_message_value;
            return payload;
        });
    };
});