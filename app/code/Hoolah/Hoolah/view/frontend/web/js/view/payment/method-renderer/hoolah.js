/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'mage/storage',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote'
    ],
    function (storage, Component, url, errorProcessor, fullScreenLoader, quote) {
        'use strict';
        
        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'Hoolah_Hoolah/payment/form',
            },
            
            //hoolah_validated : false,
            
            getCode: function() {
                return 'hoolah';
            },

            getPlaceOrderDeferredObject: function()
            {
                var serviceUrl, payload;
                var self = this;
                
                payload = {
                    guestEmail: quote.guestEmail,
                    cartId: quote.getQuoteId(),
                    billingAddress: quote.billingAddress(),
                    shippingAddress: quote.shippingAddress(),
                    customerData: window.checkoutConfig.customerData
                };
                
                serviceUrl = url.build('hoolah/gateway/validateorder');
                
                fullScreenLoader.startLoader();
                
                var result = storage.post(
                    serviceUrl, JSON.stringify(payload)
                ).fail(
                    function (response) {
                        errorProcessor.process(response, self.messageContainer);
                    }
                ).success(
                    function (response) {
                        self.response = response;
                    }
                ).always(
                    function () {
                        fullScreenLoader.stopLoader();
                    }
                );
                
                return result;
            },
            
            afterPlaceOrder: function () {
                if (this.response.redirect)
                {
                    fullScreenLoader.startLoader();
                    window.location.replace(this.response.redirect);
                }
                else
                    this.messageContainer.addErrorMessage({
                        message: 'Something gone wrong with payment. Please contact us, or make order with another payment gateway.'
                    });
            },
            
            getPaymentAcceptanceMarkSrc: function () {
                return window.checkoutConfig.payment.hoolah.paymentAcceptanceMarkSrc;
            },

            isDisabled: function () {
                return window.checkoutConfig.payment.hoolah.disabled;
            },
            
            getPaymentDescription: function () {
                return window.checkoutConfig.payment.hoolah.paymentDescription;
            },
            
            getProcessConsumerLink: function() {
                let billingAddressObject = quote.billingAddress(),
                    how_paylater_works_link = 'https://www.shopback.sg/how-paylater-works';

                if (billingAddressObject && billingAddressObject['countryId'])
                {
                    let country = billingAddressObject['countryId'].split(':', 2);

                    switch (country[0])
                    {
                        case 'SG': how_paylater_works_link = 'https://www.shopback.sg/how-paylater-works'; break;
                        case 'MY': how_paylater_works_link = 'https://www.shopback.my/how-paylater-works'; break;
                        case 'HK': break;
                    }
                }

                return how_paylater_works_link;
            }
        });
    }
);