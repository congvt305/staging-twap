define([
    'jquery',
    'moment'
], function ($, moment) {
    'use strict';

    return function (validator) {

        validator.addRule(
            'validate-vn-address-mobile-number',
            function(value, element) {
                return /^[0]{1}\d{9}$/.test(value);
            },
            $.mage.__('Please enter exactly proper mobile number. Start with 0 and 10 digit.')
        );
        return validator;
    };
});
