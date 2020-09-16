define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address',
    'Magento_Checkout/js/model/shipping-rate-processor/customer-address',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'underscore',
    'mage/validation',
    'mage/translate'
], function ($, alert, quote, defaultProcessor, customerAddressProcessor, rateRegistry, _) {
    'use strict';

    return function (config) {
        var button = config.increase + ',' + config.decrease;

        $(document).on('click',  button, function (e) {

            var form = $('form#form-validate');
            if (!(form.validation() && form.validation('isValid'))) {
                return;
            }

            $.ajax({
                url: form.attr('action'),
                data: form.serialize(),
                type: 'post',
                beforeSend: function () {
                    form.trigger('processStart');
                },

                success: function (res) {
                    var parsedResponse = $.parseHTML(res),
                        result = $(parsedResponse).find("#form-validate");

                    form.replaceWith(result);

                    var rewards_result = $(parsedResponse).find(".rewards");

                    $("div.rewards").replaceWith(rewards_result);

                    /*Shipping method reloading */
                    var processors = [];

                    rateRegistry.set(quote.shippingAddress().getCacheKey(), null);

                    processors.default =  defaultProcessor;
                    processors['customer-address'] = customerAddressProcessor;

                    var type = quote.shippingAddress().getType();

                    if (processors[type]) {
                        processors[type].getRates(quote.shippingAddress());
                    } else {
                        processors.default.getRates(quote.shippingAddress());
                    }
                    $('body').trigger('processStop');
                },

                error: function () {
                    alert({
                        content: $.mage.__('Sorry, something went wrong. Please try again later.')
                    });
                },

                /**
                 * Complete.
                 */
                complete: function () {
                    form.trigger('processStop');
                }
            });
        });
    };
});
