define([
   "jquery",
   "Magento_Ui/js/modal/modal",
   "text!CJ_CustomCookie/template/modal/modal-popup.html",
    "domReady!"
],function($, modal, popupTpl) {
    'use strict';
    return function (config) {
        var optionsPopup = {
            type: 'popup',
            responsive: true,
            popupTpl: popupTpl,
            clickableOverlay: false
        };

        var popup = modal(optionsPopup, $('#modal'));
        if (!config.isEnabledCookieBrowser) {
            $('#modal').modal('openModal');
        }
        $('#btn-cookie-allow').click(function () {
            $('#modal').modal('closeModal');
        });
    }
});
