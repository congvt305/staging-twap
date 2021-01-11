/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/mage'
], function($){

    $('#customerSubmit').on('click', function (event) {
        var dataForm = $('#form-validate');
        if(dataForm.validation('isValid') === true){
            var currentBACode = $('#ba_code').val();
            var verifiedBACode = $('#verified_ba_code').val();
            if (currentBACode) {
                if (currentBACode != verifiedBACode) {
                    event.preventDefault();
                    $('.ba-code-message').text($.mage.__("Please click 'Confirm' button for data verification"))
                        .addClass('ba-code-warning').show();
                } else {
                    $('body').loader('show');
                }
            } else {
                $('body').loader('show');
            }
        }
    });
});
