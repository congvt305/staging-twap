define([
    'ko',
    'underscore',
    'Magento_Ui/js/form/element/abstract'
], function (ko, _, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            elementTmpl: 'Amasty_SeoRichData/components/rich-data-preview',
            templates: {
                rating: 'Amasty_SeoRichData/components/rating'
            },
            links: {
                url_key: 'index = url_key:value',
                previewTitle: 'index = meta_title:value',
                previewPrice: 'dataScope = data.product.price:value',
                previewCurrency: 'dataScope = data.product.price:addbefore',
                product_description: 'dataScope = data.product.description:value',
                product_short_description: 'dataScope = data.product.short_description:value',
                meta_description: 'index = meta_description:value'
            },
            ratingType: {
                percentage: {
                    title: 'percentage',
                    value: 100
                },
                numeric: {
                    title: 'numeric',
                    value: 5
                }
            },
            urlDelimiter: ' > ',
            stripHtmlTags: /(<([^>]+)>)/gi
        },

        /**
         * @inheritDoc
         */
        initObservable: function () {
            this._super();

            this.observe([
                'url_key',
                'previewTitle',
                'previewPrice',
                'previewCurrency',
                'product_description',
                'product_short_description',
                'meta_description',
                'previewRatingTitle',
                'previewRatingValue'
            ]).observe({
                empty_description: ''
            });

            return this;
        },

        /**
         * @inheritDoc
         */
        initialize: function () {
            this._super();

            this.data = this.value();

            if (!this.componentEnabled()) {
                this.visible(false);

                return false;
            }

            this._setPreviewLink();
            this._setStockStatus();
            this._setRatingData();

            return this;
        },

        /**
         * @public
         * @returns {Boolean}
         */
        componentEnabled: function () {
            return !_.isEmpty(this.data);
        },

        /**
         * @public
         * @returns {Boolean}
         */
        ratingExist: function () {
            return _.isUndefined(this.data.rating.length) && this.data.rating.length !== 0;
        },

        /**
         * @public
         * @returns {String}
         */
        getDescription: function () {
            return _.unescape(this[this.data.description_mode]()).replace(this.stripHtmlTags, '');
        },

        /**
         * @public
         * @returns {String}
         */
        getPreviewReviews: function () {
            var count = this.data.rating.review_count;

            if (this.isTypePercentage()) {
                return count + (count > 1 ? ' reviews' : ' review');
            }

            return '(' + count + ')';
        },

        /**
         * @public
         * @returns {String}
         */
        getRatingClass: function () {
            return '-' + (this.isTypePercentage() ? this.ratingType.percentage.title : this.ratingType.numeric.title);
        },

        /**
         * @public
         * @returns {Boolean}
         */
        isTypePercentage: function () {
            return this.data.rating.best_rating === this.ratingType.percentage.value;
        },

        /**
         * @public
         * @returns {Boolean}
         */
        getPreviewPrice: function () {
            return this.isPriceUpdateLive() ? this.previewPrice() : this.data.price;
        },

        /**
         * @public
         * @returns {Boolean}
         */
        isPriceUpdateLive: function () {
            return this.data.price === 0;
        },

        /**
         * @private
         * @returns {void}
         */
        _setPreviewLink: function () {
            var baseUrl = this.data.base_url;

            if (baseUrl.slice(-1) === '/') {
                baseUrl = baseUrl.slice(0, -1);
            }

            this.previewLink = ko.pureComputed(function () {
                return '<span>' + baseUrl + '</span>' + this.urlDelimiter + this.url_key() + this.data.url_suffix;
            }, this);
        },

        /**
         * @private
         * @returns {void}
         */
        _setStockStatus: function () {
            this.stockStatus = ko.observable(!_.isNull(this.data.availability));
        },

        /**
         * @public
         * @returns {String}
         */
        getStockMessage: function () {
            switch (this.data.availability) {
                case 0:
                    return 'Out of Stock';
                case 1:
                    return 'In Stock';
                default:
                    return '';
            }
        },

        /**
         * @private
         * @returns {void}
         */
        _setRatingData: function () {
            this.previewRatingTitle(this.data.rating.rating_value + (this.isTypePercentage() ? '%' : ''));
            this.previewRatingValue((this.data.rating.rating_value * 100) / this.data.rating.best_rating);
        }
    });
});
