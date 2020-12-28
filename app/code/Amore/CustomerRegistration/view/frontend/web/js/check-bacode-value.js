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
            $('.bacode-link').attr('disabled', false);
            $('#customerSubmit').attr('disabled', true);
        } else {
            $('.ba-code-message').hide();
            $('.bacode-link').attr('disabled', true);
            $('#customerSubmit').attr('disabled', false);
        }
    });
});
