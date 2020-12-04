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
        let  collectSpan = $('#collect_span');
        $('#run_cron').click(function () {
            var params = {};
            $.ajax({
                url:config.statusAjaxUrl,
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
                $('#collect_message_span').text(resultText);
            });
        });
    }
    return main;
});