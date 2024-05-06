define([
	'jquery'
], function ($) {
	'use strict';
	return function (widget) {
		$.widget('mage.amScrollScript', widget, {
			_hideToolbars: function () {
			},
		});
		return $.mage.amScrollScript;
	}
});