/**
 * @author Eguana Commerce USER
 * @copyright Copyright (c) 2020 Eguana Commerce
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/template',
    'text!Webkul_Grid/templates/grid/cells/customer/sendmail.html',
    'Magento_Ui/js/modal/modal'
], function (Column, $, mageTemplate, sendmailPreviewTemplate) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-html-cell': true
            }
        },
        gethtml: function (row) {
            return row[this.index + '_html'];
        },
        getFormaction: function (row) {
            return row[this.index + '_formaction'];
        },
        getOrderid: function (row) {
            return row[this.index + '_orderid'];
        },
        getLabel: function (row) {
            return row[this.index + '_html']
        },
        getTitle: function (row) {
            return row[this.index + '_title']
        },
        getSubmitlabel: function (row) {
            return row[this.index + '_submitlabel']
        },
        getCancellabel: function (row) {
            return row[this.index + '_cancellabel']
        },
        preview: function (row) {
            var modalHtml = mageTemplate(
                sendmailPreviewTemplate,
                {
                    html: this.gethtml(row),
                    title: this.getTitle(row),
                    label: this.getLabel(row),
                    formaction: this.getFormaction(row),
                    orderid: this.getOrderid(row),
                    submitlabel: this.getSubmitlabel(row),
                    cancellabel: this.getCancellabel(row),
                    linkText: $.mage.__('Go to Details Page')
                }
            );
            var previewPopup = $('<div/>').html(modalHtml);
            previewPopup.modal({
                title: this.getTitle(row),
                innerScroll: true,
                modalClass: '_image-box',
                buttons: []}).trigger('openModal');
        },
        getFieldHandler: function (row) {
            return this.preview.bind(this, row);
        }
    });
});
