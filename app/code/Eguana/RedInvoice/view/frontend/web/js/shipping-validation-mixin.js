/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/11/21
 * Time: 10:10 AM
 */

define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';
    return function (Component) {
        return Component.extend({
            defaults: {
                template: 'Eguana_RedInvoice/shipping'
            },

            validateShippingInformation: function () {
                var superResult = this._super();
                if (superResult) {
                    if($("#red-invoice-form").is(":visible")) {
                        var is_apply = $('input[name="is_apply"]:checked').val();;
                        if (is_apply == 1) {
                            var company_name = '[name="company_name"]';
                            company_name = $(company_name).val();
                            $(".company-name-error").css("display", "none");
                            if (company_name.length == 0) {
                                $(".company-name-error").css("display", "block");
                                $("#company_name").focus();
                                return false;
                            }

                            var tax_code = '[name="tax_code"]';
                            tax_code = $(tax_code).val();
                            $(".tax-code-error").css("display", "none");
                            if (tax_code.length == 0) {
                                $(".tax-code-error").css("display", "block");
                                $("#tax_code").focus();
                                return false;
                            }

                            var state = '[name="state"]';
                            state = $(state).val();
                            $(".state-error").css("display", "none");
                            if (state.length == 0) {
                                $(".state-error").css("display", "block");
                                return false;
                            }

                            var road_name = '[name="road_name"]';
                            road_name = $(road_name).val();
                            $(".road-name-error").css("display", "none");
                            if (road_name.length == 0) {
                                $(".road-name-error").css("display", "block");
                                $("#road_name").focus();
                                return false;
                            }
                        }
                    }
                }
                return superResult;
            }
        });
    };
});
