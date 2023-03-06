/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * ReferralInformation model.
 */
define([
    'jquery',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/url-builder',
    'domReady!'
], function ($, ko, quote, customer, urlBuilder) {
    'use strict';

    var referralType = ko.observable(null),
        referralCode = ko.observable(null),
        posPhoneNumber = ko.observable(null),
        canResetFF = ko.observable(false),
        canResetBC = ko.observable(false);

    return {
        referralType: referralType,
        referralCode: referralCode,
        posPhoneNumber: posPhoneNumber,
        canResetFF: canResetFF,
        canResetBC: canResetBC,
        /**
         * @return {*}
         */
        getReferralType: function () {
            return referralType();
        },

        /**
         * @return {Boolean}
         */
        getReferralCode: function () {
            return referralCode();
        },

        /**
         * @return {Boolean}
         */
        getPosPhoneNumber: function () {
            return posPhoneNumber();
        },

        /**
         * @return {Object}
         */
        getData: function () {
            return {
                referralInformation: {
                    referral_type: this.getReferralType(),
                    referral_code: this.getReferralType() === '1' ? this.getReferralCode() : this.getPosPhoneNumber(),
                }
            }
        },

        /**
         * @return {Object}
         */
        getResetData: function () {
            return {
                referralInformation: {
                    referral_type: 0
                }
            }
        },

        /**
         * @return {String}
         */
        getUrl: function () {
            var url = customer.isLoggedIn() ? '/carts/mine/verify-referral'  : '/guest-carts/:cartId/verify-referral' ,
                params = customer.isLoggedIn() ? {} : {cartId: quote.getQuoteId()};
            return urlBuilder.createUrl(url, params);
        },

        /**
         * Callback when apply code success: remove unused data
         */
        onApplied: function () {
            if (this.getReferralType() === '1') {
                this.posPhoneNumber('');
                this.canResetBC(true);
            } else if (this.getReferralType() === '2') {
                this.referralCode('');
                this.canResetFF(true);
            }
        },

        /**
         * Callback when apply code success: remove unused data
         */
        onReset: function () {
            this.posPhoneNumber('');
            this.referralCode('');
            this.canResetFF(false);
            this.canResetBC(false);
        }
    };
});
