/**
 * Amasty Shopby helpers
 */

define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    return {
        color_constants: {
            gradient: 0.675,
            shadow: 0.8,
            hover: 0.45
        },

        /**
         * Generate gradient color depending on the specified percentage
         *
         * @params {String} color - default
         * @params {String} type - type of gradient
         *
         * @return {String} new gradient color
         */
        getGradient: function (color, type) {
            var lightenColor = this._getLightness(color, this.color_constants.gradient);

            if (type === 'vertical') {
                return 'linear-gradient(160deg, ' + lightenColor + '40%, ' + color + ' 100%)'
            }

            if (type === 'horizontal') {
                return 'linear-gradient(270deg,' + color + ' 0%, ' + lightenColor + ' 100%)'
            }
        },

        /**
         * Generate Box Shadow depending on the specified percentage
         *
         * @params {String} color - default
         *
         * @return {String} new box shadow
         */
        getShadow: function (color) {
            var shadowColor = this._getLightness(color, this.color_constants.shadow);

            return '0 3px 4px ' + shadowColor;
        },

        /**
         * Generate Hover color depending on the specified percentage
         *
         * @params {String} color - default
         * @params {Number} percent - default
         *
         * @return {String} new color
         */
        getHover: function (color, percent) {
            percent = percent ? percent : this.color_constants.hover;

            return this._getLightness(color, percent);
        },

        /**
         * Generate lightness color depending on the specified percentage
         *
         * @params {String} color - default
         * @params {Number} percent - default
         *
         * @return {String} new color
         */
        _getLightness: function (color, percent) {
            return $.Color(color).lightness(percent).toRgbaString();
        }
    }
});
