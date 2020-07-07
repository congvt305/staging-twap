define([
    'jquery',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'mage/storage',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'Magento_Checkout/js/model/shipping-save-processor/payload-extender',
    'mage/utils/wrapper'
], function ($,
             ko,
             quote,
             resourceUrlManager,
             storage,
             paymentService,
             methodConverter,
             errorProcessor,
             fullScreenLoader,
             selectBillingAddressAction,
             messageList,
             mage,
             payloadExtender,
             wrapper
) {
    'use strict';

    return function (shippingInformation) {
        shippingInformation.saveShippingInformation = wrapper.wrapSuper(shippingInformation.saveShippingInformation, function () {

            return this._super();

        });

        return shippingInformation;
    };
});