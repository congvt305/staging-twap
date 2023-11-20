define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/template',
    'text!Eguana_BizConnect/templates/grid/cells/messages/view.html',
    'Magento_Ui/js/modal/modal'
], function (Column, $, mageTemplate, previewTemplate) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-html-cell': true
            }
        },

        /**
         * This is a function.
         */
        getHtml: function (row) {
            return row[this.index + '_html'];
        },

        /**
         * This is a function.
         */
        getMessage: function (row) {
            return row[this.index + '_message'];
        },

        /**
         * This is a function.
         */
        getLogs: function (row) {
            return row[this.index + '_logs'];
        },

        /**
         * This is a function.
         */
        getLabel: function (row) {
            return row[this.index + '_html'];
        },

        /**
         * This is a function.
         */
        getTitle: function (row) {
            return row[this.index + '_title'];
        },

        /**
         * This is a function.
         */
        getUrl: function (row) {
            return row[this.index + '_details_url'];
        },

        /**
         * This is a function.
         */
        preview: function (row) {
            var self = this;

            $.getJSON(this.getUrl(row), function (data) {
                var modalHtml, previewPopup;

                modalHtml = mageTemplate(
                    previewTemplate,
                    {
                        html: self.getHtml(data),
                        title: self.getTitle(data),
                        label: self.getLabel(data),
                        message: self.getMessage(data),
                        logs: self.getLogs(data)
                    }
                );
                previewPopup = $('<div/>').html(modalHtml);
                previewPopup.modal({
                    title: self.getTitle(data),
                    innerScroll: true,
                    modalClass: '_image-box',
                    responsive: true,
                    type: 'slide',
                    buttons: []
                }).trigger('openModal');
            });

        },

        /**
         * This is a function.
         */
        getFieldHandler: function (row) {
            return this.preview.bind(this, row);
        }
    });
});
