/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 22/12/2020
 * Time: 8:20 PM
 */
define([
    'jquery',
    'mage/translate'
], function($) {
    'use strict';

    /**
     * @param config.baCodeVerifyUrl
     */
    function main(config) {
        $(document).on('click', '.bacode-link', function (event) {
            var baCode = $('#ba_code').val();
            if (baCode) {
                $.ajax({
                    url: config.baCodeVerifyUrl,
                    type: 'post',
                    dataType: 'json',
                    context: this,
                    cache: false,
                    data: { 'baCode': baCode },
                    beforeSend: function () {
                        $('body').loader('show');
                    },
                    success: function (response) {
                        $('body').loader('hide');
                        if (response.verify) {
                            $('.ba-code-message').text(response.message)
                                .removeClass('ba-code-warning').show();
                            $('#customerSubmit').attr('disabled', false);
                        } else if (response.message) {
                            $('.ba-code-message').text(response.message).addClass('ba-code-warning').show();
                            $('#customerSubmit').attr('disabled', true);
                        } else {
                            $('.ba-code-message').text($.mage.__('Unable to fetch ba code record at this time'))
                                .addClass('ba-code-warning').show();
                            $('#customerSubmit').attr('disabled', true);
                        }
                    },
                    error: function (response) {
                        $('body').loader('hide');
                        $('.ba-code-message').text($.mage.__('Unable to fetch ba code record at this time'))
                            .addClass('ba-code-warning').show();
                        $('#customerSubmit').attr('disabled', true);
                    }
                });
            }
        });
    }
    return main;
});
