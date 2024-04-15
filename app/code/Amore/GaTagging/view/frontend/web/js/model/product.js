define([], function () {
    'use strict';

    var productData = {
        name: '',
        code: '',
        v2code: '',
        sapcode: '',
        brand: '',
        price: 0,
        prdprice: 0,
        apg_brand_code: '',
        discount: 0,
        variant: '',
        promotion: undefined,
        cate: '',
        catecode: '',
        quantity: 0,
        url: '',
        product_param1: undefined, // Child SKUs, separate by '/'
        product_param2: undefined, // Child prices, separate by '/'
        product_param3: undefined, // Child discount prices, separate by '/'
        product_param4: undefined, // Child quantity, separate by '/'
        product_param5: undefined  // Child SKUs, separate by '/'
    };

    return {
        data: productData,

        /**
         * @param {Object} product
         */
        init: function (product) {
            this.data = productData;
            this.data.name = product.product_name;
            this.data.code = product.product_sku;
            this.data.v2code = product.product_id;
            this.data.sapcode = product.product_sku;
            this.data.brand = product.product_brand;
            this.data.price = product.price;
            this.data.prdprice = product.product_original_price;
            this.data.discount = product.discount_price;
            this.data.apg_brand_code = product.apg_brand_code;
            this.data.cate = product.product_category;
            this.data.quantity = product.qty;
            this.data.url = product.product_url;
            this.data.img_url = product.image_url;
            this.data.variant = product.variant;

            if (['bundle', 'configurable'].includes(product.product_type)) {
                if (product.parent_sku) {
                    this.data.code = product.parent_sku;
                }

                if (product.qty >= 1 && product.child_qtys) {
                    if ('bundle' === product.product_type) {
                        var childQtysArr = String(product.child_qtys).split(' / ');
                        childQtysArr = childQtysArr.map(function (childQty) {
                            return Number(childQty) * product.qty;
                        });

                        product.child_qtys = childQtysArr.join(' / ');
                    } else {
                        product.child_qtys = product.qty;
                    }
                }
                this.data.product_param1 = product?.child_skus;
                this.data.product_param2 = product?.child_prices;
                this.data.product_param3 = product?.child_discount_prices;
                this.data.product_param4 = product?.child_qtys;
                this.data.product_param5 = product?.gifts;
            }
        },

        /**
         * Get product data
         */
        getData: function () {
            return this.data;
        }
    };
});
