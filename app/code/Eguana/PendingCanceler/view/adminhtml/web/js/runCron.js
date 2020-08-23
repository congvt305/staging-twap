/**
 * User: Brian
 * Date: 2020-07-03
 */
define([
    'jquery',
    'prototype'
], function ($) {
    'use strict';
    function main(config)
    {
        let  collectSpan = $('#collect_span');
        $('#run_cron').click(function () {
            console.log('click');
            var params = {};
            $.ajax({
                url:config.statusAjaxUrl,
                showLoader:true,
                dataType:'json',
                type:'GET',
                data:params
            }).done(function (response) {
                console.log("DONE");
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
                $('#collect_message_span').text(resultText);
            }).fail(function (xhr, status, error) {
                console.log(xhr);
                console.log(status);
                console.log(error);
            });
        });
    }
    return main;
});
