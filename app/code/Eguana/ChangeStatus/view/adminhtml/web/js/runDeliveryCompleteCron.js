/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 19/4/21
 * Time: 5:00 PM
 */

define([
    'jquery',
    'prototype'
], function ($) {
    'use strict';
    function main(config)
    {
        let  collectSpan = $('#collect_span_delivery_complete');
        $('#delivery_complete_cron').click(function () {
            $.ajax({
                url: config.deliveryCompleteAjaxUrl,
                showLoader: true,
                dataType: 'json',
                type: 'GET',
                data: {}
            }).done(function (response) {
                let resultText = '';
                if (response.status > 200) {
                    resultText = response.statusText;
                } else {
                    resultText = response.success == true ? 'Success' : 'Failed';
                    if (response.message) {
                        alert(response.message);
                    }
                    collectSpan.find('.collected').show();
                }
                $('#collect_message_span_delivery_complete').text(resultText);
            }).fail(function (xhr, status, error) {
                $('#collect_message_span_delivery_complete').text(error);
            });
        });
    }
    return main;
});
