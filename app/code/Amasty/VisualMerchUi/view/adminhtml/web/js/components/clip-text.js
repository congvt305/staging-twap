/**
 * Copy target text into clipboard component
 */

define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    $.widget('mage.amClipText', {
        options: {
            copiedValue: '',
            copiedInput: false,
            messageHtml: $('<span class="ammerchui-message -success -hidden"></span>'),
            successMessage: $.mage.__('Copied!'),
            hiddenClass: '-hidden'
        },

        _create: function () {
            this.options.copiedElement = this.element.on('click', this.copyToClip.bind(this));
            this.element.append(this.getMessage());
        },

        /**
         * Copy value to clipboard
         */
        copyToClip: function () {
            if (!this.options.copiedInput) {
                this._createInput();
            }

            this.showMessage();
            this.options.copiedInput.attr({
                type: 'text'
            });
            this.options.copiedInput.select();
            document.execCommand('copy');
            this.options.copiedInput.attr({
                type: 'hidden'
            });
        },

        /**
         * Get current message html
         *
         * @return node
         */
        getMessage: function () {
            return this.options.messageHtml = this.options.messageHtml.clone().html(this.options.successMessage);
        },

        /**
         * Showing current message
         */
        showMessage: function () {
            var options = this.options;

            options.messageHtml.removeClass(options.hiddenClass);

            setTimeout(function(){
                options.messageHtml.addClass(options.hiddenClass);
            }, 3000);
        },

        /**
         * Create helper input and adding after the element
         */
        _createInput: function () {
            this.element.after(
                this.options.copiedInput = $('<input>').prop({
                    type: 'hidden',
                    value: this.options.copiedValue
                })
            );
        }
    });

    return $.mage.amClipText;
});
