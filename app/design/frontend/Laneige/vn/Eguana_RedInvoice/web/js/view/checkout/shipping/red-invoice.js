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
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/quote',
    'ko',
    'mage/url',
    'mage/translate'
], function ($, Component, stepNavigator, quote, ko, url) {
    'use strict';

    var states = window.checkoutConfig.stateList;
    var cities = "";
    var wards = "";
    var note = window.checkoutConfig.noteText;

    $(document).on('change', 'input[type="radio"]', function () {
        var isApply = $(this).val();
        $(".company-name-error, .tax-code-error, .state-error, .road-name-error").css("display", "none");
        if (isApply == 0) {
            $("#red-invoice-fields :input").prop("disabled", true);
        } else {
            $("#red-invoice-fields :input").prop("disabled", false);
        }
    });
    return Component.extend({
        defaults: {
            template: 'Eguana_RedInvoice/red-invoice'
        },
        showRedInvoiceForm: function () {
            $("#apply_button").prop("checked", true);
            $("#red-invoice-fields :input").prop("disabled", false);
            $(".company-name-error, .tax-code-error, .state-error, .road-name-error").css("display", "none");
            $('#red-invoice-form').slideToggle(function () {
                if ($('#red-invoice-form').is(":hidden")) {
                    $("#do_not_apply_button").prop("checked", true);
                }
            });
        },
        availableCountries: ko.observableArray(states),
        availableCities: ko.observableArray([
            {default_name: $.mage.__('Please select a city or district.'), city_id: '0000'}
        ]),
        availableWards: ko.observableArray([
            {default_name: $.mage.__(''), ward_id: '0000'}
        ]),
        initialize: function () {
            this._super();
            return this;
        },
        getNoteText: function () {
            return note;
        },
        updateCities: function () {
            var step = this;
            var i = 0;
            var selectedValue = $('[name="state"]').find(":selected").val();
            let apiUrl = url.build('redinvoice/index/ajaxcall/');
            $.ajax({
                url: apiUrl,
                type: "POST",
                showLoader: true,
                data: {selectedValue: selectedValue},
            }).done(function (data) {
                cities = data.cities;
                step.availableCities.removeAll();
                if (cities == "") {
                    step.availableCities.push(
                        {default_name: $.mage.__('Please select a city or district.'), city_id: '0000'}
                    );
                } else {
                    for (i = 0; i < cities.length; i++) {
                        step.availableCities.push(cities[i]);
                    }
                }
            });
        },
        updateWards: function () {
            var step = this;
            var i = 0;
            var selectedValue = $('[name="city"]').find(":selected").val();
            let apiUrl = url.build('redinvoice/directory/getWard/');
            if (selectedValue) {
                $.ajax({
                    dataType: 'json',
                    showLoader: true,
                    url: apiUrl + '?city_id=' + selectedValue,
                    success: function (data) {
                        wards = data.wards;
                        if (step.availableWards) {
                            step.availableWards.removeAll();
                        }
                        if (wards != "" && selectedValue != '0000') {
                            for (i = 0; i < wards.length; i++) {
                                step.availableWards.push(wards[i]);
                            }
                        }
                    }
                });
            }
        }
    });
});
