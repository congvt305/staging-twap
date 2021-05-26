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
    'ko'
], function ($, Component, stepNavigator, quote, ko) {
	'use strict';

    var states = window.checkoutConfig.stateList;
	return Component.extend({
		defaults: {
			template: 'Eguana_RedInvoice/red-invoice'
		},
        showRedInvoiceForm: function () {
		    $('#red-invoice-form').slideToggle();
        },
        availableCountries : ko.observableArray(states),
        initialize: function () {
            this._super();
            return this;
        }
	});
});
