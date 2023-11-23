define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.configurable', widget, {
            /**
             * Initialize tax configuration, initial settings, and options values.
             * @private
             */
            _initializeOptions: function () {
                if ($(this.element).closest('.content-view')) {
                    this.options.priceHolderSelector = $(this.element).find(this.options.priceHolderSelector);
                    if (!this.options.priceHolderSelector.data('magePriceBox')) {
                        this.options.priceHolderSelector.priceBox();
                    }
                }

                return this._super();
            }
        });

        return $.mage.configurable;
    };
});
