/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Amasty_Coupons/js/action/apply-coupon-codes',
    'Magento_SalesRule/js/action/set-coupon-code',
    'Magento_SalesRule/js/action/cancel-coupon',
    'Magento_SalesRule/js/model/coupon',
    'Magento_Ui/js/modal/modal',
    'text!CJ_CouponCustomer/template/modal/modal-popup.html',
    'mage/translate',
    'Amasty_Coupons/js/model/abstract-apply-response-processor',
    'domReady!'
], function ($, ko, Component, quote, setCouponCodesAction, setCouponCodeAction, cancelCouponAction, coupon, modal, popupTpl, $t, responseProcessor) {
    'use strict';

    var totals = quote.getTotals(),
        couponCode = coupon.getCouponCode(),
        isApplied = coupon.getIsApplied();

    var couponList = window.checkoutConfig.cj_couponcustomer.coupon_list;

    var isEnableCouponPopup = window.checkoutConfig.cj_couponcustomer.active_popup;

    var websiteCode = window.checkoutConfig.cj_couponcustomer.website_code;

    var template = 'CJ_CouponCustomer/payment/discount';

    if(websiteCode == 'base') {
        template = 'CJ_CouponCustomer/payment/sws/discount'
    }
    if (totals()) {
        couponCode(totals()['coupon_code']);
    }
    //customize here
    isApplied(couponCode() != null && couponCode() !== '');

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

    var couponAppliedPopup = '';

    var delayInMilliseconds = 3000; // 3 second
    setTimeout(function() {
        if(isEnableCouponPopup) {
            popup = modal(options, $('#modal'));
        }
    }, delayInMilliseconds);

    return Component.extend({
        defaults: {
            template: template
        },
        couponCode: couponCode,
        responseProcessor: responseProcessor,
        errorMessage: $t('Coupon code is not valid'),
        successMessage: $t('Your coupon was successfully applied.'),
        /**
         * Coupon list
         */
        couponList : ko.observableArray(couponList),

        /**
         * Is enable coupon popup
         */
        isEnableCouponPopup: ko.observable(isEnableCouponPopup),

        /**
         * Applied flag
         */
        isApplied: isApplied,

        /**
         * Coupon code application procedure
         */
        apply: function () {
            if (this.validate()) {
                setCouponCodeAction(couponCode(), isApplied);
            }
        },

        /**
         * Coupon code application when turn on coupon popup
         */
        applyPopup: function() {
            couponAppliedPopup = $('#discount-code').val();
            setCouponCodesAction([couponAppliedPopup], this.responseProcessor)
                .done(function () {
                    this.handleErrorMessages();
                    if (this.responseProcessor.appliedCoupons.length > 0 || this.responseProcessor.notChangedCoupons.length > 0) {
                        this.isApplied(true);
                        let messages = this.getChild('errors');
                        messages.messageContainer.clear();
                        messages.messageContainer.addSuccessMessage({
                            'message': this.successMessage
                        });
                        window.location.reload();
                    }
                }.bind(this));
        },

        /**
         * @returns {void}
         */
        handleErrorMessages: function () {
            var messages = this.getChild('errors');

            messages.messageContainer.clear();

            _.each(responseProcessor.errorCoupons, function (code) {
                messages.messageContainer.errorMessages.push(code + ' ' + this.errorMessage);
            }, this);
        },

        /**
         * Cancel using coupon
         */
        cancel: function () {
            if (this.validate()) {
                couponCode('');
                cancelCouponAction(isApplied);
                couponAppliedPopup = '';
                $('#discount-code').val('');
            }
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            var form = '#discount-form';

            return $(form).validation() && $(form).validation('isValid');
        },

        /**
         * create popup base on website
         *
         * @returns {Boolean}
         */

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
            this.createPopupWebsite();
            // change text to cancel for coupon applied
            $('.discount-card-button').removeClass('applied-button');
            $('.discount-card-button').text($t('Apply'));

            if (coupon.isApplied()) {
                var couponCodeApplied = $('#discount-code').val();
                $('#' + couponCodeApplied).text($t('Cancel'));
                $('#' + couponCodeApplied).addClass('applied-button');
            }

            popup.openModal();
        },
        /**
         * apply coupon on coupon popup
         *
         */

        applyCouponPopup: function(data, event) {
            var _couponCode = event.target.id;
            var couponAppliedPopup = $('#discount-code').val()
            if (coupon.isApplied() && _couponCode == couponAppliedPopup) {
                cancelCouponAction(coupon.getIsApplied(false));
                couponAppliedPopup = '';
                $('#discount-code').val('');
                popup.closeModal();
            } else {
                setCouponCodeAction(_couponCode, coupon.getIsApplied(true))
                    .done(function (response) {
                        $('#discount-code').val(_couponCode);
                        couponAppliedPopup = _couponCode;
                        popup.closeModal();
                        window.location.reload();
                    })
                    .fail(function (response) {
                        couponCode('');
                        isApplied(false);
                        $('#discount-code').val(_couponCode);
                        popup.closeModal();
                        window.location.reload();
                    }
                );
            }
        },
    });
});
