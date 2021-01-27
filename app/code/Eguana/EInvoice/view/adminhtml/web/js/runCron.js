/**
 * @author Eguana Team
 * @copyright Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2021/01/22
 * Time: 11:13 AM
 */
define([
    'jquery',
    'prototype'
], function ($) {
    'use strict';

    function main(config)
    {
        var collectSpan = $("#collect_span");

        $("#run_einvoice_cron").click(function () {
            var params = {};

            $.ajax({
                url: config.statusAjaxUrl,
                showLoader: true,
                dataType: "json",
                type: "GET",
                data: params
            }).done(function (response) {
                var resultText = "";

                if (response.status > 200) {
                    resultText = response.statusText;
                } else {
                    resultText = response.success === true ? "Success" : "Failed";

                    if (response.message) {
                        alert(response.message);
                    }
                    collectSpan.find(".collected").show();
                }

                $("#collect_message_span").text(resultText);
            }).fail(function (xhr, status, error) {
                console.log(xhr);
                console.log(status);
                console.log(error);
            });
        });
    }
    return main;
});
