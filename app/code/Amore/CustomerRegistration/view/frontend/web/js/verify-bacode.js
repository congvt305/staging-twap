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
        $('.form-create-account').on('click', '.bacode-link', function (event) {
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
                            $('#verified_ba_code').val(baCode);
                            $('.bacode-link').attr('disabled', true).hide();
                            $('.ba-code-verified').show();
                            $('.ba-code-message').text(response.message)
                                .removeClass('ba-code-warning').show();
                        } else if (response.message) {
                            $('.ba-code-verified').hide();
                            $('.bacode-link').attr('disabled', false).show();
                            $('.ba-code-message').text(response.message).addClass('ba-code-warning').show();
                        } else {
                            $('.ba-code-verified').hide();
                            $('.bacode-link').attr('disabled', false).show();
                            $('.ba-code-message').text($.mage.__('Unable to fetch information at this time'))
                                .addClass('ba-code-warning').show();
                        }
                    },
                    error: function (response) {
                        $('body').loader('hide');
                        $('.ba-code-verified').hide();
                        $('.bacode-link').attr('disabled', false).show();
                        $('.ba-code-message').text($.mage.__('Unable to fetch information at this time'))
                            .addClass('ba-code-warning').show();
                    }
                });
            }
        });
    }
    return main;
});
