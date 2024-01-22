/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/06/21
 * Time: 11:00 AM
 */

define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'underscore',
    'mage/template',
    'jquery-ui-modules/widget'
], function ($, utils, _, mageTemplate) {
    'use strict';
    return function (widget) {
        $.widget('mage.priceBox', widget, {
            /*eslint-disable no-extra-parens*/
            /**
             * Render price unit block.
             */
            reloadPrice: function reDrawPrices() {
                var priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {},
                    priceTemplate = mageTemplate(this.options.priceTemplate);

                _.each(this.cache.displayPrices, function (price, priceCode) {
                    price.final = _.reduce(price.adjustments, function (memo, amount) {
                        return memo + amount;
                    }, price.amount);

                    price.formatted = utils.formatPrice(price.final, priceFormat);

                    if (this.cache.displayPrices.oldPrice != undefined) {
                        var oldPrice = this.cache.displayPrices.oldPrice.amount;
                        if (price.final != oldPrice) {
                            var discount = Math.floor((oldPrice - price.final) / oldPrice * 100);
                            $('.discount-label').text(discount+'%');
                        }
                    }

                    if (priceCode == 'finalPrice') {
                        var qtyInput = $(this.element).closest('.product-info-main').find('input[name=qty]');
                        if (qtyInput) {
                            price.final = price.final * parseInt(qtyInput.val());
                        }
                        $('[data-price-type="' + priceCode + '"]', this.element).data('price-amount', price.final);
                        price.formatted = utils.formatPrice(price.final, priceFormat);
                    }

                    $('[data-price-type="' + priceCode + '"]', this.element).html(priceTemplate({
                        data: price
                    }));
                }, this);
            }
        });
        return $.mage.priceBox;
    };
});
