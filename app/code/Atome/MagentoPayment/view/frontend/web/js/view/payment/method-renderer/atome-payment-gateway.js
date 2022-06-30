/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'mage/url',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/model/messageList',
        'Magento_Customer/js/customer-data',
        'Magento_Customer/js/section-config'
    ],
    function ($, Component, quote, resourceUrlManager, storage, mageUrl, additionalValidators, globalMessageList, customerData, sectionConfig) {
        'use strict';

        const baseUrl = /\.apaylater\.net/.test(location.host)
          ? "https://gateway.apaylater.net"
          : "https://gateway.apaylater.com"; 

        function getOption(key, default_value = '') {
            if(window.atomePaymentPluginPriceDividerOptions && window.atomePaymentPluginPriceDividerOptions.hasOwnProperty(key)){
                return window.atomePaymentPluginPriceDividerOptions[key];
            }

            return default_value;
        }

        function formatPrice(priceInstallment, options){
            var thousandsSeparator = ',';

            if(options && options['precision']){
                priceInstallment = priceInstallment.toFixed(options['precision']);
            }

            if (['IDR', 'VND'].indexOf(getOption("currencyCode", "")) !== -1){
              thousandsSeparator = ".";
            }

            if(options && options['thousands_format']){
                var pieces = (priceInstallment+'').split('.');
                priceInstallment = pieces[0].replace(/\B(?=(?:\d{3})+$)/g, thousandsSeparator);
                if(pieces[1]){
                    priceInstallment += '.'
                    priceInstallment += pieces[1];

                    var zeroPaddingNum = options['precision'] - pieces[1].length;
                    if(zeroPaddingNum > 0){
                        priceInstallment += '00000'.substr(0, zeroPaddingNum);
                    }
                }
              }

              return priceInstallment;
        }

        function loadIntroRes() {
            if (document.getElementById("atome-intro-js")) {
                return;
            }
            const countryConfig = getOption('country_config');
            let introScriptUrl = baseUrl + '/plugins/intro/index.js?';

            const countryCode = typeof countryConfig['custom_country'] != 'undefined' ? countryConfig['custom_country'] : 'sg';
            if (countryCode) {
                introScriptUrl += 'country=';
                introScriptUrl += countryCode;
            }

            const languageCode = typeof countryConfig['custom_lang'] != 'undefined' ? countryConfig['custom_lang'] : 'en';
            if (languageCode) {
                introScriptUrl += '&lang=';
                introScriptUrl += languageCode;
            }

            introScriptUrl += 'source=magento2';

            const head = document.head || document.getElementsByTagName('head')[0];
            const el = document.createElement('script');
            el.type = 'text/javascript';
            el.src = introScriptUrl;
            el.id = 'atome-intro-js';
            head.appendChild(el);
        }

        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'Atome_MagentoPayment/payment/atome-payment-gateway'
            },

            getAtomeLogoUrl: function() {
                return baseUrl + '/plugins/common/assets/svg/' + getOption('atome_logo', '');
            },

            getAtomePaymentLogoUrl: function() {
                return baseUrl + '/plugins/common/assets/svg/' + getOption('atome_payment_logo', '');
            },

            getAtomeUrl: function() {
                var countryCode = getOption('country_code');
                if(countryCode && typeof countryCode == 'string'){
                    return 'https://www.atome.' + countryCode.toLowerCase();
                }

                return 'https://www.atome.sg';
            },

            isIndonesia: function() {
                return 'ID' === getOption('country_code');
            },

            calculateGrandTotal: function (observe = false) {
                const self = this;
                storage.get(
                    resourceUrlManager.getUrlForCartTotals(quote), false
                ).done(function (response) {
                    var format = window.checkoutConfig.priceFormat.pattern;
                    var precision = window.checkoutConfig.priceFormat.precision;
                    var amount = response.grand_total;
                    amount += response.tax_amount;

                    var $atomeGateway = $('#atome-payment-method');
                    if ('TWD' === getOption("currencyCode", "") && Math.floor(amount) !== amount) {
                        $atomeGateway.find('.atome-checkout-eligible').hide();
                        $atomeGateway.find('.atome-checkout-ineligible.atome-amount-not-integer-error').show();
                        return;
                    }
                    
                    var installmentFee = amount / 3;
                    var installmentFeeLast = amount - installmentFee.toFixed(precision) * 2;
                    var minimumSpend = getOption('minimum_spend', 0);

                    var formatOptions = {'precision': precision, 'thousands_format': true};

                    if(['IDR', 'VND'].indexOf(getOption("currencyCode", "")) !== -1){
                        formatOptions.precision = 0;
                        amount = Math.ceil(amount);
                        installmentFee = Math.floor(amount / 3);
                        var surplus = amount - installmentFee * 3;

                        var installmentFees = [];
                        while (surplus) {
                            installmentFees[--surplus] = installmentFee + 1;
                        }

                        $atomeGateway.find(".atome_total_amount").text(format.replace(/%s/g, formatPrice(amount, formatOptions)));
                        $atomeGateway.find(".atome_instalments_amount").each(function(i){
                            $(this).text(format.replace(/%s/g, formatPrice(installmentFees[i] || installmentFee, formatOptions)));
                        });
                        $atomeGateway.find(".atome_instalments_amount_last").text(format.replace(/%s/g, formatPrice(installmentFee, formatOptions)));

                    }else{
                        $atomeGateway.find(".atome_total_amount").text(format.replace(/%s/g, amount.toFixed(precision)));
                        $atomeGateway.find(".atome_instalments_amount").text(format.replace(/%s/g, installmentFee.toFixed(precision)));
                        $atomeGateway.find(".atome_instalments_amount_last").text(format.replace(/%s/g, installmentFeeLast.toFixed(precision)));
                    }


                    if (amount >= minimumSpend) {
                        $atomeGateway.find('.atome-checkout-eligible').show();
                        $atomeGateway.find('.atome-checkout-ineligible').hide();
                    } else {
                        $atomeGateway.find('.atome-checkout-eligible').hide();
                        var $atoemMinimumSpendError = $atomeGateway.find('#atome_minimum_spend_error');
                        var formatedPrice = formatPrice(minimumSpend, formatOptions);
                        $atoemMinimumSpendError.text($atoemMinimumSpendError.text().replace(RegExp("\\[\\[minimum_spend\\]\\]", "g"), format.replace(/%s/g, formatedPrice)));  
                        
                        $atomeGateway.find('.atome-checkout-ineligible.atome-minimum-spend-error').show();
                    }
                }).fail(function (response) {
                    throw new Error(response);
                }).always(function (response) {
                    !observe && self.observeTotal();
                });

                loadIntroRes();

                return '(calculating ...)';
            },

            observeTotal: function () {
                const totalPriceContainer = document.querySelector('tr.grand.totals span.price');
                if (!totalPriceContainer) {
                    return;
                }
                const self = this;
                const observer = new MutationObserver(function (mutationsList) {
                  if (mutationsList.length) {
                    self.calculateGrandTotal(true);
                  }
                });
                observer.observe(totalPriceContainer, {
                  subtree: true,
                  childList: true,
                  characterData: true,
                });
            },

            startAtomePayment: function () {
                if (additionalValidators.validate()) {
                    
                    // if (!quote.billingAddress() || !quote.billingAddress().canUseForBilling()) {
                    //     alert('billingAddress is required!');
                    //     return;
                    // };

                    if (getOption("order_created_when") === "before_paying") {
                        this.placeOrder();
                        return;
                    }

                    var atome = window.checkoutConfig.payment.atome; // from CheckoutConfigProvider
                    var url = mageUrl.build("atome/payment/prepare");
                    var formData = $("#co-shipping-form").serialize();
                    formData += '&email=' + encodeURIComponent($('#customer-email').val());

                    $('body').trigger('processStart');
                    $.ajax({
                        url: url,
                        method: 'post',
                        data: {formData: formData, email: $('#customer-email').val(), billingAddress: JSON.stringify(quote.billingAddress())},
                    }).done(function (data) {
                        if (data.atomePaymentUrl) {
                            window.location.href = data.atomePaymentUrl;
                        } else if (data.error && data.message) {
                            globalMessageList.addErrorMessage({'message': data.message});
                        } else {
                            globalMessageList.addErrorMessage({'message': data.message || 'unknown error'});
                        }
                    }).fail(function () {
                        window.location.reload();
                    }).always(function () {
                        customerData.invalidate(['cart']);
                        $('body').trigger('processStop');
                    });
                }
            },

            //support Aheadworks_OneStepCheckout
            placeOrder: function (data, event) {
                var self = this;

                if (getOption("order_created_when") === "before_paying") {
                    this.isPlaceOrderActionAllowed(false);

                    $("body").trigger("processStart");
                    this.getPlaceOrderDeferredObject()
                        .fail(function () {
                            self.isPlaceOrderActionAllowed(true);
                        })
                        .done(function () {
                            self.afterPlaceOrder();

                            if (self.redirectAfterPlaceOrder) {
                                redirectOnSuccessAction.execute();
                            }
                        })
                        .always(function () {
                            customerData.invalidate(['cart']);
                            $('body').trigger('processStop');
                        });

                    return true;
                } else if (data.methodRendererComponent) {
                    data.methodRendererComponent.startAtomePayment();
                }
            },
            afterPlaceOrder: function () {
                if (getOption("order_created_when") === "before_paying") {
                    $("body").trigger("processStart");
                    window.location.href = mageUrl.build("atome/payment/prepare");
                }
            }
        });
    }
);
