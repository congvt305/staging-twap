/**
 * User: Brian
 * Date: 2020-09-11
 */
define([
    'jquery',
    'prototype'
], function ($) {
    'use strict';
    function main(config)
    {
        let  collectSpan = $('#collect_span_connection');
        $('#test_connection').click(function () {
            console.log('click');
            var params = {};
            $.ajax({
                url:config.connectionAjaxUrl,
                showLoader:true,
                dataType:'json',
                type:'GET',
                data:params
            }).done(function (response) {
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
                $('#collect_message').text(resultText);
            }).fail(function (xhr, status, error) {
                console.log(xhr);
                console.log(status);
                console.log(error);
            });
        });
    }
    return main;
});
