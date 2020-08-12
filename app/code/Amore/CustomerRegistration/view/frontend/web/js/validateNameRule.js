/*
 *  @author Eguana Team
 *  @copyriht Copyright (c) ${YEAR} Eguana {http://eguanacommerce.com}
 *  Created byPhpStorm
 *  User: arslan
 *  Date: 12/8/20
 *  Time: 7:30 pm
 */
define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';
    /**
     *This function is used to to validate the user last and first name
     */
    return function () {
        $.validator.addMethod(
            "validatenamerule",
            function (value, element) {
                let validator = this;
                let validNameMessage = '';
                let flag = false;
                let notAllowedChar = $('#validate_name_char_hidden').val();
                let chars = notAllowedChar.split('');
                chars.forEach(function (item) {
                    if (value.indexOf(item) > -1)
                    {
                        flag = true;
                    }
                });
                if (flag){
                    validNameMessage = $.mage.__('These characters (%1) are not allowed.');
                    validator.nameErrorMessage = validNameMessage.replace('%1', notAllowedChar);
                    return false;
                }
                return true;
            },
            function () {
                return this.nameErrorMessage;
            }
        );
    };
});
