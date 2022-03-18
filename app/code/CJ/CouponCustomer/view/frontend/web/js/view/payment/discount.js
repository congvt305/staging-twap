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
], function ($, ko, Component, quote, setCouponCodeAction, cancelCouponAction, coupon, modal, popupTpl ) {
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
    isApplied(couponCode() != null);

    //define coupon popup

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
         * Cancel using coupon
         */
        cancel: function () {
            if (this.validate()) {
                couponCode('');
                cancelCouponAction(isApplied);
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

        showPopup: function() {
            popup.openModal();
            var couponCodeApplied = $('#discount-code').val();
            $('#' + couponCodeApplied).text('Cancel');
            $('#' + couponCodeApplied).addClass('applied-button');
        },
        applyCouponPopup: function(data, event) {
            console.log("you clicked " + event.target.id);
            couponCode = event.target.id;
            console.log(couponCode);
            if(couponCode == couponAppliedPopup) {
                console.log("cancel");
                cancelCouponAction(coupon.getIsApplied(false));
                couponAppliedPopup = '';
                $('#discount-code').val('');
                popup.closeModal();
            }
            else
            {
                setCouponCodeAction(couponCode, coupon.getIsApplied(true));
                $('#discount-code').val(couponCode);
                couponAppliedPopup = couponCode;
                popup.closeModal();
            }
        },
    });
});
