define([
        'ko',
        'jquery',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'Amasty_Rewards/js/model/resource-url-manager',
        'Magento_Checkout/js/model/error-processor',
        'Amasty_Rewards/js/model/payment/reward-messages',
        'mage/storage',
        'mage/translate',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/full-screen-loader'
    ], function (ko, $, url, quote, urlManager, errorProcessor, messageContainer,
        storage, $t, getPaymentInformationAction, totals, fullScreenLoader
    ) {
        'use strict';
        return function (points, isApplied, pointsLeftObs, rateForCurrency, noticeMessage) {
            var quoteId = quote.getQuoteId(),
                form = $('#discount-reward-form'),
                submitUrl = urlManager.getRewardsUrl(points(), quoteId);

            messageContainer.clear();
            fullScreenLoader.startLoader();
            $.ajax({
                url: url.build('cj_amrewards/ajax/validaterewardpost'),
                type: 'post',
                dataType: 'json',
                context: this,
                cache: false,
                data: form.serialize(),
                success: function (response) {
                    if (response['success']) {
                        return storage.put(
                            submitUrl,
                            {},
                            false
                        ).done(function (response) {
                            var deferred,
                                amount,
                                pointsUsed = 0;

                            if (response) {
                                pointsUsed = response[1];
                                noticeMessage($t(response[0]));
                                $('[data-amrewards-js="notice-message"]').show();
                                setTimeout(function () {
                                    $('[data-amrewards-js="notice-message"]').hide('blind', {}, 500);
                                }, 5000);

                                deferred = $.Deferred();

                                if (pointsUsed > 0) {
                                    isApplied(true);
                                    totals.isLoading(true);
                                    getPaymentInformationAction(deferred);

                                    $.when(deferred).done(function () {
                                        points((pointsUsed).toFixed(2));
                                        pointsLeftObs((pointsLeftObs() - points()).toFixed(2));
                                        amount = points() / rateForCurrency;
                                        $('#amreward_amount').val(amount).change();

                                        fullScreenLoader.stopLoader();
                                        totals.isLoading(false);
                                    });
                                }

                                fullScreenLoader.stopLoader();
                            }
                        }).fail(function (response) {
                            fullScreenLoader.stopLoader();
                            totals.isLoading(false);
                            errorProcessor.process(response, messageContainer);
                        });
                    }
                },
            });
        }
    }
);
