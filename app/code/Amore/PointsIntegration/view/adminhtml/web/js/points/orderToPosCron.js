/**
 * @author Eguana Commerce USER
 * @copyright Copyright (c) 2020 Eguana Commerce
 */
define([
    'jquery',
    'prototype'
], function ($) {
    'use strict';
    function main(config)
    {
        let  collectSpan = $('#collect-span-pos-order');
        $('#order_to_pos').click(function () {
            console.log(config);
            let params = {"websiteId":config.websiteId};
            $.ajax({
                url:config.orderToPosAjaxUrl,
                showLoader:true,
                dataType:'json',
                type:'GET',
                data:params
            }).done(function (response) {
                console.log("DONE");
                console.log(response);
                let resultText = '';
                if (response.status > 200) {
                    resultText = response.statusText;
                } else {
                    resultText = response.success == true ?'Success':'Failed';
                    if (response.message) {
                        alert(response.message);
                    }
                    collectSpan.find('.collected').show();
                }
                $('#collect-message-span-pos-order').text(resultText);
            }).fail(function (xhr, status, error) {
                console.log(xhr);
                console.log(status);
                console.log(error);
            });
        });
    }
    return main;
});
