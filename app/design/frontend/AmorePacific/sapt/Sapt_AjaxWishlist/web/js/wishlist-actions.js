define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/url',
    'mage/translate',
    'jquery-ui-modules/widget',
], function ($, confirmation, urlBuilder) {
    'use strict';
    var self;
    $.widget('mage.ajaxWishlist', {

        _create: function () {
            this._bind();
        },

        _bind: function () {
            self = this;
            $('body').on('click', '[data-action="wishlist-actions"]', this._wishlistAction);
        },

        _wishlistAction: function (event) {
            var productId = $(this).attr('data-product-id'),
                urlkey = urlBuilder.build('rest/V2/wishlist/ajax-wishlist-api/?productId=' + productId);
            self._ajaxcall(urlkey)
        },

        _ajaxcall: function (urlkey) {

            $.ajax({
                type: "POST",
                dataType: "json",
                url: urlkey,
            }).done(function (data) {

                if (data !== true) {
                    var signupUrl = urlBuilder.build('customer/account/create');
                    var loginUrl = urlBuilder.build('customer/account/login');
                    var content = '<span class="msg">' + $.mage.__('You Can Access A Wishlist After Logging In') + '</span>' +
                        '<a class="action secondary" href="' + loginUrl + '">' + $.mage.__('Log in') + '</a>' +
                        '<a class="action signup-link" href="' + signupUrl + '">' + $.mage.__('New to Laneige? Create new account') + '</a>';
                    confirmation({
                        title: $.mage.__('Please login'),
                        content: content,
                        modalClass: 'wishlist-popup-login'
                    });
                }
            });
        }

    });
    return $.mage.ajaxWishlist;
});
