define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Checkout/js/action/get-totals',
    'underscore',
    'mage/validation',
    'mage/translate'
], function ($, alert, getTotalsAction, _) {
    'use strict';

    return function (config) {
        var button = config.increase + ',' + config.decrease;

        /**
         * Update Item qty
         */
        function updateQty() {
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

                    /* Totals summary reloading */
                    var deferred = $.Deferred();
                    getTotalsAction([], deferred);

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
        }

        /**
         * Click button increase or decrease item qty
         */
        $(document).on('click',  button, function (e) {
            updateQty();
        });

        /**
         * Edit item qty in input field
         */
        $(document).keyup(function(event) {
            if ($(".qty").is(":focus") && event.keyCode == 13) {
                updateQty();
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        });
    };
});
