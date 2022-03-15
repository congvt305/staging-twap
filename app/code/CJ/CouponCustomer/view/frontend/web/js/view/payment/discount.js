/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/action/set-coupon-code',
    'Magento_SalesRule/js/action/cancel-coupon',
    'Magento_SalesRule/js/model/coupon',
    'Magento_Ui/js/modal/modal',
    'text!CJ_CouponCustomer/template/modal/modal-popup.html'
], function ($, ko, Component, quote, setCouponCodeAction, cancelCouponAction, coupon, modal,popupTpl ) {
    'use strict';


    var totals = quote.getTotals(),
        couponCode = coupon.getCouponCode(),
        isApplied = coupon.getIsApplied();

    var couponList = window.checkoutConfig.cj_couponcustomer.coupon_list;

    var isEnableCouponPopup = window.checkoutConfig.cj_couponcustomer.active_popup;

    var websiteCode = window.checkoutConfig.cj_couponcustomer.website_code;

    var template = 'CJ_CouponCustomer/payment/discount';

    var options = {
        type: 'popup',
        responsive: true,
        title: $.mage.__('Coupon List'),
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
    var delayInMilliseconds = 3000; //1 second

    setTimeout(function() {
        if(isEnableCouponPopup) {
            modal(options, $('#modal'));
            $("#coupon-wallet").on('click', function () {
                var popup = modal(options, $('#modal'));
                popup.openModal();
                // add class css
                // for laneige website
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
                // change text to cancel for coupon applied
                $('.discount-card-button').removeClass('applied-button');
                $('.discount-card-button').text('Apply');

                var couponCodeApplied = $('#discount-code').val();
                $('#' + couponCodeApplied).text('Cancel');
                $('#' + couponCodeApplied).addClass('applied-button');

                $(".discount-card-button").on('click', function () {
                    var couponCode = $(this).attr('id');
                    // cancel coupon code
                    if (couponCode == couponCodeApplied) {
                        cancelCouponAction(coupon.getIsApplied(false));
                        $('#discount-code').val('');
                        $('#' + couponCodeApplied).text('Apply');
                        $('#' + couponCodeApplied).removeClass('applied-button');
                        popup.closeModal();
                    }

                    // applied coupon code
                    else {
                        setCouponCodeAction(couponCode, coupon.getIsApplied(true));
                        coupon.getCouponCode(couponCode);
                        $('#discount-code').val(couponCode);
                        popup.closeModal();
                    }

                });
            });
        }
    }, delayInMilliseconds);


    if (totals()) {
        couponCode(totals()['coupon_code']);
    }
    isApplied(couponCode() != null);

    if(websiteCode == 'base') {
        template = 'CJ_CouponCustomer/payment/sws/discount'
    }

    return Component.extend({
        defaults: {
            template: template
        },

        couponCode: couponCode,

        couponList : ko.observableArray(couponList),

        /**
         * Applied flag
         */
        isApplied: isApplied,

        isEnableCouponPopup: ko.observable(isEnableCouponPopup),

        popupColor: ko.pureComputed(function() {
            return 'sws-coupon-popup-color';
        }),


        /**
         * Coupon code application procedure
         */
        apply: function () {
                setCouponCodeAction(couponCode(), isApplied);
        },

        /**
         * Cancel using coupon
         */
        cancel: function () {
            if (this.validate()) {
                couponCode('');
                cancelCouponAction(isApplied);
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
        }

    });
});
