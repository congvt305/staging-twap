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
    'mage/mage'
], function($) {
    $('.form-create-account').on('keyup', '#ba_code', function (event) {
        if (this.value) {
            if ($('#verified_ba_code').val() == this.value) {
                $('.bacode-link').attr('disabled', true).hide();
                $('.ba-code-verified').show();
            } else {
                $('.ba-code-verified').hide();
                $('.bacode-link').attr('disabled', false).show();
            }
        } else {
            $('.ba-code-message, .ba-code-verified').hide();
            $('.bacode-link').attr('disabled', true).show();
        }
    });
});
