/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 24/2/21
 * Time: 6:10 PM
 */

define([
    'jquery',
    'mage/translate',
    'domReady!'
], function ($) {
    /**
     * @param config.reservationUrl
     * @param config.counterTime
     * @param config.resendUrl
     */
    function main(config) {
        let reservationUrl = config.reservationUrl;
        let minutes = config.counterTime;
        let resendUrl = config.resendUrl;
        let reserveId = "";
        let successErrorDiv = $("#error-and-success-message");
        $("#reservation-form").submit(function(e) {
            e.preventDefault();
            if ($(this).valid()) {
                let formkey = "<input name='form_key' value=" + window.FORM_KEY + " title='form_key' type='hidden'>";
                $(this).append(formkey);
                $.ajax({
                    url: reservationUrl,
                    type: 'POST',
                    data: $(this).serialize(),
                    beforeSend: function () {
                        jQuery('body').loader('show');
                    },
                    success: function (data) {
                        jQuery('body').loader('hide');
                        if (data['success']) {
                            reserveId = data['reserve_id'];
                            $('#name, #email, #phone, #line_id').prop('readonly', true);
                            $('#reserved_id, #date, #time_slot, #reservation-btn')
                                .prop('disabled', true);
                            successErrorDiv.removeClass("message info")
                                .addClass("message success")
                                .html("<span>" + data['message'] + "</span>");
                            $('#info-message').show();
                            startTimer();
                            $('#reservation-success-img, #reservation-btn').show();
                            $('#event-thumbnail-img, #reservation-resend-btn').hide();
                        } else {
                            successErrorDiv.removeClass("message success")
                                .addClass("message info")
                                .html("<span>" + data['message'] + "</span>");
                            $('#reservation-btn').show().removeAttr('disabled');
                            $('#reservation-success-img').hide();
                            $('#event-thumbnail-img').show();
                            $('#reservation-resend-btn').prop('disabled', true).hide();
                        }
                        window.scrollTo(500, 0);
                    }
                });
            };
        });

        $("#reservation-resend-btn").click(function(e) {
            e.preventDefault();
            $.ajax({
                url: resendUrl,
                type: 'POST',
                data: { 'reserved_id': reserveId, 'form_key': window.FORM_KEY },
                beforeSend: function () {
                    jQuery('body').loader('show');
                },
                success: function (data) {
                    startTimer();
                    jQuery('body').loader('hide');
                    successErrorDiv.removeClass("message info")
                        .addClass("message success")
                        .html("<span>" + data['resendMessage'] + "</span>");
                    $('#reservation-btn').hide();
                    $('#reservation-btn, #reservation-resend-btn').prop('disabled', true);
                    $('#info-message, #countTime, #reservation-resend-btn').show();
                    window.scrollTo(500, 0);
                }
            });
        });

        function startTimer() {
            let countdown = minutes * 60 * 1000;
            let timerId = setInterval(function () {
                countdown -= 1000;
                let min = Math.floor(countdown / (60 * 1000));
                let sec = Math.floor((countdown - (min * 60 * 1000)) / 1000);

                if (countdown <= 0) {
                    $('#reservation-resend-btn').show().prop('disabled', false);
                    $("#countTime, #info-message, #reservation-btn").hide();
                    clearInterval(timerId);
                } else {
                    $("#countTime").html(min + " : " + sec);
                }
            }, 1000);
        }
    };
    return main;
});
