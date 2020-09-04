define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'underscore',
    'jquery-ui-modules/widget'
], function ($, customerData, _) {
    'use strict';

    $.widget('mage.apCart', {
        options: {
            dataLayer: window.dataLayer || [],
            cookieAddToCart: '',
            temporaryEventStorage: [],
            events: {
                AJAX_ADD_TO_CART: 'ajax:addToCart',
            },
            actions: {}
        },

        /**
         * @inheritdoc
         *
         * @private
         */
        _create: function () {
            this.cartItemsCache = [];
            this._initActions();
            this._setListeners();
            this._setCartDataListener();
        },

        /**
         * Initialize actions callback function.
         *
         * @private
         */
        _initActions: function () {
            var events = this.options.events;

            this.options.actions[events.AJAX_ADD_TO_CART] = function (product) {
                console.log('addcart')
                this.options.dataLayer.push({'event': 'addcart'});
            }.bind(this);
        },

        /**
         * Finds and returns product by sku.
         *
         * @param {String} productId - product id.
         * @return {Object} product data.
         */
        getProductById: function (productId) {
            /**
             * Product search criteria.
             *
             * @param {Object} item
             * @return {Boolean}
             */
            var searchCriteria = function (item) {
                    return item['product_id'] === productId;
                },
                productFromCache = _.find(this.cartItemsCache, searchCriteria),
                productFromCart = _.find(customerData.get('cart')().items, searchCriteria);

            if (!productFromCache && !productFromCart) {
                return _.extend({}, productFromCart, {
                    qty: 1
                });
            }

            if (productFromCache && productFromCart) {
                return _.extend({}, productFromCache, {
                    qty: productFromCart.qty - productFromCache.qty
                });
            }

            return productFromCache || productFromCart;
        },

        /**
         * Sets event to temporary storage.
         * When the cart data was updated this event will be executed.
         *
         * @param {String} type - Event type.
         * @param {Array} productIds - list of product ids.
         *
         * @private
         */
        _setToTemporaryEventStorage: function (type, productIds) {
            this.options.temporaryEventStorage.push({
                type: type,
                productIds: productIds
            });
        },

        /**
         * Sets listener to the cart data.
         *
         * @private
         */
        _setCartDataListener: function () {
            customerData.get('cart').subscribe(function (data) {
                if (this.options.temporaryEventStorage.length) {
                    this._executeEvents();
                }

                this.cartItemsCache = data.items.slice();
            }.bind(this));
        },

        /**
         * Sets listener to the cart.
         *
         * @private
         */
        _executeEvents: function () {
            var product;

            this.options.temporaryEventStorage.forEach(function (item, index) {
                item.productIds.forEach(function (productId) {
                    product = this.getProductById(productId);

                    if (!_.isUndefined(product['product_sku']) && parseInt(product.qty, 10) > 0) {
                        this.options.actions[item.type](product);
                    }

                    this.options.temporaryEventStorage.splice(index, 1);
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
                    callback.call(this, type, eventData.productIds);
                },
                opt = this.options;

            $(document)
                .on(
                    opt.events.AJAX_ADD_TO_CART,
                    handlerWrapper.bind(this, this._setToTemporaryEventStorage, opt.events.AJAX_ADD_TO_CART)
                );
        }
    });

    return $.mage.apCart;
});
