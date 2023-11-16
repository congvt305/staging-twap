define(['jquery'], function ($) {
    'use strict';
    var mixin = {
        _create: function createPriceOptions() {
            var priceFormat = this.options.priceFormat || {};
            this.options.priceFormat = $.extend(priceFormat,this.options.optionConfig.priceFormat)
            return this._super();
        }
    };
    return function (targetWidget) {
        $.widget('mage.priceOptions', targetWidget, mixin);
        return $.mage.priceOptions;
    }
})
