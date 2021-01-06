/*
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 6/1/21
 * Time: 10:15 AM
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/lib/spinner',
    'Magento_Ui/js/form/element/abstract',
    'mage/url',
    'mage/translate',
    'domReady!'
], function ($, _, loader, Abstract, urlBuilder) {
    'use strict';

    $(document).on('keyup', 'input[name="customer[ba_code]"]', function() {
        if (this.value) {
            if ($('#admin_verified_ba_code').val() == this.value) {
                $('#save_and_continue, #save').attr('disabled', false);
                $('#admin_ba_verify_btn').attr('disabled', true).hide();
            } else {
                $('#save_and_continue, #save').attr('disabled', true);
                $('#admin_ba_verify_btn').attr('disabled', false).show();
            }
        } else {
            $('.admin-ba-code-message').text('').hide();
            $('#save_and_continue, #save').attr('disabled', false);
            $('#admin_ba_verify_btn').attr('disabled', true).show();
        }
    });

    return Abstract.extend({
        _create: function () {
            $('#admin_ba_verify_btn').on('click', $.proxy(this.verifyBACode, this));
        },
        verifyBACode: function () {
            var baCode = $('input[name="customer[ba_code]"]').val();
            if (baCode) {
                $.ajax({
                    url: urlBuilder.build('/customerregistration/verification/verifybacode'),
                    type: 'post',
                    dataType: 'json',
                    cache: false,
                    data: { 'baCode': baCode },
                    beforeSend: function () {
                        $('body').loader('show');
                    },
                    success: function (response) {
                        $('body').loader('hide');
                        if (response.verify) {
                            $('#admin_verified_ba_code').val(baCode);
                            $('#admin_ba_verify_btn').attr('disabled', true).hide();
                            $('#save_and_continue, #save').attr('disabled', false);
                            $('.admin-ba-code-message').text(response.message).show();
                        } else if (response.message) {
                            $('#save_and_continue, #save').attr('disabled', true);
                            $('#admin_ba_verify_btn').attr('disabled', false).show();
                            $('.admin-ba-code-message').text(response.message).show();
                        } else {
                            $('#save_and_continue, #save').attr('disabled', true);
                            $('#admin_ba_verify_btn').attr('disabled', false).show();
                            $('.admin-ba-code-message').text($.mage.__('Unable to fetch information at this time')).show();
                        }
                    },
                    error: function (response) {
                        $('body').loader('hide');
                        $('#save_and_continue, #save').attr('disabled', true);
                        $('#admin_ba_verify_btn').attr('disabled', false).show();
                        $('.admin-ba-code-message').text($.mage.__('Unable to fetch information at this time')).show();
                    }
                });
            }
        }
    });
});
