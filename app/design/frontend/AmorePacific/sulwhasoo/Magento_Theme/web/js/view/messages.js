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
    'jquery/jquery-storageapi'
], function ($, Component, customerData, _) {
    'use strict';

    return Component.extend({
        defaults: {
            cookieMessages: [],
            messages: [],
            selector: '.page.messages .message',
            removeButton: '.page.messages .message-visible .remove'
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

            if (!_.isEmpty(this.messages().messages)) {
                customerData.set('messages', {});
            }

            $.cookieStorage.set('mage-messages', '');

            $(document).on('click touch touchstart', removeButton, function (event) {
                $(event.target).closest('div.message').slideUp();
            });
        },
    });
});
