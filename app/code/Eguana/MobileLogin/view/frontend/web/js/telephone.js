define([
    'jquery',
], function($) {
    'use strict';
    const LIMIT_CN = 11; // number of digits limit for CHINE
    const LIMIT_DEFAULT = 8;

    $("#login-form").on("change", "[name='country_mobile_id']", function () {
        refreshErrMsg();
    });
    let refreshErrMsg = function () {
        let limit = LIMIT_DEFAULT;
        if ($("#login-form").find("[name='country_mobile_id']").val() === 'CN') {
            limit = LIMIT_CN;
        }

        $.validator.addMethod(
            'mobileloginvalidationrule',
            $.validator.methods["mobileloginvalidationrule"],
            $.mage.__('Please enter an {0}-digit phone number.').replace('{0}', limit)
        );
    }
    refreshErrMsg();
    $.widget('mage.validate_tel', {
        _create: function () {
            let self = this;
            $("#login-form").on("keypress", "#phone", self.validateChar);
            $("#login-form").on("input", "#phone", self.validateTel);
            refreshErrMsg();
        },
        validateTel: function (e) {
            let telephone = $(this).val();
            let posCode = $(e.target).closest("#username-block").find("[name='country_mobile_id']");
            let limit = posCode.val() === 'CN' ? LIMIT_CN : LIMIT_DEFAULT;
            let showErr = function(errorMessage) {
                let phoneError = $(document.querySelectorAll("#phone-error"));
                if (phoneError.length) {
                    phoneError.remove();
                }
                $('#phone').after('<div for="phone" class="mage-error" id="phone-error">' + errorMessage + '</div>');
            };

            if (telephone.length === 0) {
                showErr($.mage.__('This is a required field.'));
            } else if  (telephone.length === limit) {
                $("#phone-error").remove();
                return true;
            } else {
                showErr($.mage.__('Please enter an {0}-digit phone number.').replace('{0}', limit));
                return false;
            }
        },
        validateChar: function(e) {

            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
                // Allow: Ctrl+A, Command+A
                (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                // let it happen, don't do anything
                return true;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                return false;
            }

            var number = ['1','2','3','4','5','6','7','8','9','0'];
            if($.inArray(e.key,number) === -1){
                return false;
            }

            let posCode = $(e.target).closest("#username-block").find("[name='country_mobile_id']");
            let limit = posCode.val() === 'CN' ? LIMIT_CN : LIMIT_DEFAULT;
            if ($(e.target).val().length < limit) {
                return true;
            } else {
                return false;
            }

            return true;
        }
    });

    return $.mage.validate_tel;
});
