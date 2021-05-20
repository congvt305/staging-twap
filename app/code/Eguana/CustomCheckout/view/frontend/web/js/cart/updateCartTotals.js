/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 20/5/21
 * Time: 6:50 PM
 */

define([
    'jquery',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Customer/js/customer-data'
], function ($, getTotalsAction, customerData) {
    $(document).ready(function() {
        var form = $('form#form-validate');
        $.ajax({
            url: form.attr('action'),
            data: form.serialize(),
            success: function (res) {
                var parsedResponse = $.parseHTML(res);
                var result = $(parsedResponse).find("#form-validate");
                var sections = ['cart'];

                $("#form-validate").replaceWith(result);

                // The totals summary block reloading
                var deferred = $.Deferred();
                getTotalsAction([], deferred);
            },
            error: function (xhr, status, error) {
                var err = eval("(" + xhr.responseText + ")");
            }
        });
    });
});
