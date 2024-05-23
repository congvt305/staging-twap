define([
    'jquery',
    'underscore',
    'jquery/ui',
    'jquery/jquery.parsequery',
    'mage/translate'
], function ($, _) {
    'use strict';
    return function (widget) {

        $.widget('mage.SwatchRenderer', widget, {

            /**
             * Event for swatch options
             *
             * @param {Object} $this
             * @param {Object} $widget
             * @private
             */
            _OnClick: function ($this, $widget) {
                $widget._super($this, $widget);
                if ($this.hasClass('outofstock')) {
                    $('button[id="product-addtocart-button"]').attr('disabled', true);
                    $('button[id="product-addtocheckout-button"]').attr('disabled', true);
                } else {
                    $('button[id="product-addtocart-button"]').attr('disabled', false);
                    $('button[id="product-addtocheckout-button"]').attr('disabled', false);
                }
            },

            /**
             * Render swatch options by part of config
             *
             * @param {Object} config
             * @param {String} controlId
             * @returns {String}
             * @private
             */
            _RenderSwatchOptions: function (config, controlId) {
                var optionConfig = this.options.jsonSwatchConfig[config.id],
                    optionClass = this.options.classes.optionClass,
                    sizeConfig = this.options.jsonSwatchImageSizeConfig,
                    moreLimit = parseInt(this.options.numberToShow, 10),
                    moreClass = this.options.classes.moreButton,
                    moreText = this.options.moreButtonText,
                    countAttributes = 0,
                    html = '';

                if (!this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                    return '';
                }

                $.each(config.options, function (index) {
                    var id,
                        type,
                        value,
                        thumb,
                        label,
                        width,
                        height,
                        attr,
                        swatchImageWidth,
                        swatchImageHeight;

                    if (!optionConfig.hasOwnProperty(this.id)) {
                        return '';
                    }

                    // Add more button
                    if (moreLimit === countAttributes++) {
                        html += '<a href="#" class="' + moreClass + '"><span>' + moreText + '</span></a>';
                    }

                    id = this.id;
                    type = parseInt(optionConfig[id].type, 10);
                    value = optionConfig[id].hasOwnProperty('value') ?
                        $('<i></i>').text(optionConfig[id].value).html() : '';
                    thumb = optionConfig[id].hasOwnProperty('thumb') ? optionConfig[id].thumb : '';
                    width = _.has(sizeConfig, 'swatchThumb') ? sizeConfig.swatchThumb.width : 110;
                    height = _.has(sizeConfig, 'swatchThumb') ? sizeConfig.swatchThumb.height : 90;
                    label = this.label ? $('<i></i>').text(this.label).html() : '';
                    attr =
                        ' id="' + controlId + '-item-' + id + '"' +
                        ' index="' + index + '"' +
                        ' aria-checked="false"' +
                        ' aria-describedby="' + controlId + '"' +
                        ' tabindex="0"' +
                        ' data-option-type="' + type + '"' +
                        ' data-option-id="' + id + '"' +
                        ' data-option-label="' + label + '"' +
                        ' aria-label="' + label + '"' +
                        ' role="option"' +
                        ' data-thumb-width="' + width + '"' +
                        ' data-thumb-height="' + height + '"';

                    attr += thumb !== '' ? ' data-option-tooltip-thumb="' + thumb + '"' : '';
                    attr += value !== '' ? ' data-option-tooltip-value="' + value + '"' : '';

                    swatchImageWidth = _.has(sizeConfig, 'swatchImage') ? sizeConfig.swatchImage.width : 30;
                    swatchImageHeight = _.has(sizeConfig, 'swatchImage') ? sizeConfig.swatchImage.height : 20;

                    if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                        attr += ' data-option-empty="true"';
                    }
                    if (this.stockStatus == 0) {
                        attr += ' option-stock="false"';
                    }
                    if (type === 0) {
                        // Text
                        html += '<div class="' + optionClass + ' text" ' + attr + '>' + (value ? value : label) + '</div>';
                    } else if (type === 1) {
                        // Color
                        html += '<div class="' + optionClass + ' color" ' + attr +
                            ' style="background: ' + value +
                            ' no-repeat center; background-size: initial;">' + '' +
                            '</div>';
                    } else if (type === 2) {
                        // Image
                        html += '<div class="' + optionClass + ' image" ' + attr +
                            ' style="background: url(' + value + ') no-repeat center; background-size: initial;width:' +
                            swatchImageWidth + 'px; height:' + swatchImageHeight + 'px">' + '' +
                            '</div>';
                    } else if (type === 3) {
                        // Clear
                        html += '<div class="' + optionClass + '" ' + attr + '></div>';
                    } else {
                        // Default
                        html += '<div class="' + optionClass + '" ' + attr + '>' + label + '</div>';
                    }
                });

                return html;
            },

            /**
             * Rewind options for controls
             *
             * @private
             */
            _Rewind: function (controls) {
                controls.find('div[data-option-id], option[data-option-id]').removeClass('disabled').removeAttr('disabled');
                controls.find('div[data-option-empty], option[data-option-empty]')
                    .attr('disabled', true)
                    .addClass('disabled')
                    .attr('tabindex', '-1');
                controls.find('div[option-stock]')
                    .addClass('outofstock');
            },
            _UpdatePrice: function () {
                var $widget = this,
                    $product = $widget.element.parents($widget.options.selectorProduct),
                    $productPrice = $product.find(this.options.selectorProductPrice),
                    result = $widget._getNewPrices(),
                    tierPriceHtml,
                    isShow;

                $productPrice.trigger(
                    'updatePrice',
                    {
                        'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                    }
                );

                isShow = typeof result != 'undefined' && result.oldPrice.amount !== result.finalPrice.amount;

                $productPrice.find('span:first').toggleClass('special-price', isShow);

                $product.find(this.options.slyOldPriceSelector)[isShow ? 'show' : 'hide']();
                //custom here
                $product.find('.discount-label')[isShow ? 'show' : 'hide']();
                //end custom
                if (typeof result != 'undefined' && result.tierPrices && result.tierPrices.length) {
                    if (this.options.tierPriceTemplate) {
                        tierPriceHtml = mageTemplate(
                            this.options.tierPriceTemplate,
                            {
                                'tierPrices': result.tierPrices,
                                '$t': $t,
                                'currencyFormat': this.options.jsonConfig.currencyFormat,
                                'priceUtils': priceUtils
                            }
                        );
                        $(this.options.tierPriceBlockSelector).html(tierPriceHtml).show();
                    }
                } else {
                    $(this.options.tierPriceBlockSelector).hide();
                }

                $(this.options.normalPriceLabelSelector).hide();

                _.each($('.' + this.options.classes.attributeOptionsWrapper), function (attribute) {
                    if ($(attribute).find('.' + this.options.classes.optionClass + '.selected').length === 0) {
                        if ($(attribute).find('.' + this.options.classes.selectClass).length > 0) {
                            _.each($(attribute).find('.' + this.options.classes.selectClass), function (dropdown) {
                                if ($(dropdown).val() === '0') {
                                    $(this.options.normalPriceLabelSelector).show();
                                }
                            }.bind(this));
                        } else {
                            $(this.options.normalPriceLabelSelector).show();
                        }
                    }
                }.bind(this));
            },
        });

        return $.mage.SwatchRenderer;
    }
});
