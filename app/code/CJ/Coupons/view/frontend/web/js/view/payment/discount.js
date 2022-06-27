/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
define(
    [
        'jquery',
        'ko',
        'underscore',
        'mage/translate',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Ui/js/model/messageList',
        'Amasty_Coupons/js/action/set-coupon-code',
        'Magento_SalesRule/js/action/cancel-coupon',
        'Magento_SalesRule/js/model/coupon',
        'Magento_Ui/js/modal/modal',
        'text!CJ_Coupons/template/modal/modal-popup.html',
        'domReady!'
    ],
    function ($, ko, _, $t, Component, quote, messageList, setCouponCodeAction, cancelCouponAction,couponAction, modal, popupTpl) {
        'use strict';

        var totals = quote.getTotals(),
            couponCode = ko.observable(''),
            fakeCouponCode = ko.observable('');

        var couponList = window.checkoutConfig.cj_couponcustomer.coupon_list;

        var isEnableCouponPopup = window.checkoutConfig.cj_couponcustomer.active_popup;

        var websiteCode = window.checkoutConfig.cj_couponcustomer.website_code;

        var appliedCouponList = [];

        var template = 'CJ_Coupons/payment/discount';

        if(websiteCode == 'base') {
            template = 'CJ_Coupons/payment/sws/discount'
        }

        //define coupon popup
        var options = {
            type: 'popup',
            responsive: true,
            title: $t('Coupon List'),
            innerScroll: true,
            popupTpl: popupTpl,
            buttons: [{
                text: $.mage.__('Ok'),
                class: '',
                click: function () {
                    this.closeModal();
                }
            }]
        };

        var popup = null;

        var delayInMilliseconds = 3000; // 3 second
        setTimeout(function() {
            if(isEnableCouponPopup) {
                popup = modal(options, $('#modal'));
            }
        }, delayInMilliseconds);

        if (totals()['coupon_code']) {
            couponCode(totals()['coupon_code']);
        }

        var isApplied = ko.observable(couponCode() != null);
        var isLoading = ko.observable(false);
        var message = $t('Your coupon was successfully applied'),
            messageError = $t('Coupon code is not valid'),
            messageDelete = $t('Coupon code was removed');
        return Component.extend({

            defaults: {
                template: template
            },
            initialize: function () {
                this._super();
            },

            couponCode: couponCode,
            fakeCouponCode: fakeCouponCode,

            /**
             * Applied flag
             */
            isApplied: isApplied,
            isLoading: isLoading,

            /**
             * Coupon list
             */
            couponList : ko.observableArray(couponList),

            /**
             * Is enable coupon popup
             */
            isEnableCouponPopup: ko.observable(isEnableCouponPopup),

            createPopupWebsite: function() {
                if (websiteCode == 'tw_lageige_website') {
                    $('.coupon-header').addClass('lng-coupon-popup-color');
                    $('.discount-bar').addClass('lng-coupon-popup-color');
                    $('.discount-border-right').addClass('lng-discount-border-right');
                    $('.discount-card-button').addClass('lng-discount-card-button');
                }
                // for sws website
                if (websiteCode == 'base') {
                    $('.coupon-header').addClass('sws-coupon-popup-color');
                    $('.discount-bar').addClass('sws-coupon-popup-color');
                    $('.discount-border-right').addClass('sws-discount-border-right');
                    $('.discount-card-button').addClass('sws-discount-card-button');
                }

            },

            /**
             * show popup
             *
             */

            showPopup: function() {
                var currentCode = this.couponCode().split(',');
                if(!appliedCouponList.length) {
                    appliedCouponList = appliedCouponList.concat(currentCode);
                }
                appliedCouponList = appliedCouponList.filter(function (n) {
                    return n != ''
                })
                this.createPopupWebsite();
                // change text to cancel for coupon applied
                $('.discount-card-button').removeClass('applied-button');
                $('.discount-card-button').text($t('Apply'));

                for (let i = 0; i < currentCode.length; i++) {
                    $('#' + currentCode[i]).text($t('Cancel'));
                    $('#' + currentCode[i]).addClass('applied-button');
                }
                popup.openModal();
            },

            removeSelected : function (coupon) {
                var currentCodeList = this.couponCode().split(',');
                var index = currentCodeList.indexOf(coupon);
                if (index > -1) {
                    currentCodeList.splice(index, 1);
                }

                isLoading(true);
                if( currentCodeList.length > 0 ){
                    setCouponCodeAction(currentCodeList.join(','), isApplied, true).done(function (response) {
                        $('.totals.discount .title').removeClass('negative');
                        this.couponCode(response);
                        messageList.addSuccessMessage({'message': messageDelete});
                        this.fakeCouponCode('');
                        var index = appliedCouponList.indexOf(coupon);
                        if (index > -1) {
                            appliedCouponList.splice(index, 1);
                        }
                    }.bind(this)).always(function(){
                        isLoading(false);
                    });
                }else{
                    this.couponCode('');
                    appliedCouponList = [];
                    cancelCouponAction(isApplied, isLoading).always(function(){
                        isLoading(false);
                    });
                }
            },

            /**
             * Coupon code application procedure
             */
            apply: function() {
                if (this.validate()) {
                    isLoading(true);
                    var newDiscountCode =  this.fakeCouponCode();
                    var code = [];
                    code = this.couponCode().split(',');
                    code.push(newDiscountCode);
                    code = code.filter(function(n){ return n != '' });
                    code = code.join(',');
                    setCouponCodeAction(code, isApplied).done(function (response) {
                        $('.totals.discount .title').removeClass('negative');
                        var codeList =  response.split(',');
                        this.couponCode(response);

                        var newCode = this.fakeCouponCode().split(',');
                        if (_.difference(newCode, codeList).length) {
                            messageList.addErrorMessage({'message': messageError});
                        } else{
                            messageList.addSuccessMessage({'message': message});
                        }
                        this.fakeCouponCode('');
                    }.bind(this)).always(function(){
                        isLoading(false);
                    });
                }
            },

            /**
             * Cancel using coupon
             */
            cancel: function() {
                if (this.validate()) {
                    isLoading(true);
                    couponCode('');
                    cancelCouponAction(isApplied, isLoading);
                }
            },

            /**
             * Coupon form validation
             *
             * @returns {boolean}
             */
            validate: function() {
                var form = '#discount-form';
                return $(form).validation() && $(form).validation('isValid');
            },

            /**
             * Apply coupon on coupon popup
             *
             */

            applyCouponPopup: function() {
                var newCodePopup = this.code;
                var code = [];
                if(!appliedCouponList.includes(newCodePopup)) {
                    appliedCouponList.push(newCodePopup);
                    code = appliedCouponList.filter(function (n) {
                        return n != ''
                    });
                    code = appliedCouponList.join(',');
                    if (_.difference(newCodePopup, code)) {
                        setCouponCodeAction(code, isApplied).done(function (response) {
                            var codeList = response.split(',');
                            couponCode(response);
                            var newCode = code.split(',');
                            if (_.difference(newCode, codeList).length) {
                                messageList.addErrorMessage({'message': messageError});
                                // remove new code
                                var index = appliedCouponList.indexOf(newCodePopup);
                                if (index > -1) {
                                    appliedCouponList.splice(index, 1);
                                }
                            } else {
                                messageList.addSuccessMessage({'message': message});
                            }
                        }.bind(this)).always(function () {
                            isLoading(false);
                        });
                    }
                }
                else {
                    var index = appliedCouponList.indexOf(newCodePopup);
                    if (index > -1) {
                        appliedCouponList.splice(index, 1);
                    }

                    isLoading(true);
                    if( appliedCouponList.length > 0 ){
                        setCouponCodeAction(appliedCouponList.join(','), isApplied, true).done(function (response) {

                            messageList.addSuccessMessage({'message': messageDelete});
                            var codeList =  response.split(',');
                            couponCode(response);
                        }.bind(this)).always(function(){
                            isLoading(false);
                        });
                    }else{
                        cancelCouponAction(isApplied, isLoading).always(function(){
                            isLoading(false);
                        });
                        couponCode('');
                        appliedCouponList = [];
                    }

                }
                popup.closeModal();
            },
        });
    }
);
