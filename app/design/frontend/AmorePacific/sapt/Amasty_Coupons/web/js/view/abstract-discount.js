/* Coupon codes field view */
define([
    'jquery',
    'ko',
    'underscore',
    'Magento_SalesRule/js/view/payment/discount',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/model/messageList',
    'Amasty_Coupons/js/action/apply-coupon-codes',
    'Amasty_Coupons/js/model/coupon',
    'Amasty_Coupons/js/model/abstract-apply-response-processor',
    'Magento_Customer/js/model/customer'
], function ($, ko, _, Component, quote, messageList, setCouponCodesAction, couponModel, responseProcessor, customer) {
    'use strict';

    var couponList = window.checkoutConfig.cj_couponcustomer.coupon_list;

    return Component.extend({
        defaults: {
            inputCode: '',
            isLoading: false,
            template: 'Amasty_Coupons/cart/discount',
            messageTimeout: 2000,
            cancelMessage: 'Coupon code was removed',
            errorMessage: 'Coupon code is not valid',
            successMessage: 'Coupon was successfully applied',
            selectors: {
                form: '#discount-form',
                removeAppliedCoupon: '[data-amcoupons-js="remove-applied-coupon"]',
                messageElement: '[data-amcoupons-js="message"]'
            },
            classes: {
                successMessage: 'message success',
                warningMessage: 'message warning'
            }
        },

        /**
         * @property {CouponApplyReportProcessor} responseProcessor
         */
        responseProcessor: responseProcessor,

        couponsArray: couponModel.couponsArray,

        initialize: function () {
            this._super();

            if (this.couponCode()) {
                this.couponsArray(couponModel.renderCoupons(this.couponCode()));
            }

            _.bindAll(this, 'onCouponRemove', 'onCouponAdd', 'removeSelected', 'apply');

            return this;
        },

        initObservable: function () {
            this._super();

            this.observe(['inputCode', 'isLoading', 'selectedCoupon']);

            this.selectedCoupon.subscribe(function (coupon) {
                if (coupon && !this.couponsArray().includes(coupon)) {
                    this.applyWallet(coupon);
                }
            }, this);

            return this;
        },

        /**
         * @param {string} coupon
         * @returns {void}
         */
        removeSelected: function (coupon) {
            var codes = _.without(this.couponsArray(), coupon);

            this.isLoading(true);

            setCouponCodesAction(codes, this.responseProcessor).always(function () {
                this.isLoading(false);
            }.bind(this));
        },

        /**
         * Coupon code application procedure
         * @returns {void}
         */
        apply: function () {
            var codes = [];

            if (this.validate()) {
                this.isLoading(true);
                codes = codes.concat(this.couponsArray())
                    .concat(couponModel.renderCoupons(this.inputCode()));

                setCouponCodesAction(codes, this.responseProcessor)
                    .done(function () {
                        this.handleErrorMessages();
                        this.inputCode(this.responseProcessor.errorCoupons.join(', '));
                        $('.totals.discount .title').removeClass('negative');
                    }.bind(this))
                    .always(function () {
                        this.isLoading(false);
                    }.bind(this));
            }
        },

        /**
         * @returns {void}
         */
        handleErrorMessages: function () {
            var messages = this.getChild('errors');

            if (messages) {
                messages.messageContainer.clear();

                _.each(responseProcessor.errorCoupons, function (code) {
                    messages.messageContainer.errorMessages.push(code + ' ' + this.errorMessage);
                }, this);
            }
        },

        /**
         * @param {HTMLElement} elem
         * @returns {void}
         */
        onCouponRemove: function (elem) {
            var $elem;

            if (elem.nodeName !== 'DIV') {
                return;
            }

            $elem = $(elem);

            $elem.addClass(this.classes.warningMessage);
            $elem.find(this.selectors.messageElement).text(this.cancelMessage);
            $elem.find(this.selectors.removeAppliedCoupon).remove();

            setTimeout(function () {
                $elem.fadeOut(400, function () {
                    $elem.remove();
                });
            }, this.messageTimeout);
        },

        /**
         * @param {HTMLElement} elem
         * @returns {void}
         */
        onCouponAdd: function (elem) {
            var $elem,
                $message;

            if (elem.nodeType !== 1 || elem.nodeName !== 'DIV') {
                return;
            }

            $elem = $(elem);
            $message = $elem.find(this.selectors.messageElement);

            $elem.addClass(this.classes.successMessage);
            $message.text(this.successMessage);

            setTimeout(function () {
                $elem.removeClass(this.classes.successMessage);
                $message.text('');
            }.bind(this), this.messageTimeout);
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            return $(this.selectors.form).validation() && $(this.selectors.form).validation('isValid');
        },

        /**
         * Check if customer is logged in
         *
         * @return {boolean}
         */
        isLoggedIn: function () {
            return customer.isLoggedIn();
        },

        hasCouponWallet: function () {
            return couponList && couponList.length > 0;
        },

        getCouponOptions: function () {
            if (!this.hasCouponWallet()) {
                return [];
            }
            const options = couponList.map(coupon => ({
                    name: coupon.name + '-' + coupon.code,
                    value: coupon.code
                }
            ));
            options.unshift({
                name: 'Please select a coupon',
                value: ''
            });
            return options;
        },

        /**
         * Coupon code application procedure
         * @returns {void}
         */
        applyWallet: function (coupon) {
            var codes = [];

            this.isLoading(true);
            codes = codes.concat(this.couponsArray())
                .concat(coupon);


            setCouponCodesAction(codes, this.responseProcessor)
                .done(function () {
                    this.handleErrorMessages();
                    $('.totals.discount .title').removeClass('negative');
                }.bind(this))
                .always(function () {
                    this.isLoading(false);
                }.bind(this));
        },

        isEnableMultiCoupons: function() {
            return parseInt(window.checkoutConfig.enable_multi_coupons);
        }
    });
});
