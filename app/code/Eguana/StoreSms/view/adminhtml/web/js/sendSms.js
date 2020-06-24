/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: Shahroz
 * Date: 11/15/19
 * Time: 1:09 PM
 */
define([
    'jquery',
    'prototype'
], function ($) {
    'use strict';
    function main(config)
    {
        /**
         * this function is used to send test sms
         */
        $(document).ready(function () {
            let telephoneId = $('#eguanasms_test_templates_eguana_test_numbers');
            let testMessageId = $('#eguanasms_test_templates_eguana_test_message');
            let telephone = telephoneId.val();
            let testMessage = testMessageId.val();
            if (telephone === '' || testMessage === '') {
                telephoneId.on('keyup', function () {
                    telephone = telephoneId.val();

                });
                testMessageId.on('keyup', function () {
                    testMessage = testMessageId.val();
                });
            }

            $('#send-sms').click(function () {
                let params = {'number':telephone,'message':testMessage};
                new Ajax.Request(config.smsAjaxUrl, {
                    parameters:     params,
                    loaderArea:     true,
                    asynchronous:   true,
                    onSuccess: function (response) {
                        window.location.href = response;
                    }
                });
            });
        });
    }
    return main;
});