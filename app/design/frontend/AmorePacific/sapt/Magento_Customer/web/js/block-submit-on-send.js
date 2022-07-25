/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/mage'
], function ($) {
    'use strict';

    return function (config) {
        var dataForm = $('#' + config.formId);

        dataForm.on('submit', function () {
            $(this).find(':submit').attr('disabled', 'disabled');

            if (this.isValid === false) {
                $(this).find(':submit').prop('disabled', false);
            } else {
                if (config.formId == 'newsletter-validate-detail') {
                    var self = $(this),
                        url = dataForm.attr('action'),
                        form = document.getElementById(config.formId),
                        data = new FormData(form);
                    $.ajax({
                        url:url,
                        type:'POST',
                        data: data,
                        showLoader: true,
                        cache:false,
                        processData: false,
                        contentType: false,
                        success:function(response){
                            self.find(':submit').prop('disabled', false);
                        }
                    });
                }
                return false;
            }
            this.isValid = true;
        });
        dataForm.on('invalid-form.validate', function () {
            $(this).find(':submit').prop('disabled', false);
            this.isValid = false;
        });
    };
});
