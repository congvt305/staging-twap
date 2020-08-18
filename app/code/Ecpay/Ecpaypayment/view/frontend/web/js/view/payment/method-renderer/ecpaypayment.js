define(
    [
        'jquery',
        'ko',
        'mage/translate',
        'mage/url',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'domReady!'
    ],
    function ($, ko, $t, url, Component, placeOrderAction, additionalValidators, quote) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Ecpay_Ecpaypayment/payment/payment', // path to template
                paymentMethod: '',
                invoiceChecked: ko.observable('greenworld-invoice')
            },

            /** @inheritdoc */
            initObservable: function () {

                this._super()
                    .observe([
                        'paymentMethod'
                    ]);
                return this;
            },

            /**
             * @return {String}
             */
            getCode: function () {
                return 'ecpay_ecpaypayment';
            },

            /**
             * @returns {Object}
             */
            getData: function () {
                var data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'ecpay_choosen_payment': this.paymentMethod(),
                        'ecpay_einvoice_type': $("input:radio[name=ecpay_einvoice_type]:checked").val(),
                        'ecpay_einvoice_title': $("input:text[name=invoice_title]").val(),
                        'ecpay_einvoice_tax_id_number': $("input:text[name=tax_id_number]").val(),
                        'ecpay_einvoice_cellphone_barcode': $("input:text[name=cellphone_barcode]").val(),
                    }
                };
                return data;
            },

            /**
             * @return {*}
             */
            isPaymentReady: function () {
                return this.paymentReady();
            },

            isPaymentAvailable: function () {
                return window.checkoutConfig.payment.ecpay_ecpaypayment.isPaymentAvailable;
            },

            getPaymentMethodMsg: function () {
                if (this.isPaymentAvailable() === false) {
                    var msg = window.checkoutConfig.payment.ecpay_ecpaypayment.ecpayPaymentMethods;
                    return '※ ' + msg[0];
                }
                return '';
            },

            /**
             * Get list of payment methods
             * @return {Object}
             */
            getPaymentMethods: function () {
                var self = this;
                return _.map(window.checkoutConfig.payment.ecpay_ecpaypayment.ecpayPaymentMethods, function (value, key) {
                    var paymentText = self.getPaymentMethodText(value);
                    return {
                        'value': value,
                        'payment_method': paymentText
                    }
                });
            },

            /**
             * Get list of payment methods
             * @return String
             */
            getPaymentMethodText: function (value) {
                switch (value) {
                    case 'credit':
                        return $t('Credit');
                    case 'credit_3':
                        return $t('Credit(3 Installments)');
                    case 'credit_6':
                        return $t('Credit(6 Installments)');
                    case 'credit_12':
                        return $t('Credit(12 Installments)');
                    case 'credit_18':
                        return $t('Credit(18 Installments)');
                    case 'credit_24':
                        return $t('Credit(24 Installments)');
                    case 'webatm':
                        return $t('WEB-ATM');
                    case 'atm':
                        return $t('ATM');
                    case 'cvs':
                        return $t('cvs');
                    case 'barcode':
                        return $t('Barcode');
                    default:
                        return $t(value);
                }
            },

            /** Redirect to AIO */
            checkoutToEcpay: function () {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                if (additionalValidators.validate()) {
                    self.isPlaceOrderActionAllowed(false);
                    self.getPlaceOrderDeferredObject()
                        .fail(
                            function () {
                                self.isPlaceOrderActionAllowed(true);
                            }
                        )
                        .done(
                            function () {
                                $.mage.redirect(url.build('ecpay_ecpaypayment/payment/redirect'));
                            }
                        );
                    return false;
                }
            },

            getPlaceOrderDeferredObject: function () {
                return $.when(
                    placeOrderAction(this.getData(), this.messageContainer)
                );
            }
        });
    }
);