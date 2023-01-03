define(['jquery'], function($) {
    'use strict';

    return function() {
        $.validator.addMethod(
            'validate-address-name-admin',
            function(value, element) {
                return /^[a-zA-Z ]{4,10}$/.test(value) || /^[\u4e00-\u9fa5]{2,5}$/.test(value) || /^\s/.test(value);
            },
            $.mage.__('Customer name must be the most 5 Chinese alphabets or 10 English alphabets and should not contain spaces.')
        );
    }
});
