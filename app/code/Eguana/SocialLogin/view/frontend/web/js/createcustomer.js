/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 19/6/20
 * Time: 2:51 PM
 */
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    function main(config) {
        var AjaxUrl = config.url;
        var url = config.customerurl;
        var registrationUrl = config.registrationurl;
        $(document).on('click', '#not-a-member', function(ev){
            $("form[id=eguana-form-validate] input[id=email]").prop('disabled', true);
            $("form[id=eguana-form-validate] input[id=password]").prop('disabled', true);
            $("form[id=eguana-form-validate] input[id=member]").not(this).prop('checked', false);
        });
        $(document).on('click', '#member', function(ev){
            $("form[id=eguana-form-validate] input[id=email]").prop('disabled', false);
            $("form[id=eguana-form-validate] input[id=password]").prop('disabled', false);
            $("form[id=eguana-form-validate] input[id=not-a-member]").not(this).prop('checked', false);
        });
        $(document).on('click', 'button[id="cancel"]', function(ev){
            ev.preventDefault();
            window.location.href= url;
        });
        $(document).on('submit', 'form[id="eguana-form-validate"]', function(ev){
            ev.preventDefault();
            var radioValue = $("form[id='eguana-form-validate'] input[name='member']:checked").val();
            if (radioValue == "notamember") {
                window.location.href = registrationUrl;
            } else {
                var form_data = $(this).serialize();
                $.ajax({
                    showLoader: true,
                    url: AjaxUrl,
                    data: form_data,
                    type: "POST"
                }).done(function (data) {
                    window.location.href= url;
                });
            }
        });
    };
    return main;
});

