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
    const LIMIT_CN = 11; // number of digits limit for CHINE
    const LIMIT_DEFAULT = 8;
    /**
     * Validate customer email and phone num
     * Phone num length should be between 10 and 11
     */
    return function () {
        $.validator.addMethod(
            "validate-telephone-number",
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
                    let posCode = $("#country-mobile");
                    let limit = posCode.val() === 'CN' ? LIMIT_CN : LIMIT_DEFAULT;
                    return value.length === limit;
                }
                return false;
            },
            $.mage.__('Please enter an {0}-digit phone number.').replace('{0}', LIMIT_DEFAULT)
        );
    };
});
