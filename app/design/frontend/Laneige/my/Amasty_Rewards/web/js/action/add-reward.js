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
        'Magento_Checkout/js/model/full-screen-loader',

    ], function (ko, $, url, quote, urlManager, errorProcessor, messageContainer,
        storage, $t, getPaymentInformationAction, totals, fullScreenLoader
    ) {
        'use strict';
        return function (points, isApplied, pointsLeftObs, rateForCurrency, noticeMessage, disableButton) {
            var quoteId = quote.getQuoteId(),
                submitUrl = urlManager.getRewardsUrl(points(), quoteId),
                form = $('#discount-reward-form');
            messageContainer.clear();
            fullScreenLoader.startLoader();
            $.ajax({
                url: url.build('cj_amrewards/ajax/rewardpost'),
                type: 'post',
                dataType: 'json',
                context: this,
                cache: false,
                data: form.serialize(),
                beforeSend: function () {
                    $('body').loader('show');
                },
                success: function (response) {
                    if (response['success']) {
                        storage.put(
                            submitUrl,
                            {},
                            false
                        ).done(function (response) {
                            var deferred,
                                pointsUsed = 0;

                            if (response) {
                                pointsUsed = response[1];
                                messageContainer.addSuccessMessage({'message': response[0]});

                                deferred = $.Deferred();

                                if (pointsUsed > 0) {
                                    isApplied(true);
                                    totals.isLoading(true);
                                    getPaymentInformationAction(deferred);

                                    $.when(deferred).done(function () {
                                        points((parseInt(pointsUsed)));
                                        pointsLeftObs((pointsLeftObs() - points()).toFixed(0));
                                        disableButton(true);
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
                    } else {
                        fullScreenLoader.stopLoader();
                        totals.isLoading(false);
                        messageContainer.addErrorMessage({'message': response['message']});
                    }
                },
                /** @inheritdoc */
                complete: function () {
                    $('body').trigger('processStop');
                }
            });
        };
    }
);
