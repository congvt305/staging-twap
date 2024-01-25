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

        validator.addRule(
            'validate-cvs-address-mobile-number',
            function(value, element) {
                return /^[0]{1}[9]{1}\d{8}$/.test(value);
            },
            $.mage.__('Please enter exactly proper mobile number. Start with 09 and 10 digit.')
        );

        validator.addRule(
            'validate-cvs-address-lastname',
            function(value, element) {
                return /^[a-zA-Z]{1,9}$/.test(value) || /^[\u4e00-\u9fa5]{1,4}$/.test(value) || /^\s/.test(value);
            },
            $.mage.__('Last name must be less than 5 Chinese alphabets or 10 English alphabets.')
        );

        validator.addRule(
            'validate-cvs-address-firstname',
            function(value, element) {
                var lastname = $('#opc-new-shipping-address input[name="lastname"]').val();
                if (typeof lastname != 'undefined') {
                    value = lastname + value;
                    return /^[a-zA-Z]{4,10}$/.test(value) || /^[\u4e00-\u9fa5]{2,5}$/.test(value) || /^\s/.test(value);
                }
                return true;
            },
            $.mage.__('Last name + first name must be the most 5 Chinese alphabets or 10 English alphabets and should not contain spaces.')
        );
        return validator;
    };
});
