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
            $('body').loader('show');
        }});
});