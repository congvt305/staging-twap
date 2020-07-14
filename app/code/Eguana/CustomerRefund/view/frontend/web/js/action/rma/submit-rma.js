/*
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 4/26/20, 8:37 AM
 *
 */

define([
    'jquery',
    'mage/storage',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function ($, storage, customerData, $t) {
    'use strict';

    var action = function (rmaData, submitUrl) {
        return  $.ajax({
            url: submitUrl,
            type: 'POST',
            data: rmaData,
            global: false,
        }).done(function (response) {
            if (response.errors) {
                customerData.set('messages', {
                    messages: [{
                        type: 'error',
                        text: response.message
                    }]
                });
            } else {
                return true;
            }
        }).fail(function () {
            customerData.set('messages', {
                messages: [{
                    type: 'error',
                    text: $t('Could not submit your return request. Please try again later')
                }]
            });
        });
    };

    return action;

});
