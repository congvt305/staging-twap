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

    //popup

    var totals = quote.getTotals(),
        couponCode = coupon.getCouponCode(),
        isApplied = coupon.getIsApplied();

    var couponList = window.checkoutConfig.cj_couponcustomer.coupon_list;

    var isEnableCouponPopup = window.checkoutConfig.cj_couponcustomer.active_popup;

    var options = {
        type: 'popup',
        responsive: true,
        title: $.mage.__('Coupon List'),
        innerScroll: true,
        clickableOverlay: true,
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
        modal(options, $('#modal'));
        $("#coupon-wallet").on('click',function() {
            $('#modal').modal(options).modal('openModal')
        });
        $(".discount-card-button").on('click',function() {
            var couponCode = $(this).attr('id');
            setCouponCodeAction(couponCode, isApplied);
            $('#modal').modal('closeModal');
            $('#discount-code').val(couponCode);

            // change background and text
        });

    }, delayInMilliseconds);

    if (totals()) {
        couponCode(totals()['coupon_code']);
    }
    isApplied(couponCode() != null);

    return Component.extend({
        defaults: {
            template: 'CJ_CouponCustomer/payment/discount'
        },

        couponCode: couponCode,

        couponList : ko.observableArray(couponList),

        /**
         * Applied flag
         */
        isApplied: isApplied,

        isEnableCouponPopup: ko.observable(isEnableCouponPopup),

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
        }

    });
});
