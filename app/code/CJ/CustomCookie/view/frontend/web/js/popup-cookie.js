define([
   "jquery",
   "Magento_Ui/js/modal/modal",
   "text!CJ_CustomCookie/template/modal/modal-popup.html",
    "domReady!",
    'mage/cookies'
],function($, modal, popupTpl) {
    'use strict';
    return function (config) {
        var optionsPopup = {
            type: 'popup',
            responsive: true,
            popupTpl: popupTpl,
            clickableOverlay: false
        };

        let cookieValue = $.parseJSON($.mage.cookies.get(config.cookieName));
        if(cookieValue == null) {
            var popup = modal(optionsPopup, $('#modal'));
            $('#modal').modal('openModal');
        }
        else {
            if(cookieValue != config.websiteId) {
                var popup = modal(optionsPopup, $('#modal'));
                $('#modal').modal('openModal');
            };
        }
        $(config.cookieClosePopup).on('click', $.proxy(function () {
            var cookieExpires = new Date(new Date().getTime() + (config.cookieLifetime*60*60*1000));
            $.mage.cookies.set(config.cookieName, JSON.stringify(config.cookieValue), {
                expires: cookieExpires
            });
            $('#modal').modal('closeModal');
        }, this));
    }
});
