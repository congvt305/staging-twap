define(['jquery', 'jquery-ui-modules/widget'], function ($) {
    'use strict';

    var stickyMixin = {
        options: {
            /**
             * Element selector, who's height will be used to restrict the
             * maximum offsetTop position of the stuck element.
             * Default uses document body.
             * @type {String}
             */
            container: '',

            /**
             * Spacing in pixels above the stuck element
             * @type {Number|Function} Number or Function that will return a Number
             */
            spacingTop: 0,

            /**
             * Allows postponing sticking, until element will go out of the
             * screen for the number of pixels.
             * @type {Number|Function} Number or Function that will return a Number
             */
            stickAfter: 0,

            /**
             * CSS class for active sticky state
             * @type {String}
             */
            stickyClass: '_sticky',

            fixedMaxOffset: 999999
        },
        /**
         * float Block on windowScroll
         * @private
         */
        _stick: function () {
            var offset,
                isStatic,
                stuck,
                stickAfter,
                fixedMaxOffset;
            
            isStatic = this.element.css('position') === 'static';

            if (!isStatic && this.element.is(':visible')) {
                offset = $(document).scrollTop() -
                    this.parentOffset +
                    this._getOptionValue('spacingTop');

                fixedMaxOffset = this._getOptionValue('fixedMaxOffset')

                offset = Math.max(0, Math.min(offset, this.maxOffset, fixedMaxOffset));
                offset +=this._getOptionValue('spacingTop')
                stuck = this.element.hasClass(this.options.stickyClass);
                stickAfter = this._getOptionValue('stickAfter');

                if (offset && !stuck && offset < stickAfter) {
                    offset = 0;
                }

                this.element
                    .toggleClass(this.options.stickyClass, offset > 0)
                    .css('top', offset);
            }
        },
        _calculateDimens: function () {
            var $parent         = this.element.parent(),
                topMargin       = parseInt(this.element.css('margin-top'), 10),
                parentHeight    = $parent.height() - topMargin,
                height          = this.element.innerHeight(),
                maxScroll       = document.body.offsetHeight - window.innerHeight;

            if (this.options.container.length > 0) {
                maxScroll = $(this.options.container).height();
            }

            this.parentOffset   = $parent.offset().top + topMargin;
            this.maxOffset      = maxScroll - this.parentOffset;

            if (this.maxOffset + height >= parentHeight) {
                this.maxOffset = parentHeight - height;
            }

            return this;
        },
    };

    return function (targetWidget) {
        // Example how to extend a widget by mixin object
        $.widget('mage.sticky', targetWidget, stickyMixin); // the widget alias should be like for the target widget

        return $.mage.sticky; //  the widget by parent alias should be returned
    };
});
