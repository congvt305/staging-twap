define(['jquery'], function ($) {
    'use strict';
    return function (SwatchRenderer) {
        $.widget('mage.addToWishlist', $['mage']['addToWishlist'], {
            options: {
                bundleInfo: 'div.control [name^=bundle_option]',
                configurableInfo: '.super-attribute-select',
                groupedInfo: '#super-product-table input',
                downloadableInfo: '#downloadable-links-list input',
                customOptionsInfo: '.product-custom-option',
                qtyDecreaseInfo: '.qty-decrease',
                qtyIncreaseInfo: '.qty-increase',
                qtyInfo: '#qty'
            },

            /** @inheritdoc */
            _create: function () {
                this._bind();
            },

            /**
             * @private
             */
            _bind: function () {
                var options = this.options,
                    dataUpdateFunc = '_updateWishlistData',
                    changeCustomOption = 'change ' + options.customOptionsInfo,
                    changeQty = 'change ' + options.qtyInfo,
                    decreaseIQty = 'click ' + options.qtyDecreaseInfo,
                    increaseQty = 'click ' + options.qtyIncreaseInfo,
                    events = {},
                    key;

                if ('productType' in options) {
                    if (typeof options.productType === 'string') {
                        options.productType = [options.productType];
                    }
                } else {
                    options.productType = [];
                }

                events[changeCustomOption] = dataUpdateFunc;
                events[changeQty] = dataUpdateFunc;
                events[decreaseIQty] = dataUpdateFunc;
                events[increaseQty] = dataUpdateFunc;

                for (key in options.productType) {
                    if (options.productType.hasOwnProperty(key) && options.productType[key] + 'Info' in options) {
                        events['change ' + options[options.productType[key] + 'Info']] = dataUpdateFunc;
                    }
                }
                this._on(events);
            },

            /**
             * @param {jQuery.Event} event
             * @private
             */
            _updateWishlistData: function (event) {
                var dataToAdd = {},
                    isFileUploaded = false,
                    self = this;

                if (event.handleObj.selector == this.options.qtyInfo) { //eslint-disable-line eqeqeq
                    this._updateAddToWishlistButton({});
                    event.stopPropagation();

                    return;
                }
                if (event.handleObj.selector == this.options.qtyDecreaseInfo || event.handleObj.selector == this.options.qtyIncreaseInfo) {
                    //eslint-disable-line eqeqeq
                    this._updateAddToWishlistButton({});
                    event.stopPropagation();

                    return;
                }
                $(event.handleObj.selector).each(function (index, element) {
                    if ($(element).is('input[type=text]') ||
                        $(element).is('input[type=email]') ||
                        $(element).is('input[type=number]') ||
                        $(element).is('input[type=hidden]') ||
                        $(element).is('input[type=checkbox]:checked') ||
                        $(element).is('input[type=radio]:checked') ||
                        $(element).is('textarea') ||
                        $('#' + element.id + ' option:selected').length
                    ) {
                        if ($(element).data('selector') || $(element).attr('name')) {
                            dataToAdd = $.extend({}, dataToAdd, self._getElementData(element));
                        }

                        return;
                    }

                    if ($(element).is('input[type=file]') && $(element).val()) {
                        isFileUploaded = true;
                    }
                });

                if (isFileUploaded) {
                    this.bindFormSubmit();
                }
                this._updateAddToWishlistButton(dataToAdd);
                event.stopPropagation();
            },
        });
        return $.mage.addToWishlist; // Return flow of original action.
    };
});
