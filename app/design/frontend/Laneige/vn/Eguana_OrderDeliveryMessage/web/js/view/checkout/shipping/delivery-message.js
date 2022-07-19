define([
    'jquery',
	'uiComponent'
], function ($, Component) {
	'use strict';

	return Component.extend({
		defaults: {
			template: 'Eguana_OrderDeliveryMessage/delivery-message'
		},

        showDeliveryMessageText: function () {
            $('#delivery_form').slideToggle();
        },
	});
});
