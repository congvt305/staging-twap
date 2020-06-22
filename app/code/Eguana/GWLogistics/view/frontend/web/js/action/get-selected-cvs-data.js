define([
    'jquery',
    'mage/storage',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function ($, storage, customerData, $t) {
    'use strict';

    var action = function (quoteId) {
        // console.log('action quoteId: ', quoteId);
        var carId = {'quoteId' : quoteId};
        return storage.post(
            'eguana_gwlogistics/ajax/getSelectedCvsData',
            // JSON.stringify(quoteId),
            JSON.stringify(carId),
            false
        ).done(function (response) {
            if (response.errors) {
                customerData.set('messages', {
                    messages: [{
                        type: 'error',
                        text: response.message
                    }]
                });
            } else {
                return response;
            }
        }).fail(function () {
            customerData.set('messages', {
                messages: [{
                    type: 'error',
                    text: $t('Could not save the bank information. Please try again later')
                }]
            });
        });
    };

    return action;

});
