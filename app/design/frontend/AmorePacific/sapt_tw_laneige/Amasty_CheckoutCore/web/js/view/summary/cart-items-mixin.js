define([
	'ko',
	'Magento_Checkout/js/model/totals',
	'uiComponent',
	'Magento_Checkout/js/model/step-navigator',
	'Magento_Checkout/js/model/quote'
], function (ko, totals, Component, stepNavigator, quote) {
	'use strict';

	return function (Component) {
		return Component.extend({
			setItems: function (items) {
				var quoteItems = quote.getItems();

				if (items && items.length > 0) {
					items = items.slice(parseInt(-this.maxCartItemsToDisplay, 10));
				}
				var normalItems = [],
					promoItems = [];
				items.forEach((item, index) => {
					if (quoteItems[index]['ampromo_rule_id']) {
						promoItems.push(item);
					} else {
						normalItems.push(item);
					}
				})
				items = normalItems.concat(promoItems);
				this.items(items);
			},
			/**
			 * After Magento 2.2.4 items is not expanded by default.
			 * return true for expanded by default
			 */
			isItemsBlockExpanded: function () {
				return true;
			}
		});
	}
});