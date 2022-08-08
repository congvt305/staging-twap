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
            var apCartAddProds = [];
            this.options.actions[events.AJAX_ADD_TO_CART] = function (product) {
                if(product.product_type === 'bundle') {
                    this.getBundleProductData(product).forEach(function (info) {
                        apCartAddProds.push(info);
                    });
                } else if (product.product_type === 'configurable') {
                    this.getConfigurableProductData(product).forEach(function (info) {
                        apCartAddProds.push(info);
                    });
                } else {
                    this.getSimpleProductData(product).forEach(function (info) {
                        apCartAddProds.push(info);
                    });
                }
                console.log('apCartAddProds:',apCartAddProds);
                window.AP_CART_ADDPRDS = apCartAddProds;
                this.options.dataLayer.push({'event': 'addcart'});
            }.bind(this);
        },

        getBundleProductData: function (product) {
            var productInfosArr = window.PRD_DATA;
            productInfosArr.forEach(function (productInfo) {
                productInfo.quantity = parseInt(product.qty);
                delete productInfo.rate;
            });
            return productInfosArr;
        },
        getSimpleProductData: function (product) {
            var productInfosArr = [];
            var productInfo = {
                'name': product.product_name,
                'code': product.product_sku,
                'v2code': product.product_id,
                'sapcode': product.product_sku,
                'brand': product.product_brand,
                'price': product.product_price_value,
                'prdprice': parseInt(product.product_original_price),
                'variant': '',
                'promotion': '',
                'cate': '',
                'catecode': '',
                'quantity': product.qty,
            }
            productInfosArr.push(productInfo);
            return productInfosArr;
        },
        getConfigurableProductData: function (product) {
            var productInfosArr = window.PRD_DATA;
            var selectedProductInfo = [];
            productInfosArr.forEach(function (productInfo) {
                productInfo.quantity = parseInt(product.qty);
                productInfo.variant = productInfo.name.replace(product.product_name, '');
                selectedProductInfo.push(productInfo);
            });
            return selectedProductInfo;
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
        },


    });

    return $.mage.apCart;
});
