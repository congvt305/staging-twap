define([
	'jquery',
	'Amasty_Base/js/http_build_query',
	'uiRegistry',
	'underscore',
	'mage/cookies',
	'Magento_Ui/js/modal/modal'
], function ($, httpBuildQuery, uiRegistry, _) {
	'use strict';

	return function (widget) {
		$.widget('mage.amScrollScript', widget, {
			_hideToolbars: function () {},
		});
		return $.mage.SwatchRenderer;
	}
});