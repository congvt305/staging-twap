/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 3/8/20
 * Time: 6:28 PM
 */
define([
    'jquery',
    'jquery-ui-modules/widget',
    'mage/validation'
], function ($, customerData) {
    'use strict';
    var timer2 = '1:00';
    var refreshIntervalId;
    /**
     * @api
     */
    $.widget('mage.verifymobile', {
        options: {
            getCodeSelector : '.sms-link',
            verifyCodeSelector : '.verify-link',
            verifyPosSelector : '.verify-registration-link',
            mobileNumberSelector : '.mobile-number',
            codeSelector : '.code',
            firstNameSelector : '#firstname',
            lastNameSelector : '#lastname',
        },
        _init: function() {
        },
        /**
         * Method binds click event to get SMS, verify code and timer value
         * @private
         */
        _create: function () {

            $('.form-edit-account').on('click', this.options.getCodeSelector, $.proxy(this.getCode, this));
            $('.form-edit-account').on('click', this.options.verifyCodeSelector, $.proxy(this.verifyCode, this));
            $('.form-edit-account').on('click', this.options.verifyPosSelector, $.proxy(this.posVerification, this));
            $('.form-edit-account').on('keyup', this.options.mobileNumberSelector, $.proxy(this.verifyMobileNumber, this));
            timer2 = this.options.codeExpirationMinutes;
        },
        verifyMobileNumber: function() {
            if(this.options.mobileNumber == $(this.options.mobileNumberSelector).val()){
                $('.sms-link').attr('disabled', true);
                $('.customer-submit').attr('disabled', false);
            } else {
                $('.customer-submit').attr('disabled', true);
                $('.sms-link').attr('disabled', false);
            }
        },
        getCode: function() {

            $('.pos-verification-message').hide();
            $('.customer-submit').attr('disabled', true);
            var mobileNumberIsValid = this.isFieldValid('mobile_number');
            var firstNameIsValid = this.isFieldValid('firstname');
            var lastNameIsValid = this.isFieldValid('lastname');

            if(mobileNumberIsValid && lastNameIsValid && firstNameIsValid){
                $.ajax({
                    url: this.options.sendCodeUrl,
                    type: 'post',
                    showLoader: true,
                    dataType: 'json',
                    context: this,
                    cache: false,
                    data: {
                        'mobileNumber':$(this.options.mobileNumberSelector).val(),
                        'firstName':$(this.options.firstNameSelector).val(),
                        'lastName':$(this.options.lastNameSelector).val()
                    },

                    /**
                     * @param {Object} response
                     */
                    success: function (response) {

                        if (response.send) {
                            timer2 = this.options.codeExpirationMinutes;
                            $('.code').prop( "disabled", false );
                            $('.verify-link').removeAttr( "disabled");
                            refreshIntervalId = setInterval(this.timer, 1000);
                            $('.countdown').html('');
                            $('.countdown').show();
                        }
                        $('.code-send-message').html(response.message);
                        $('.code-send-message').show();
                    },

                    /** Complete callback. */
                    complete: function () {
                        this.element.removeClass(this.options.refreshClass);
                    }
                });

            }
        },
        verifyCode: function() {
            var codeIsValid = this.isFieldValid('code');

            if(codeIsValid){
                var mobileNumberIsValid = this.isFieldValid('mobile_number');
                if(codeIsValid && mobileNumberIsValid){
                    $.ajax({
                        url: this.options.verifyCodeUrl,
                        type: 'post',
                        showLoader: true,
                        dataType: 'json',
                        context: this,
                        cache: false,
                        data: {
                            'mobileNumber':$(this.options.mobileNumberSelector).val(),
                            'code':$(this.options.codeSelector).val()
                        },

                        /**
                         * @param {Object} response
                         */
                        success: function (response) {

                            if (response.verify) {
                                $('.verify-registration-link').removeAttr( "disabled");
                                $('.code-send-message').hide();
                                $('.code').prop( "disabled", true );
                                $('.verify-link').attr( "disabled",'true');
                                $('.countdown').hide();
                                $('.customer-submit').attr('disabled', false);
                                clearInterval(refreshIntervalId);
                            }
                            $('.verification-message').html(response.message);
                            $('.verification-message').show();
                        }
                    });
                }
            }
        },
        posVerification: function() {
            var codeIsValid = this.isFieldValid('code');
            var mobileNumberIsValid = this.isFieldValid('mobile_number');

            if(codeIsValid && mobileNumberIsValid){
                $.ajax({
                    url: this.options.POSVerificationUrl,
                    type: 'post',
                    showLoader: true,
                    dataType: 'json',
                    context: this,
                    cache: false,
                    data: {
                        'mobileNumber':$(this.options.mobileNumberSelector).val(),
                        'code':$(this.options.codeSelector).val()
                    },

                    /**
                     * @param {Object} response
                     */
                    success: function (response) {

                        if (response.verify) {
                            console.log(response);

                        }else if(!response.verify && (response.code == 5 || response.code == 4)){

                            if(response.url)
                            {
                                window.location.replace(response.url);
                            }else {
                                $('.pos-verification-message span').html(response.message);
                                $('.pos-verification-message').show();
                            }
                        }
                        else {
                            $('.pos-verification-message span').html(response.message);
                            $('.pos-verification-message').show();
                        }
                    }
                });
            }

        },
        isFieldValid: function(fieldName) {
            $('input[name='+fieldName+"]").validation();
            return $('input[name='+fieldName+"]").validation('isValid')?true:false;
        },
        timer: function(){
            var timer = timer2.split(':');
            //by parsing integer, I avoid all extra string processing
            var minutes = parseInt(timer[0], 10);
            var seconds = parseInt(timer[1], 10);
            --seconds;
            minutes = (seconds < 0) ? --minutes : minutes;
            if (minutes < 0) {
                $('.code-send-message').hide();
                $('.code').prop( "disabled", true );
                $('.verify-link').attr( "disabled",'true');
                $('.countdown').hide();
                clearInterval(refreshIntervalId);
            }
            seconds = (seconds < 0) ? 59 : seconds;
            seconds = (seconds < 10) ? '0' + seconds : seconds;
            //minutes = (minutes < 10) ?  minutes : minutes;
            $('.countdown').html(minutes + ':' + seconds);
            timer2 = minutes + ':' + seconds;
        },

    });

    return $.mage.verifymobile;
});
