/**
 * Price filter Slider
 */

define([
    'jquery',
    'amshopby_color',
    'amShopbyHelpers',
    'jquery-ui-modules/slider',
    'mage/tooltip',
    'amShopbyFiltersSync'
], function ($, colorHelper, helpers) {
    'use strict';

    $.widget('mage.amShopbyFilterSlider', $.mage.amShopbyFilterAbstract, {
        options: {
            gradients: {},
        },
        selectors: {
            value: '[data-amshopby-slider-id="value"]',
            range: '.ui-slider-range',
            slider: '[data-amshopby-slider-id="slider"]',
            display: '[data-amshopby-slider-id="display"]',
            container: '[data-am-js="slider-container"]',
            tooltip: '[data-amshopby-js="slider-tooltip"]',
            handle: '.ui-slider-handle'
        },
        classes: {
            tooltip: 'amshopby-slider-tooltip',
            styleDefault: '-default',
            loaded: '-loaded'
        },
        attributes: {
            tooltip: 'slider-tooltip'
        },
        slider: null,
        value: null,
        display: null,

        /**
         * inheritDoc
         *
         * @private
         */
        _create: function () {
            helpers.jqueryWidgetCompatibility(
                'jquery/ui-modules/widgets/slider',
                'slider',
                function () {
                    this._initializeWidget();
                }.bind(this)
            );
        },

        /**
         * @private
         * @returns {void}
         */
        _initializeWidget: function () {
            var fromLabel = Number(this._getInitialFromTo('from')).toFixed(2),
                toLabel = Number(this._getInitialFromTo('to')).toFixed(2);

            this.setCurrency(this.options.curRate);
            this._initNodes();
            this._initColors();

            if (this.options.to) {
                this.value.val(fromLabel + '-' + toLabel);
            } else {
                this.value.trigger('change');
                this.value.trigger('sync');
            }

            fromLabel = this.processPrice(false, fromLabel, this.options.deltaFrom);
            toLabel = this.processPrice(false, toLabel, this.options.deltaTo);

            this._initSlider(fromLabel, toLabel);
            this._renderLabel(fromLabel, toLabel);
            this._setTooltipValue(this.slider, fromLabel, toLabel);
            this.value.on('amshopby:sync_change', this._onSyncChange.bind(this));

            if (this.options.hideDisplay) {
                this.display.hide();
            }
        },

        /**
         * @private
         * @param {string} value - 'from' or 'to'
         * @returns {string | number}
         */
        _getInitialFromTo: function (value) {
            switch (value) {
                case 'from':
                    return this.options.from && this.options.from >= this.options.min
                        ? this.options.from
                        : this.options.min;
                case 'to':
                    return this.options.to && this.options.to <= this.options.max
                        ? this.options.to
                        : this.options.max;
            }
        },

        /**
         * @private
         * @returns {void}
         */
        _initNodes: function () {
            this.value = this.element.find(this.selectors.value);
            this.slider = this.element.find(this.selectors.slider);
            this.display = this.element.find(this.selectors.display);
        },

        /**
         * @private
         * @returns {void}
         */
        _initColors: function () {
            var colors = this.options.colors,
                mainColor = colors.main,
                gradients = this.options.gradients;

            gradients.vertical = colorHelper.getGradient(mainColor, 'vertical');
            gradients.horizontal = colorHelper.getGradient(mainColor, 'horizontal');

            colors.shadow = colorHelper.getShadow(mainColor);
            colors.hover = colorHelper.getHover(mainColor);
        },

        /**
         * @private
         * @param {number} fromLabel
         * @param {number} toLabel
         * @returns {void}
         */
        _initSlider: function (fromLabel, toLabel) {
            this.slider.slider({
                step: (this.options.step ? this.options.step : 1) * this.options.curRate,
                range: true,
                min: this.options.min * this.options.curRate,
                max: this.options.max * this.options.curRate,
                values: [fromLabel, toLabel],
                slide: this._onSlide.bind(this),
                change: this._onChange.bind(this)
            });

            this.handles = this.element.find(this.selectors.handle);
            this.range = this.element.find(this.selectors.range);

            if (this._isNotDefaultSlider()) {
                this._renderTooltips();
            }

            switch (this.options.style) {
                case '-volumetric':
                    this._initVolumetric();
                    break
                case '-improved':
                    this._initImproved();
                    break
                case '-light':
                    this._initLight();
                    break
                case '-dark':
                    this._initDark();
                    break
                default:
                    this._initDefault();
                    break
            }

            this._initHandles();
            this.slider.addClass(this.classes.loaded);
        },

        /**
         * @private
         */
        _initVolumetric: function () {
            $(this.tooltips).css({
                'background': this.options.gradients.horizontal
            });

            $(this.handles).css({
                'background': this.options.gradients.vertical,
                'box-shadow': this.options.colors.shadow
            });

            $(this.range).css({
                'background': this.options.gradients.horizontal
            });
        },

        /**
         * @private
         */
        _initImproved: function () {
            $(this.tooltips).css({
                'background': this.options.colors.main,
                'border-color': this.options.colors.main
            });

            $(this.handles).css({
                'background': this.options.colors.main
            });

            $(this.range).css({
                'background': this.options.colors.main
            });
        },

        /**
         * @private
         */
        _initDark: function () {
            $(this.tooltips).css({
                'background': this.options.colors.main,
                'border-color': this.options.colors.main
            });

            $(this.handles).css({
                'background': this.options.colors.main,
                'box-shadow': this.options.colors.shadow
            });

            $(this.range).css({
                'background': this.options.colors.main
            });
        },

        /**
         * @private
         */
        _initLight: function () {
            $(this.tooltips).css({
                'color': this.options.colors.main
            });

            $(this.range).css({
                'background': this.options.colors.main
            });
        },

        /**
         * @private
         */
        _initDefault: function () {
            $(this.handles).css({
                'background': this.options.colors.main
            });
        },

        /**
         * @private
         */
        _initHandles: function () {
            var self = this,
                handles = self.handles,
                sliderStyle = self.options.style,
                mainColor = handles.css('background-color');

            if (sliderStyle === '-light') {
                mainColor = handles.css('border-color');
            }

            if (sliderStyle === '-volumetric') {
                mainColor = handles.css('background-image')
            }

            handles.on('mouseover', function () {
                if (self.options.style === '-light') {
                    $(this).css({
                        'border-color': self.options.colors.hover
                    });
                } else {
                    $(this).css({
                        'background': self.options.colors.hover
                    });
                }
            });

            handles.on('mouseout', function () {
                if (self.options.style === '-light') {
                    $(this).css({
                        'border-color': mainColor
                    });
                } else {
                    $(this).css({
                        'background': mainColor
                    });
                }
            });
        },

        /**
         * @private
         * @returns {boolean}
         */
        _isNotDefaultSlider: function () {
            return this.options.style !== this.classes.styleDefault;
        },

        /**
         * @private
         * @param {object} event
         * @param {object} ui
         * @returns {boolean}
         */
        _onChange: function (event, ui) {
            var rate;

            if (this.slider.skipOnChange !== true) {
                rate = $(ui.handle).closest(this.selectors.container).data('rate');

                this._setValue(Number(ui.values[0]).toFixed(2), Number(ui.values[1]).toFixed(2), true, rate);
            }

            return true;
        },

        /**
         * @private
         * @param {object} event
         * @param {object} ui
         * @returns {boolean}
         */
        _onSlide: function (event, ui) {
            var valueFrom = ui.values[0],
                valueTo = ui.values[1];

            this._setValue(valueFrom, valueTo, false);
            this._renderLabel(valueFrom, valueTo);

            this._setTooltipValue(event.target, valueFrom, valueTo);

            return true;
        },

        /**
         * @private
         * @param {object} event
         * @param {array} values
         * @returns {void}
         */
        _onSyncChange: function (event, values) {
            var value = values[0].split('-'),
                valueFrom,
                valueTo;

            if (value.length === 2) {
                valueFrom = this._parseValue(value[0]);
                valueTo = this._parseValue(value[1]);

                this.slider.skipOnChange = true;

                this.slider.slider('values', [valueFrom, valueTo]);
                this._setValueWithoutChange(valueFrom, valueTo);
                this._setTooltipValue(this.slider, valueFrom, valueTo);
                this.slider.skipOnChange = false;
            }
        },

        /**
         * @private
         * @param {number} from
         * @param {number} to
         * @param {boolean} apply
         * @returns {void}
         */
        _setValue: function (from, to, apply) {
            var valueFrom = this._parseValue(this.processPrice(true, from), 2),
                valueTo = this._parseValue(this.processPrice(true, to), 2),
                newValue,
                changedValue,
                linkHref;

            newValue = valueFrom + '-' + valueTo;
            changedValue = this.value.val() !== newValue;

            this.value.val(newValue);

            if (!this.isBaseCurrency()) {
                this.setDeltaParams(this.getDeltaParams(from, valueFrom, to, valueTo, false));
            }

            if (changedValue) {
                this.value.trigger('change');
                this.value.trigger('sync');
            }

            if (apply !== false) {
                newValue = valueFrom + '-' + valueTo;
                linkHref = this.options.url
                    .replace('amshopby_slider_from', valueFrom)
                    .replace('amshopby_slider_to', valueTo);

                linkHref = this.getUrlWithDelta(
                    linkHref,
                    valueFrom,
                    from,
                    valueTo,
                    to,
                    this.options.deltaFrom,
                    this.options.deltaTo
                );

                this.value.val(newValue);
                $.mage.amShopbyFilterAbstract.prototype.renderShowButton(0, this.element[0]);
                $.mage.amShopbyFilterAbstract.prototype.apply(linkHref);
            }
        },

        /**
         * @private
         * @param {number} from
         * @param {number} to
         * @returns {void}
         */
        _setValueWithoutChange: function (from, to) {
            this.value.val(this._parseValue(from) + '-' + this._parseValue(to));
        },

        /**
         * @private
         * @param {string} from
         * @param {string} to
         * @returns {string}
         */
        _getLabel: function (from, to) {
            return this.options.template.replace('{from}', from.toString()).replace('{to}', to.toString());
        },

        /**
         * @private
         * @param {number} from
         * @param {number} to
         * @returns {void}
         */
        _renderLabel: function (from, to) {
            var valueFrom = this._parseValue(from),
                valueTo = this._parseValue(to);

            this.display.html(this._getLabel(valueFrom, valueTo));
        },

        /**
         * @private
         * @returns {object}
         */
        _getTooltip: function () {
            return $('<span>', {
                'class': this.classes.tooltip,
                'data-amshopby-js': this.attributes.tooltip
            });
        },

        /**
         * @private
         * @returns {void}
         */
        _renderTooltips: function () {
            this.handles.prepend(this._getTooltip());
            this.tooltips = this.handles.find(this.selectors.tooltip);
        },

        /**
         * @private
         * @param {object} element
         * @param {string} from
         * @param {string} to
         * @returns {void}
         */
        _setTooltipValue: function (element, from, to) {
            var handle = this.selectors.handle,
                tooltip = this.selectors.tooltip,
                currencySymbol = this.options.currencySymbol,
                currencyPosition = parseInt(this.options.currencyPosition),
                valueFrom = this._parseValue(from),
                valueTo = this._parseValue(to),
                firstElement = $(element).find(handle + ':first-of-type ' + tooltip),
                lastElement = $(element).find(handle + ':last-of-type ' + tooltip);

            if (!this._isNotDefaultSlider()) {
                return;
            }

            if (currencyPosition) {
                firstElement.html(valueFrom + currencySymbol);
                lastElement.html(valueTo + currencySymbol);
            } else {
                firstElement.html(currencySymbol + valueFrom);
                lastElement.html(currencySymbol + valueTo);
            }
        },

        /**
         * @private
         * @returns {number}
         */
        _getFixedValue: function () {
            return this.getSignsCount(this.options.step, 0);
        },

        /**
         * @private
         * @param {string | number} value
         * @param {number} toFixedValue
         * @returns {string}
         */
        _parseValue: function (value, toFixedValue) {
            return parseFloat(value).toFixed(toFixedValue ? toFixedValue : this._getFixedValue());
        },

        /**
         * @private
         * @param {string} value
         * @returns {string}
         */
        _replacePriceDelimiter: function (value) {
            return value.replace('.', ',');
        }
    });

    return $.mage.amShopbyFilterSlider;
});
