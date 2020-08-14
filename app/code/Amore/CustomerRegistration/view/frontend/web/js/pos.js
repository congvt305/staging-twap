/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
    $.widget('mage.pos', {
        options: {
            getCodeSelector : '.sms-link',
            verifyCodeSelector : '.verify-link',
            verifyPosSelector : '.verify-registration-link',
            firstNameSelector : '.first-name',
            lastNameSelector : '.last-name',
            mobileNumberSelector : '.mobile-number',
            codeSelector : '.code',


        },

        _init: function() {
            if(this.options.currentStep == 2)
            {
                if ($('.form-create-account #firstname').val()) {
                    $('.form-create-account-pos').hide();
                    $('.customer-registration-form-create-account').show();
                    $('.customer-registration-form-create-account .form-create-account').show();
                    $('.form-create-account #firstname').prop("readonly", true);
                    $('.form-create-account #lastname').prop("readonly", true);
                }
            }
        },
        /**
         * Method binds click event to get SMS, verify code and timer value
         * @private
         */
        _create: function () {

            $('.form-create-account-pos').on('click', this.options.getCodeSelector, $.proxy(this.getCode, this));
            $('.form-create-account-pos').on('click', this.options.verifyCodeSelector, $.proxy(this.verifyCode, this));
            $('.form-create-account-pos').on('click', this.options.verifyPosSelector, $.proxy(this.posVerification, this));
            timer2 = this.options.codeExpirationMinutes;
        },

        getCode: function() {

            $('.pos-verification-message').hide();
            var firstNameIsValid = this.isFieldValid('first_name');
            var lastNameIsValid = this.isFieldValid('last_name');
            var mobileNumberIsValid = this.isFieldValid('mobile_number');

            if(firstNameIsValid && lastNameIsValid && mobileNumberIsValid){
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
            var firstNameIsValid = this.isFieldValid('first_name');
            var lastNameIsValid = this.isFieldValid('last_name');

            if(codeIsValid && mobileNumberIsValid && firstNameIsValid && lastNameIsValid){
                $.ajax({
                    url: this.options.POSVerificationUrl,
                    type: 'post',
                    showLoader: true,
                    dataType: 'json',
                    context: this,
                    cache: false,
                    data: {
                        'firstName':$(this.options.firstNameSelector).val(),
                        'lastName':$(this.options.lastNameSelector).val(),
                        'mobileNumber':$(this.options.mobileNumberSelector).val(),
                        'code':$(this.options.codeSelector).val()
                    },

                    /**
                     * @param {Object} response
                     */
                    success: function (response) {

                        if (response.verify) {
                            $('.form-create-account #firstname').prop("readonly", true);
                            $('.form-create-account #lastname').prop("readonly", true);
                            $('.form-create-account #mobile_number').prop("readonly", true);
                            if(response.pos.firstName){
                                $('.customer-registration-form-create-account .pos-alert').show();
                                $('.form-create-account #firstname').val(response.pos.firstName);
                                $('.form-create-account #lastname').val(response.pos.lastName);
                                $('.form-create-account #mobile_number').val($(this.options.mobileNumberSelector).val());
                                $('.form-create-account #email_address').val(response.pos.email);
                                $('.form-create-account #integration_number').val(response.pos.cstmIntgSeq);
                                //$('.form-create-account #gender').val(response.pos.sex == 'M'?1:4);
                                if (response.pos.sex == 'F') {
                                    $(".form-create-account #gender_Female").prop("checked", true);
                                }
                                $('.form-create-account #dob').val(response.pos.birthDay);
                                $('.form-create-account #is_subscribed').prop('checked',response.pos.emailYN == 'Y'?true:false);
                                $('.form-create-account .dm_subscription_status_checkbox').prop('checked',response.pos.dmYN == 'Y'?true:false);
                                $('.form-create-account #dm_subscription_status').val(response.pos.dmYN == 'Y'?1:0);
                                if (response.pos.homeCity) {
                                    $('.form-create-account #region_id').val(response.pos.region.region_id).trigger('change');
                                    $('.form-create-account #dm_state').val(response.pos.region.default_name);
                                }
                                if (response.pos.homeState) {
                                    $('.form-create-account #city_id').val(response.pos.city.code);
                                    $('.form-create-account #dm_city').val(response.pos.city.name);
                                }
                                $('.form-create-account #dm_detailed_address').val(response.pos.homeAddr1);
                                $('.form-create-account #dm_zipcode').val(response.pos.homeZip);
                                $('.form-create-account #imported_from_pos').val(1);
                                if(response.pos.dmYN == 'Y')
                                {
                                    $('.dm-address').toggle();
                                }

                            }else {
                                $('.form-create-account #firstname').val($(this.options.firstNameSelector).val());
                                $('.form-create-account #lastname').val($(this.options.lastNameSelector).val());
                                $('.form-create-account #mobile_number').val($(this.options.mobileNumberSelector).val());
                            }
                            $('.form-create-account-pos').hide();
                            $('.customer-registration-form-create-account').show();
                            $('.customer-registration-form-create-account .form-create-account').show();
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

    return $.mage.pos;
});
