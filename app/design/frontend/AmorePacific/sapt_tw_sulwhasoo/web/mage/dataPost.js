/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/confirm',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'mage/translate',
    'jquery-ui-modules/widget'
], function ($, mageTemplate, uiConfirm, customerData, urlBuilder) {
    'use strict';

    $.widget('mage.dataPost', {
        options: {
            formTemplate: '<form action="<%- data.action %>" method="post">' +
                '<% _.each(data.data, function(value, index) { %>' +
                '<input name="<%- index %>" value="<%- value %>">' +
                '<% }) %></form>',
            postTrigger: ['a[data-post]', 'button[data-post]', 'span[data-post]'],
            formKeyInputSelector: 'input[name="form_key"]'
        },

        /** @inheritdoc */
        _create: function () {
            this._bind();
        },

        /** @inheritdoc */
        _bind: function () {
            var events = {};

            $.each(this.options.postTrigger, function (index, value) {
                events['click ' + value] = '_postDataAction';
            });

            this._on(events);
        },

        /**
         * Handler for click.
         *
         * @param {Object} e
         * @private
         */
        _postDataAction: function (e) {
            var params = $(e.currentTarget).data('post');

            if ($(e.currentTarget).hasClass('ajax-towishlist')) {
                var customer = customerData.get('customer');
                if(!customer().fullname && !customer().firstname) {
                    var signupUrl = urlBuilder.build('customer/account/create');
                    var loginUrl = urlBuilder.build('customer/account/login');
                    var content = '<span class="msg">' + $.mage.__('You Can Access A Wishlist After Logging In') + '</span>' +
                        '<a class="action secondary" href="' + loginUrl + '">' + $.mage.__('Log in') + '</a>' +
                        '<a class="action signup-link" href="' + signupUrl + '">' + $.mage.__('New to Sulwhasoo? Create new account') + '</a>';
                    uiConfirm({
                        title: 'Please login',
                        content: content,
                        modalClass: 'wishlist-popup-login'
                    });
                    return;
                }
            }

            e.preventDefault();
            this.postData(params);
        },

        /**
         * Data post action.
         *
         * @param {Object} params
         */
        postData: function (params) {
            var formKey = $(this.options.formKeyInputSelector).val(),
                $form, input;

            if (formKey) {
                params.data['form_key'] = formKey;
            }

            $form = $(mageTemplate(this.options.formTemplate, {
                data: params
            }));

            if (params.files) {
                $form[0].enctype = 'multipart/form-data';
                $.each(params.files, function (key, files) {
                    if (files instanceof FileList) {
                        input = document.createElement('input');
                        input.type = 'file';
                        input.name = key;
                        input.files = files;
                        $form[0].appendChild(input);
                    }
                });
            }

            if (params.data.confirmation) {
                uiConfirm({
                    content: params.data.confirmationMessage,
                    actions: {
                        /** @inheritdoc */
                        confirm: function () {
                            $form.appendTo('body').hide().trigger('submit');
                        }
                    }
                });
            } else {
                $form.appendTo('body').hide().trigger('submit');
            }
        }
    });

    $(document).dataPost();

    return $.mage.dataPost;
});
