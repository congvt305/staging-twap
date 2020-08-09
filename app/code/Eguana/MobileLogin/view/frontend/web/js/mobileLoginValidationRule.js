/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 22/6/20
 * Time: 7:26 PM
 */
define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';
    /**
     * Validate customer email and phone num
     * Phone num length should be between 10 and 11
     */
    return function () {
        $.validator.addMethod(
            "mobileloginvalidationrule",
            function (value, element) {
                var validator = this;
                validator.mobileErrorMessage = $.mage.__(
                    "Please enter a valid email or phone num"
                );
                let regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                if (regex.test(value)) {
                    return true;
                } else {
                    validator.mobileErrorMessage = $.mage.__(
                        "Please enter a valid email or phone num"
                    );
                }
                let isnum = /^\d+$/.test(value);
                if (isnum) {
                    if (value.length < 10) {
                        validator.mobileErrorMessage = $.mage.__(
                            "Please enter at least 10 characters in mobile no"
                        );
                    }
                    if (value.length > 11) {
                        validator.mobileErrorMessage = $.mage.__(
                            "Please enter no more than 11 characters."
                        );
                    }
                    if (value.length >= 10 && value.length <= 11) {
                        return true;
                    }
                }
                return false;
            },
            function () {
                return this.mobileErrorMessage;
            }
        );
    };
});
