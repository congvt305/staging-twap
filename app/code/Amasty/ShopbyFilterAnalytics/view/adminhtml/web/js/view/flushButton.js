define([
    'jquery',
    'underscore',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'jquery/ui'
], function ($, _, confirm, alert) {
    'use strict';

    $.widget('mage.amShopAnalyticsFlush', {
        options: {
            confirm: {
                title: '',
                content: ''
            },
            url: ''
        },

        /**
         * Bind handlers to events
         * @return {void}
         */
        _create: function () {
            this._on({
                'click': $.proxy(this.onClick, this)
            });
        },

        /**
         * Click event
         * @return {void}
         */
        onClick: function () {
            var confirmConfig = _.extend(
                {
                    actions: { confirm: this.sendRequest.bind(this) }
                },
                this.options.confirm
            );

            confirm(confirmConfig);
        },

        /**
         * @return {void}
         */
        sendRequest: function () {
            $.ajax({
                url: this.options.url,
                data: { form_key: window.FORM_KEY },
                showLoader: true,
                headers: this.options.headers || {}
            }).done(function (response) {
                var message = response.errorMessage;

                if (!message) {
                    message = response.message;
                }

                if (message) {
                    alert({ content: message });
                }
            });
        }
    });

    return $.mage.amShopAnalyticsFlush;
});
