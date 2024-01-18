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
				if (items && items.length > 0) {
					items = items.slice(parseInt(-this.maxCartItemsToDisplay, 10));
				}
				var normalItems = [],
					promoItems = [];
				items.forEach((item) => {
					let quoteItem = this.getItemFromQuote(item);
					if (quoteItem.ampromo_rule_id) {
						promoItems.push(item);
					} else {
						normalItems.push(item);
					}
				})
				items = normalItems.concat(promoItems);
				this.items(items);
			},

			/**
			 *
			 * @param {Object} item
			 * @return {*}
			 */
			getItemFromQuote: function (item) {
				var items = quote.getItems(),
					quoteItems = items.filter(function (quoteItem) {
						return +quoteItem.item_id == item.item_id;
					});

				if (quoteItems.length === 0) {
					return false;
				}

				return quoteItems[0];
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