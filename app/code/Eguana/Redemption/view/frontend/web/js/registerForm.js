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
    'underscore',
    'mage/translate',
    'domReady!',
    'Magento_Ui/js/modal/modal',
    'mage/cookies'
], function ($, _, modal) {
    /**
     * @param config.countersaveurl
     * @param config.counterTime
     * @param config.resendUrl
     * @param config.successUrl
     */
    function main(config) {
        let countersaveurl = config.countersaveurl;
        let minutes = config.counterTime;
        let resendUrl = config.resendUrl;
        let successUrl = config.successUrl;
        let entityId = "";
        let infoAndErrorMessageDiv = $("#error-and-success-message");
        let url_string = window.location.href
        let url = new URL(url_string);
        let utmSource = url.searchParams.get("utm_source");
        let utmMedium = url.searchParams.get("utm_medium");
        let utmContent = url.searchParams.get("utm_content");
        let dmState = $('#dm_state');
        if (utmSource) {
          $('#utm_source').val(_.escape(utmSource));
        }
        if (utmMedium) {
            $('#utm_medium').val(_.escape(utmMedium));
        }
        if (utmContent) {
            $('#utm_content').val(_.escape(utmContent));
        }
        if (!utmSource && !utmMedium && !utmContent && $.mage.cookies.get('utm_information')) {
            let utmInformation = $.parseJSON($.mage.cookies.get('utm_information'));
            if (utmInformation.utm_source) {
                $('#utm_source').val(_.escape(utmInformation.utm_source));
            }
            if (utmInformation.utm_medium) {
                $('#utm_medium').val(_.escape(utmInformation.utm_medium));
            }
            if (utmInformation.utm_content) {
                $('#utm_content').val(_.escape(utmInformation.utm_content));
            }
        }
        $(document).find('span[id="recaptcha-response"]').hide();
        $('#info-message').hide();
        $("#counter-form-submit").click(function(e){
            e.preventDefault();
            if ($('form[id="counter-form"]').valid()) {
                /**
                 * To check phone no starts with 09 digits or not
                 */
                if ($('#phone').val().substr(0, 2) != '09') {
                    $('#phone').addClass('mage-error');
                    var phoneError = $('#phone-error');
                    if (!phoneError.length) {
                        $('#phone').after('<div for="phone" generated="true" class="mage-error" id="phone-error">' + $.mage.__("Phone number should starts with '09'") + '</div>');
                    } else {
                        phoneError.html($.mage.__("Phone number should starts with '09'")).show();
                    }
                    $('html, body').animate({
                        scrollTop: $('#name').offset().top},
                    'slow');
                    return false;
                } else {
                    $('#phone').removeClass('mage-error');
                    $('#phone-error').hide();
                }
                if (dmState.length) {
                    let regionName = "<input name='region_name' value=" + $('#region_id option:selected').text() + " title='region_name' type='hidden'>";
                    $('form[id="counter-form"]').append(regionName);
                }
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
                        if (data['success'] == false) {
                            window.location.href = data['redirectUrl'];
                        }
                        if (data['duplicate'] || data['vvip_case']) {
                            var options = {
                                type: 'popup',
                                responsive: false,
                            }, popup;

                            if (data['duplicate']) {
                                popup = modal(options, $('#popup-modal'));
                                $('#popup-modal').modal(options).modal('openModal');
                            }
                            if (data['vvip_case']) {
                                var $vipMessage = $('#popup-modal-vvip-denied');
                                $vipMessage.find('.modal-body-content').html(data['message']);
                                popup = modal(options, $vipMessage);
                                $vipMessage.modal(options).modal('openModal');
                            }
                            $('#counter-form-submit').show();
                            $('#counter-form-submit').removeAttr('disabled');
                            $('#redemption_thank_you_img').show();
                            $('#redemption_banner_img').hide();
                            window.scrollTo(500, 0);
                            jQuery('body').loader('hide');
                        }
                        if (data['success']) {
                            if (data['fbFunEnable']) {
                                fbq('track', 'Foundation_check_finalcheck');
                            }
                            if (typeof data['entity_id'] !== 'undefined' && data['entity_id']) {
                                window.location.href = successUrl + 'participant_id/' + data['entity_id'];
                            } else {
                                window.location.href = successUrl;
                            }
                        }
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
                        if (data['success'] == false) {
                            window.location.href = data['redirectUrl'];
                        }
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
