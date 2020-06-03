/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details'
        },

        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getValue: function (quoteItem) {
            return quoteItem.name;
        },

        getSize: function (itemId) {
            var itemsData = window.checkoutConfig.quoteItemData;
            var size = null;
            itemsData.forEach(function (item) {
                if (item.item_id === itemId) {
                    size = item.laneige_size;
                }
            });
            if (size !== false) {
                return size;
            } else {
                return '';
            }
        }
    });
});
