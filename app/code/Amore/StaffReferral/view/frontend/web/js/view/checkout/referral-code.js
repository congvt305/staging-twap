/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Amore_StaffReferral/js/action/set-referral-code',
    'Amore_StaffReferral/js/action/reset-referral-code',
    'Amore_StaffReferral/js/model/referral-information'
], function ($, ko, Component, setCouponCodeAction, resetCouponCodeAction, referralInformation) {
    'use strict';

    return Component.extend({
        referralType: referralInformation.referralType,
        referralCode: referralInformation.referralCode,
        posCountryCode: referralInformation.posCountryCode,
        posPhoneNumber: referralInformation.posPhoneNumber,
        isLoading: false,
        isBcForm: referralInformation.referralType() === '1',
        canResetFF: referralInformation.canResetFF,
        canResetBC: referralInformation.canResetBC,
        countryCodeOptions: [],

        initObservable: function () {
            var checkoutConfig = window.checkoutConfig || {},
                quoteBaCode = checkoutConfig.quoteData && checkoutConfig.quoteData.referral_ba_code,
                quoteFfCode = checkoutConfig.quoteData && checkoutConfig.quoteData.referral_ff_code;
            this._super().observe('isLoading isBcForm canResetBC canResetFF');

            this.referralType.subscribe(function (type) {
                return this.isBcForm(type === '1');
            }.bind(this));
            if (quoteBaCode) {
                this.referralCode(quoteBaCode);
                this.referralType('1');
                this.canResetBC(true);
            } else if (quoteFfCode) {
                this.setFriendCode(quoteFfCode);
            } else {
                this.referralType('1');
            }
            return this;
        },
        /**
         * Coupon code application procedure
         */
        apply: function () {
            if (this.validate()) {
                this.isLoading(true);
                setCouponCodeAction(referralInformation).always(function () {
                    this.isLoading(false);
                }.bind(this));
            }
        },
        /**
         * Coupon code application procedure
         */
        reset: function () {
            if (this.validate()) {
                this.isLoading(true);
                resetCouponCodeAction(referralInformation).always(function () {
                    this.isLoading(false);
                }.bind(this));
            }
        },


        setFriendCode: function (code) {
            this.posPhoneNumber(code);
            this.referralType('2');
            this.canResetFF(true);
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            var form = '#referral-form';
            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
