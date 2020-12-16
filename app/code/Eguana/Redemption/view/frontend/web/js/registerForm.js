/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 26/11/20
 * Time: 4:10 PM
 */
define([
    'jquery',
    'domReady!',
    "mage/translate",
], function ($) {
    /**
     * @param config.countersaveurl
     * @param config.counterTime
     * @param config.resendUrl
     */
    function main(config) {
        let countersaveurl = config.countersaveurl;
        let minutes = config.counterTime;
        let resendUrl = config.resendUrl;
        let entityId = "";
        let infoAndErrorMessageDiv = $("#error-and-success-message");
        $(document).find('span[id="recaptcha-response"]').hide();
        $('#info-message').hide();
        $("#counter-form-submit").click(function(e){
            e.preventDefault();
            if ($('form[id="counter-form"]').valid()) {
                let formkey = "<input name='form_key' value=" + window.FORM_KEY + " title='form_key' type='hidden'>";
                $('form[id="counter-form"]').append(formkey);
                let form_data = $('form[id="counter-form"]').serialize();
                $.ajax({
                    url: countersaveurl,
                    type: 'POST',
                    data: form_data,
                    beforeSend: function () {
                        jQuery('body').loader('show');
                    },
                    success: function (data) {
                        jQuery('body').loader('hide');
                        if (data['duplicate']) {
                            infoAndErrorMessageDiv.removeClass("message success");
                            infoAndErrorMessageDiv.addClass("message info");
                            infoAndErrorMessageDiv.find('span').remove()
                            infoAndErrorMessageDiv.append("<span>" + data['message'] + "</span>");
                            $('#counter-form-submit').show();
                            $('#counter-form-submit').removeAttr('disabled');
                        }
                        if (data['success']) {
                            $('#name, #phone, #email, #line').prop('readonly', true);
                            $('#counter').prop('disabled', true);
                            entityId = data['entity_id'];
                            infoAndErrorMessageDiv.removeClass("message info");
                            infoAndErrorMessageDiv.addClass("message success");
                            infoAndErrorMessageDiv.find('span').remove()
                            infoAndErrorMessageDiv.append("<span>" + data['message'] + "</span>");
                            $('#counter-form-submit').prop('disabled', true);
                            $('#resend-button').prop('disabled', true);
                            $('#info-message').show();
                            startTimer();
                        }
                        window.scrollTo(500, 0);
                    }
                });
            };
        });
        $("#resend-button").click(function(e) {
            var response = $(document).find('textarea[class="g-recaptcha-response"]').val();
            if (response == '') {
                e.preventDefault();
                $('html,body').animate({
                        scrollTop: $("#counter").offset().top},
                    'slow');
                $(document).find('span[id="recaptcha-response"]').show();
                $(document).find('span[id="recaptcha-response"]').css('color','red');
            } else {
                $(document).find('span[id="recaptcha-response"]').hide();
                $.ajax({
                    url: resendUrl,
                    type: 'POST',
                    data: {counter_id: entityId, form_key: window.FORM_KEY},
                    beforeSend: function () {
                        jQuery('body').loader('show');
                    },
                    success: function (data) {
                        startTimer();
                        jQuery('body').loader('hide');
                        infoAndErrorMessageDiv.find('span').remove()
                        infoAndErrorMessageDiv.append("<span>" + data['resendmessage'] + "</span>");
                        $('#resend-button').prop('disabled', true);
                        $('#info-message').show();
                        $("#countTime").show();
                        window.scrollTo(500, 0);
                    }
                });
            }
        });
        function startTimer() {
            let countdown = minutes * 60 * 1000;
            let timerId = setInterval(function () {
                countdown -= 1000;
                let min = Math.floor(countdown / (60 * 1000));
                let sec = Math.floor((countdown - (min * 60 * 1000)) / 1000);

                if (countdown <= 0) {
                    $('#resend-button').show();
                    $('#resend-button').prop('disabled', false);
                    $("#countTime").hide();
                    $("#info-message").hide();
                    $("#counter-form-submit").hide();
                    clearInterval(timerId);
                } else {
                    $("#countTime").html(min + " : " + sec);
                }
            }, 1000);
        }
    };
    return main;
});