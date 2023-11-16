define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('cj.validateQty', {
        options: {
            validateQtyUrl: '',
            stockMessage: '.stock-message',
            formInfo: 'form#product_addtocart_form',
            bundleInfo: 'div.control [name^=bundle_option]',
            configurableInfo: '.super-attribute-select',
            customOptionsInfo: '.product-custom-option',
            qtyDecreaseInfo: '.qty-decrease',
            qtyIncreaseInfo: '.qty-increase',
            qtyInfo: 'input.qty'
        },

        /** @inheritdoc */
        _create: function () {
            this._bind();
            this._validateQty();
        },

        /**
         * @private
         */
        _bind: function () {
            var self = this,
                options = this.options,
                qtyValidationFunc = '_validateQty',
                changeQty = 'change ' + options.qtyInfo,
                events = {};

            events[changeQty] = qtyValidationFunc;

            this._on(events);
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _validateQty: function (event) {
            var self = this,
                options = this.options,
                qtyInput = $(self.element).find(options.qtyInfo + '[id^=qty]'),
                formData = $(options.formInfo).serializeArray(),
                url = options.validateQtyUrl;

            self._blockQtyButtons(true);
            $.ajax({
                url: url,
                data: formData,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (!response) {
                        return false;
                    }

                    var isInStock = response.is_in_stock;
                    self._validateQtyBtn(qtyInput.val(), isInStock);
                    self._displayStockMessage(response.message);
                }
            }).always(function () {
                self._blockQtyButtons(false);
            });
        },

        _validateQtyBtn: function (qty, isInStock = false) {
            var self = this,
                options = this.options;
            if (!isInStock) {
                $(self.element).find(options.qtyIncreaseInfo).addClass('disable');
            } else {
                $(self.element).find(options.qtyIncreaseInfo).removeClass('disable');
            }

            if (qty <= 1) {
                $(self.element).find(options.qtyDecreaseInfo).addClass('disable');
            } else {
                $(self.element).find(options.qtyDecreaseInfo).removeClass('disable');
            }
        },

        _displayStockMessage: function (message) {
            var self = this,
                options = self.options;
            $(options.stockMessage).html('');
            if (message) {
                $(options.stockMessage).html(message);
            }
        },

        _blockQtyButtons: function (isBlock = false) {
            var self = this,
                options = this.options;

            if (isBlock) {
                $(self.element).find(options.qtyIncreaseInfo).attr('disabled', 'disabled');
                $(self.element).find(options.qtyDecreaseInfo).attr('disabled', 'disabled');
            } else {
                $(self.element).find(options.qtyIncreaseInfo).removeAttr('disabled');
                $(self.element).find(options.qtyDecreaseInfo).removeAttr('disabled');
            }
        }
    });

    return $.cj.validateQty;
});
