define([
    'jquery',
    'Amore_GaTagging/js/model/product',
    'Magento_Customer/js/customer-data',
    'Magento_Theme/js/model/breadcrumb-list',
    'underscore',
    'jquery-ui-modules/widget'
], function ($, productModel, customerData, breadcrumbs, _) {
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
            this.cartItemsCache = customerData.get('cart')().items;
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
                var apCartAddProds = [];
                productModel.init(product);
                apCartAddProds.push(productModel.getData());

                window.AP_CART_ADDPRDS = apCartAddProds;
                this.options.dataLayer.push({'event': 'addcart'});
                this.options.dataLayer.push({
                    'event': 'productAddToCart',
                    'ecommerce': {
                        'currency': AP_PRD_CURRENCY,
                        'add': {
                            'products': apCartAddProds
                        }
                    }
                });
            }.bind(this);
        },

        /**
         * Finds and returns product.
         *
         * @param {Object} productInfo - product info.
         * @return {Object} product data.
         */
        getProduct: function (productInfo) {
            var searchCriteria,
                productOptionValues = productInfo.optionValues || [],
                productFromCache,
                productFromCart;

            /**
             * Product search criteria.
             *
             * @param {Object} item
             * @return {Boolean}
             */
            searchCriteria = function (item) {
                var index = 0;

                if (item['product_id'] !== productInfo.id) {
                    return false;
                }

                if (productOptionValues.length === 0) {
                    return true;
                }

                if (item['product_type'] === 'bundle') {
                    if (_.isEmpty(item.bundle_options)) {
                        return false;
                    }

                    while (index < item.bundle_options.length) {
                        if (productOptionValues.indexOf(item.bundle_options[index]) === -1) {
                            return false;
                        }
                        index++;
                    }
                } else {
                    while (index < item.options.length) {
                        if (productOptionValues.indexOf(item.options[index]['option_value']) === -1) {
                            return false;
                        }
                        index++;
                    }
                }

                return true;
            };

            productFromCache = _.find(this.cartItemsCache, searchCriteria);
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
         * @param {Array} productInfo - product info.
         *
         * @private
         */
        _setToTemporaryEventStorage: function (type, productInfo) {
            this.options.temporaryEventStorage.push({
                type: type,
                productInfo: productInfo
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
                if (typeof item.productInfo === 'undefined') {
                    return;
                }

                item.productInfo.forEach(function (productInfoItem) {
                    product = this.getProduct(productInfoItem);

                    if (!_.isUndefined(product['product_sku']) && Math.abs(parseInt(product.qty, 10)) > 0) {
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
                    if (_.isEmpty(eventData.response?.backUrl)) {
                        callback.call(this, type, eventData.productInfo);
                    }
                },
                opt = this.options;

            $(document)
            .on(
                opt.events.AJAX_ADD_TO_CART,
                handlerWrapper.bind(this, this._setToTemporaryEventStorage, opt.events.AJAX_ADD_TO_CART)
            );
        },


    });

    return $.mage.apCart;
});
