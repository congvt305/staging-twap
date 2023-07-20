define([
    'ko',
    'mage/translate',
    'jquery',
    'underscore',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Amasty_Rewards/js/action/add-reward',
    'Amasty_Rewards/js/action/cancel-reward',
    'Amasty_Rewards/vendor/tooltipster/js/tooltipster.min',
    'Magento_Catalog/js/price-utils',
    'Magento_Ui/js/modal/modal'
], function (ko, $t, $, _, Component, quote, setRewardPointAction, cancelRewardPointAction, tooltipster, priceUtils, modal) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_Rewards/checkout/payment/rewards',
            isApplied: false,
            pointsUsed: 0,
            pointsLeft: 0,
            noticeMessage: '',
            minimumPointsValue: 0,
            disableElem: ko.observable(true),
            isRewardsTooltipEnabled: false,
            rewardsTooltipContent: '',
            listOption: ko.observable({}),
            canUseRewardPoint: false,
            currentOption: 0,
            disableRadioButton: ko.observable(false),
            selectors: {
                tooltipElement: '[data-amrewards-js="tooltip"]',
            },
        },


        initObservable: function () {
            this._super();
            this.observe(['pointsUsed', 'pointsLeft', 'isApplied', 'noticeMessage', 'disableElem']);

            return this;
        },

        /**
         * @return {exports}
         */
        initialize: function () {
            this._super();
            this.isApplied(false);
            var self = this;
            if (this.pointsUsed() > 0) {
                this.disableRadioButton(true);
                this.pointsUsed(parseInt(this.pointsUsed()));//force it to int
                this.isApplied(true);
            }

            if (_.isUndefined(Number.parseFloat)) {
                Number.parseFloat = parseFloat;
            }

            if (this.getMinimumPointsValue() > this.pointsLeft() + Number.parseFloat(this.pointsUsed())) {
                this.disableElem(true);
            }

            this.pointsUsed.subscribe(function (value) {
                self.disableElem(false);
            });
            this.initTooltip();

            return this;
        },

        /**
         * @return {*|Boolean}
         */
        isDisplayed: function () {
            return this.customerId && this.canUseRewardPoint;
        },

        /**
         *
         * @returns {*}
         */
        getListOptionReward: function () {
            return this.listOption;
        },
        /**
         * Coupon code application procedure
         */
        apply: function () {
            var self = this;
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Confirm to use point?',
                buttons: [{
                    text: $t('I confirm '),
                    class: '',
                    click: function (data, event) {
                        if (event) {
                            event.preventDefault();
                        }
                        if (self.validate()) {
                            setRewardPointAction(self.pointsUsed, self.isApplied, self.pointsLeft, self.rateForCurrency, self.noticeMessage, self.disableRadioButton);
                        } else {
                            self.noticeMessage($t('You don’t have enough point to apply'));
                            $('[data-amrewards-js="notice-message"]').show();
                            self.disableRadioButton(false);
                        }
                        $('#popup-modal-apply').modal('closeModal');
                    }
                }]
            };
            var popup = modal(options, $('#popup-modal-apply'));
            $('#popup-modal-apply').modal('openModal');
        },

        /**
         * @param {*} price
         * @return {*|String}
         */
        getFormattedPrice: function (price) {
            return priceUtils.formatPriceLocale(price, {
                "pattern": "RM%s",
                "precision": 0,
                "requiredPrecision": 0,
                "decimalSymbol": ".",
                "groupSymbol": ",",
                "groupLength": 2,
                "integerRequired": true
            });
        },

        /**
         * Cancel using coupon
         */
        cancel: function () {
            cancelRewardPointAction(this.isApplied);
            this.pointsLeft((Number.parseFloat(this.pointsLeft()) + Number.parseFloat(this.pointsUsed())).toFixed(0));
            this.pointsUsed(0);
            this.disableRadioButton(false);
            this.disableElem(true);
        },

        /**
         *
         * @return {*}
         */
        getRewardsCount: function () {
            return this.pointsLeft();
        },

        /**
         *
         * @return {*}
         */
        getPointsRate: function () {
            return this.pointsRate;
        },

        /**
         *
         * @return {*}
         */
        getCurrentCurrency: function () {
            return this.currentCurrencyCode;
        },

        /**
         *
         * @return {*}
         */
        getRateForCurrency: function () {
            return this.rateForCurrency;
        },

        /**
         * @return {*}
         */
        getMinimumPointsValue: function () {
            return Number.parseFloat(this.minimumPointsValue);
        },

        /**
         * @return {Boolean}
         */
        canApply: function () {
            return !(this.disableElem() || this.isApplied());
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            var form = '#discount-reward-form',
                valueValid = (this.pointsLeft() - this.pointsUsed() >= 0) && this.pointsUsed() > 0;

            return $(form).validation() && $(form).validation('isValid') && valueValid;
        },

        initTooltip: function () {
            var tooltipTrigger = this.isTouchDevice() ? 'click' : 'hover';

            if (!this.isRewardsTooltipEnabled) {
                return;
            }

            $.async(this.selectors.tooltipElement, function () {
                $(this.selectors.tooltipElement).tooltipster({
                    position: 'right',
                    contentAsHtml: true,
                    interactive: true,
                    trigger: tooltipTrigger
                });
            }.bind(this));
        },

        isTouchDevice: function () {
            return ('ontouchstart' in window)
                || (navigator.maxTouchPoints > 0)
                || (navigator.msMaxTouchPoints > 0);
        },

        removeRadioButton: function () {
            if (this.pointsUsed() > 0 && this.currentOption == this.pointsUsed()) {
                this.pointsUsed(0);
                this.disableElem(true);
            }
            this.currentOption = this.pointsUsed();
        }
    });
});
