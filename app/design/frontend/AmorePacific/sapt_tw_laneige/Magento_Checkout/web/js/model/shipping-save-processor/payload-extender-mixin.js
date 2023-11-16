define(['jquery', 'mage/utils/wrapper', 'Magento_Customer/js/model/customer',], function ($, wrapper, customer) {
    'use strict';

    return function (payloadExtender) {
        return wrapper.wrap(payloadExtender, function (proceed, payload) {
            return payload;
        });
    };
});
