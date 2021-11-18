/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'underscore',
    'escaper',
    'jquery/jquery-storageapi'
], function ($, Component, customerData, _, escaper) {
    'use strict';

    return Component.extend({
        defaults: {
            cookieMessages: [],
            messages: [],
            selector: '.page.messages .message',
            removeButton: '.page.messages .message-visible .remove',
            isHidden: false,
            listens: {
                isHidden: 'onHiddenChange'
            },
            allowedTags: ['div', 'span', 'b', 'strong', 'i', 'em', 'u', 'a']
        },

        /**
         * Extends Component object by storage observable messages.
         */
        initialize: function () {
            this._super();

            this.cookieMessages = _.unique($.cookieStorage.get('mage-messages'), 'text');
            this.messages = customerData.get('messages').extend({
                disposableCustomerData: 'messages'
            });

            var removeButton = this.removeButton;
            $(removeButton).show();

            // Force to clean obsolete messages
            if (!_.isEmpty(this.messages().messages)) {
                customerData.set('messages', {});
            }

            $.cookieStorage.set('mage-messages', '');

            $(document).on('click touch touchstart', removeButton, function (event) {
                $(event.target).closest('div.message').slideUp();
            });
        },

        //2021.11.02 추가 S
        initObservable: function () {
            this._super()
                .observe('isHidden');

            return this;
        },

        RemoveMessage: function () {
            var el = $(this.selector);
            el.slideUp();
        },

        isVisible: function () {
            return this.isHidden(!_.isEmpty(this.messages().messages) || !_.isEmpty(this.cookieMessages));
        },

        onHiddenChange: function (isHidden) {
            var self = this;

            // Hide message block if needed
            if (isHidden) {
                setTimeout(function () {
                    self.RemoveMessage();
                }, 3000);
            }
            // reseting variable
            this.isHidden(false);
        },
        //2021.11.02 추가 E

        /**
         * Prepare the given message to be rendered as HTML
         *
         * @param {String} message
         * @return {String}
         */
        prepareMessageForHtml: function (message) {
            return escaper.escapeHtml(message, this.allowedTags);
        }
    });
});
