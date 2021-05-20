define([
    'jquery',
	'uiComponent'
], function ($, Component) {
	'use strict';

	return Component.extend({
		defaults: {
			template: 'Eguana_RedInvoice/red-invoice'
		},
        showRedInvoiceForm: function () {
		    $('#red-invoice-form').slideToggle();
        }
	});
});
