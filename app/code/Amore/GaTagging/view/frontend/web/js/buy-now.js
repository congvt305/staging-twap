define([
    'jquery',
    'underscore',
    'Amore_GaTagging/js/model/product',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'Amore_GaTagging/js/ap-cart'
], function ($, _, productModel, customerData, url) {
    'use strict';

    $.widget('mage.apBuyNow', $.mage.apCart, {
        options: {
            events: {
                AJAX_BUY_NOW: 'ajax:buyNow',
            },
            buttonSelect: '',
            productQtySelector: '#qty'
        },

        /**
         * @inheritdoc
         *
         * @private
         */
        _create: function () {
            this._super();
            this.cartItemsCache = [];
        },

        /**
         * Initialize actions callback function.
         *
         * @private
         */
        _initActions: function () {
            var events = this.options.events;
            this.options.actions[events.AJAX_BUY_NOW] = function (product) {
                product.qty = $(this.options.productQtySelector).val() || product.qty;
                productModel.init(product);
                this._notify('purchaseprdbtn', productModel.getData());

                $(document.body).trigger('buyNowGaEvent');
            }.bind(this);
        },

        /**
         * Sets listener to the cart data.
         *
         * @private
         */
        _setCartDataListener: function () {
            $(document.body).on('catalogCategoryAddToCartRedirect', function (e) {
                $.getJSON(
                    url.build('customer/section/load'),
                    {sections: 'cart'}
                ).fail(function (jqXHR) {
                    throw new Error(jqXHR);
                }).done(function (data) {
                    this.options.purchaseBtnEvent = true;
                    this.cartItemsCache = data?.cart?.items || [];

                    if (this.options.temporaryEventStorage.length) {
                        this._executeEvents();
                    }
                }.bind(this));
            }.bind(this));
        },

        /**
         * Sets listener to cart events.
         *
         * @private
         */
        _setListeners: function () {
            /**
             * Wrapper function for handler.
             *
             * @param {Function} callback
             * @param {String} type - action type
             * @param {Object} event - jQuery event
             * @param {Object} eventData - event data
             * @param {String} eventData.productId - product id
             */
            var handlerWrapper = function (callback, type, event, eventData) {
                    if (!_.isEmpty(eventData.response?.backUrl)) {
                        callback.call(this, type, eventData.productInfo);
                    }
                },
                opt = this.options;

            $(document)
                .on(
                    opt.events.AJAX_ADD_TO_CART,
                    handlerWrapper.bind(this, this._setToTemporaryEventStorage, opt.events.AJAX_BUY_NOW)
                );
        },

        /**
         * @param eventName
         * @param product
         */
        _notify: function (eventName, product) {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                event: eventName,
                quantity: product.quantity,
                variant: product.variant,
                product_param1: product.product_param1, // Child SKUs, separate by '/'
                product_param2: product.product_param2, // Child prices, separate by '/'
                product_param3: product.product_param3, // Child discount prices, separate by '/'
                product_param4: product.product_param4, // Child quantity, separate by '/'
                product_param5: product.product_param5
            });
        }
    });

    return $.mage.apBuyNow;
});
